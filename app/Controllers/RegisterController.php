<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\TendikModel;
use App\Models\GuruModel;

class RegisterController extends BaseController
{
    public function index()
    {
        $data = [
            'title' => 'Registrasi Akun Absensi'
        ];

        return view('auth/register', $data);
    }

    public function store()
    {
        $role     = $this->request->getPost('role');
        $nama     = $this->request->getPost('nama');
        $nip      = $this->request->getPost('nip');
        $noHp     = $this->request->getPost('no_hp');
        $email    = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        if (empty($role) || empty($nama) || empty($nip) || empty($email) || empty($password)) {
            return redirect()->back()->withInput()->with('error', 'Semua kolom utama wajib diisi!');
        }

        $userModel = model(\CodeIgniter\Shield\Models\UserModel::class);

        // 1. Simpan Akun User
        $user = new \CodeIgniter\Shield\Entities\User([
            'username' => $nip,
            'email'    => $email,
            'password' => $password,
        ]);

        if (!$userModel->save($user)) {
            return redirect()->back()->withInput()->with('errors', $userModel->errors());
        }

        $user = $userModel->findById($userModel->getInsertID());

        // 2. Simpan Data Pegawai Berdasarkan Role
        if ($role === 'guru') {
            $user->addGroup('guru');
            $mapel = $this->request->getPost('mapel');
            
            $guruModel = new GuruModel();
            $guruModel->insert([
                'nuptk'          => $nip,
                'nama_guru'      => $nama,
                'no_hp'          => $noHp,
                'email'          => $email,
                'mata_pelajaran' => $mapel
            ]);

        } else if ($role === 'tendik') {
            $user->addGroup('tendik');
            $jabatan = $this->request->getPost('jabatan');
            
            $tendikModel = new TendikModel();
            $tendikModel->insert([
                'nip'         => $nip,
                'nama_tendik' => $nama,
                'no_hp'       => $noHp,
                'email'       => $email,
                'jabatan'     => $jabatan
            ]);
        }

        return redirect()->to(base_url('login'))->with('message', 'Registrasi berhasil! Silakan login.');
    }
}