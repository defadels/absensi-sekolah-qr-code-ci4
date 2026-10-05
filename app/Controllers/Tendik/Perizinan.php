<?php

namespace App\Controllers\Tendik;

use App\Controllers\BaseController;

class Perizinan extends BaseController
{
    public function index()
    {
        $db         = \Config\Database::connect();
        $isLoggedIn = auth()->loggedIn();
        $loggedData = null;

        if ($isLoggedIn) {
            $user = auth()->user();
            if (!empty($user->id_guru) && $db->tableExists('tb_guru')) {
                $loggedData = $db->table('tb_guru')->where('id_guru', $user->id_guru)->get()->getRowArray();
            } elseif (!empty($user->id_tendik) && $db->tableExists('tb_tendik')) {
                $loggedData = $db->table('tb_tendik')->where('id_tendik', $user->id_tendik)->get()->getRowArray();
            }
        }

        return view('tendik/perizinan', [
            'title'      => 'Form Pengajuan Izin / Sakit',
            'isLoggedIn' => $isLoggedIn,
            'loggedData' => $loggedData
        ]);
    }

    public function submit()
    {
        $db         = \Config\Database::connect();
        $isLoggedIn = auth()->loggedIn();

        $idGuru   = null;
        $idTendik = null;

        if ($isLoggedIn) {
            // Otomatis deteksi dari Akun Login
            $user     = auth()->user();
            $idGuru   = $user->id_guru ?? null;
            $idTendik = $user->id_tendik ?? null;
        } else {
            // Verifikasi Manual jika diajukan sebelum Login
            $nama     = trim($this->request->getPost('nama') ?? '');
            $nuptkNip = trim($this->request->getPost('nuptk_nip') ?? '');

            if (empty($nama) || empty($nuptkNip)) {
                return redirect()->back()->withInput()->with('error', 'Nama Lengkap dan NUPTK/NIP wajib diisi!');
            }

            $guru = null;
            if ($db->tableExists('tb_guru')) {
                $guru = $db->table('tb_guru')
                    ->groupStart()->where('LOWER(nama_guru)', strtolower($nama))->orWhere('LOWER(nama)', strtolower($nama))->groupEnd()
                    ->groupStart()->where('nuptk', $nuptkNip)->orWhere('nip', $nuptkNip)->groupEnd()
                    ->get()->getRowArray();
            }

            $tendik = null;
            if (!$guru && $db->tableExists('tb_tendik')) {
                $tendik = $db->table('tb_tendik')
                    ->groupStart()->where('LOWER(nama_tendik)', strtolower($nama))->orWhere('LOWER(nama)', strtolower($nama))->groupEnd()
                    ->where('nip', $nuptkNip)
                    ->get()->getRowArray();
            }

            if (!$guru && !$tendik) {
                return redirect()->back()->withInput()->with('error', 'Data Nama dan NUPTK/NIP tidak cocok dengan data Admin!');
            }

            $idGuru   = $guru ? ($guru['id_guru'] ?? $guru['id'] ?? null) : null;
            $idTendik = $tendik ? ($tendik['id_tendik'] ?? $tendik['id'] ?? null) : null;
        }

        $jenisIzin  = $this->request->getPost('jenis_izin') ?? 'Izin';
        $tglMulai   = $this->request->getPost('tanggal_mulai') ?? date('Y-m-d');
        $tglSelesai = $this->request->getPost('tanggal_selesai') ?? $tglMulai;
        $keterangan = trim($this->request->getPost('keterangan') ?? '-');

        // Upload Bukti File
        $fileName  = null;
        $fileBukti = $this->request->getFile('bukti');
        if ($fileBukti && $fileBukti->isValid() && !$fileBukti->hasMoved()) {
            $fileName   = $fileBukti->getRandomName();
            $uploadPath = FCPATH . 'uploads/perizinan';
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }
            $fileBukti->move($uploadPath, $fileName);
        }

        $rawSave = [
            'id_guru'         => $idGuru,
            'id_tendik'       => $idTendik,
            'status'          => 'pending',
            'tanggal'         => $tglMulai,
            'tanggal_mulai'   => $tglMulai,
            'tanggal_selesai' => $tglSelesai,
            'tipe_izin'       => $jenisIzin,
            'jenis_izin'      => $jenisIzin,
            'keterangan'      => $keterangan,
            'alasan'          => $keterangan,
            'id_kehadiran'    => (strtolower($jenisIzin) === 'sakit') ? 2 : 3,
            'bukti'           => $fileName,
            'created_at'      => date('Y-m-d H:i:s')
        ];

        if ($db->tableExists('tb_perizinan')) {
            $existingFields = $db->getFieldNames('tb_perizinan');
            $dataSave = [];
            foreach ($rawSave as $col => $val) {
                if (in_array($col, $existingFields)) {
                    $dataSave[$col] = $val;
                }
            }
            $db->table('tb_perizinan')->insert($dataSave);

            if ($isLoggedIn) {
                $user = auth()->user();
                $targetUrl = $user->inGroup('guru') ? base_url('teacher/dashboard') : base_url('tendik/dashboard');
                return redirect()->to($targetUrl)->with('message', 'Pengajuan izin berhasil dikirim!');
            }

            return redirect()->to(base_url('login'))->with('message', 'Pengajuan izin berhasil dikirim! Silakan tunggu konfirmasi Admin.');
        }

        return redirect()->back()->withInput()->with('error', 'Gagal menyimpan perizinan.');
    }
}