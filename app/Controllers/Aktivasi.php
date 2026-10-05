<?php

namespace App\Controllers;

use App\Models\GuruModel;
use App\Models\TendikModel;
use App\Controllers\BaseController;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Models\UserModel;

class Aktivasi extends BaseController
{
    protected $guruModel;
    protected $tendikModel;

    public function __construct()
    {
        $this->guruModel = new GuruModel();
        $this->tendikModel = new TendikModel();
    }

    public function index()
    {
        if (auth()->loggedIn()) {
            return redirect()->to(base_url('/'));
        }
        return view('auth/aktivasi');
    }

    public function cekData()
    {
        if (auth()->loggedIn()) {
            return redirect()->to(base_url('/'));
        }

        $namaInput  = trim($this->request->getPost('nama_lengkap'));
        $nomorInput = trim($this->request->getPost('nomor_induk'));

        // Bersihkan spasi & karakter newline dari input
        $cleanNomorInput = preg_replace('/\s+/', '', $nomorInput);

        $db = \Config\Database::connect();

        // 1. Cek di Tabel Guru
        $guru = $db->table('tb_guru')
                   ->where("REPLACE(REPLACE(REPLACE(nuptk, ' ', ''), '\r', ''), '\n', '') =", $cleanNomorInput)
                   ->like('nama_guru', $namaInput, 'both')
                   ->get()->getRowArray();

        // 2. Cek di Tabel Tendik jika tidak ditemukan di Guru
        $tendik = null;
        if (!$guru) {
            $tendik = $db->table('tb_tendik')
                         ->where("REPLACE(REPLACE(REPLACE(nip, ' ', ''), '\r', ''), '\n', '') =", $cleanNomorInput)
                         ->like('nama_tendik', $namaInput, 'both')
                         ->get()->getRowArray();
        }

        if (!$guru && !$tendik) {
            return redirect()->back()->with('error', 'Data Nama dan NUPTK/NIP tidak cocok dengan data Admin!')->withInput();
        }

        $tipe     = $guru ? 'guru' : 'tendik';
        $idMaster = $guru ? $guru['id_guru'] : $tendik['id_tendik'];
        $tableUserExists = $db->tableExists('users') ? 'users' : 'tb_petugas';
        $columnCheck     = $guru ? 'id_guru' : 'id_tendik';

        $alreadyHasAccount = $db->table($tableUserExists)
                                ->where($columnCheck, $idMaster)
                                ->get()->getRowArray();

        if ($alreadyHasAccount) {
            return redirect()->back()->with('error', 'Akun untuk data ini SUDAH AKTIF! Silakan langsung login.')->withInput();
        }

        session()->set('data_aktivasi', [
            'tipe'  => $tipe,
            'id'    => $idMaster,
            'nama'  => $guru ? ($guru['nama_guru'] ?? '') : ($tendik['nama_tendik'] ?? ''),
            'nip'   => $nomorInput
        ]);

        return redirect()->to(base_url('daftar/buat-akun'));
    }

    public function buatAkun()
    {
        if (auth()->loggedIn()) {
            return redirect()->to(base_url('/'));
        }

        $dataAktivasi = session()->get('data_aktivasi');

        if (!$dataAktivasi) {
            return redirect()->to(base_url('daftar'));
        }

        return view('auth/buat-akun', ['data' => $dataAktivasi]);
    }

    public function simpanAkun()
    {
        if (auth()->loggedIn()) {
            return redirect()->to(base_url('/'));
        }

        $dataAktivasi = session()->get('data_aktivasi');
        if (!$dataAktivasi) {
            return redirect()->to(base_url('daftar'));
        }

        $username    = trim($this->request->getPost('username'));
        $email       = trim($this->request->getPost('email'));
        $password    = $this->request->getPost('password');
        $passConfirm = $this->request->getPost('pass_confirm');

        if ($password !== $passConfirm) {
            return redirect()->back()->with('error', 'Konfirmasi password tidak sama dengan password!')->withInput();
        }

        $db = \Config\Database::connect();
        $userModel = new UserModel();

        // 1. Cek apakah Username sudah dipakai
        $checkUsername = $userModel->where('username', $username)->first();

        // 2. Cek apakah Email sudah dipakai (di tabel auth_identities)
        $checkEmail = null;
        if ($db->tableExists('auth_identities')) {
            $checkEmail = $db->table('auth_identities')
                             ->where('secret', $email)
                             ->get()->getRowArray();
        }

        if ($checkUsername || $checkEmail) {
            return redirect()->back()->with('error', 'Username atau Email sudah digunakan! Pilihlah yang lain.')->withInput();
        }

        // 3. Buat Entity User CI Shield
        $user = new User([
            'username' => $username,
            'email'    => $email,
            'password' => $password,
        ]);

        if (!$userModel->save($user)) {
            $errors = implode(', ', $userModel->errors());
            return redirect()->back()->with('error', 'Gagal membuat akun: ' . $errors)->withInput();
        }

        // 4. Ambil User terdaftar & Aktifkan
        $insertedId = $userModel->getInsertID();
        $user = $userModel->findById($insertedId);

        if ($user) {
            $user->activate();

            // 5. Tambahkan Group (Role) CI Shield
            if ($dataAktivasi['tipe'] === 'guru') {
                $user->addGroup('guru');
            } else {
                $user->addGroup('tendik');
            }
        }

        // 6. Hubungkan ID Guru / ID Tendik ke tabel users
        $tableUser = $db->tableExists('users') ? 'users' : 'tb_petugas';
        $updateData = [];

        if ($dataAktivasi['tipe'] === 'guru') {
            $updateData['id_guru'] = $dataAktivasi['id'];
        } else {
            $updateData['id_tendik'] = $dataAktivasi['id'];
        }

        $db->table($tableUser)->where('id', $insertedId)->update($updateData);

        // Hapus session sementara
        session()->remove('data_aktivasi');

        return redirect()->to(base_url('login'))->with('message', 'Aktivasi akun berhasil! Silakan login dengan email/username dan password Anda.');
    }
}