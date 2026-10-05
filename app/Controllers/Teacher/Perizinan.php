<?php

namespace App\Controllers\Teacher;

use App\Controllers\BaseController;

class Perizinan extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        
        $sessionUser = session()->get('username') ?? session()->get('nuptk') ?? session()->get('nip') ?? 'KIKI HANDAYANI';
        $namaGuru    = 'KIKI HANDAYANI';

        if ($db->tableExists('tb_guru')) {
            $builder = $db->table('tb_guru');
            if ($db->fieldExists('nuptk', 'tb_guru')) {
                $builder->where('nuptk', $sessionUser);
            }
            if ($db->fieldExists('nip', 'tb_guru')) {
                $builder->orWhere('nip', $sessionUser);
            }
            if ($db->fieldExists('nama_guru', 'tb_guru')) {
                $builder->orLike('nama_guru', $sessionUser);
            }
            $guru = $builder->get()->getRowArray();
            if (!$guru) {
                $guru = $db->table('tb_guru')->get()->getRowArray();
            }
            if ($guru) {
                $namaGuru = $guru['nama_guru'] ?? $guru['nama'] ?? 'KIKI HANDAYANI';
            }
        }

        $csrfField = csrf_field();
        $actionUrl = base_url('teacher/perizinan/submit');
        $backUrl   = base_url('teacher/attendance');
        $today     = date('Y-m-d');

        return <<<HTML
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Pengajuan Izin Guru</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f1f5f9; font-family: system-ui, -apple-system, sans-serif; padding: 35px 15px; }
        .form-card { max-width: 520px; margin: auto; background: #fff; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); overflow: hidden; border: 1px solid #e2e8f0; }
        .form-header { background: #1565c0; color: #fff; padding: 18px 22px; }
        .btn-submit { background: #1565c0; color: #fff; font-weight: 600; padding: 12px; border-radius: 8px; border: none; width: 100%; }
        .btn-submit:hover { background: #0d47a1; color: #fff; }
    </style>
</head>
<body>
<div class="form-card">
    <div class="form-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0 fw-bold">Pengajuan Izin / Sakit Guru</h5>
            <small class="opacity-75">Pengaju: <b>{$namaGuru}</b></small>
        </div>
        <a href="{$backUrl}" class="btn btn-sm btn-light text-dark fw-bold">Kembali</a>
    </div>
    <div class="p-4">
        <form action="{$actionUrl}" method="POST" enctype="multipart/form-data">
            {$csrfField}
            
            <div class="mb-3">
                <label class="form-label fw-bold small">Jenis Izin <span class="text-danger">*</span></label>
                <select name="jenis_izin" class="form-select" required>
                    <option value="Izin">Izin Tidak Masuk</option>
                    <option value="Sakit">Sakit</option>
                    <option value="Cuti">Cuti</option>
                    <option value="Dinas Luar">Tugas Dinas Luar</option>
                </select>
            </div>

            <div class="row">
                <div class="col-6 mb-3">
                    <label class="form-label fw-bold small">Tanggal Mulai <span class="text-danger">*</span></label>
                    <input type="date" name="tanggal_mulai" class="form-control" value="{$today}" required>
                </div>
                <div class="col-6 mb-3">
                    <label class="form-label fw-bold small">Tanggal Selesai <span class="text-danger">*</span></label>
                    <input type="date" name="tanggal_selesai" class="form-control" value="{$today}" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold small">Alasan / Keterangan <span class="text-danger">*</span></label>
                <textarea name="keterangan" class="form-control" rows="3" placeholder="Tuliskan keterangan perizinan..." required></textarea>
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold small">Upload Bukti <small class="text-muted fw-normal">(Opsional / Surat Dokter/Tugas)</small></label>
                <input type="file" name="bukti" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
            </div>

            <button type="submit" class="btn btn-submit">Kirim Pengajuan Izin</button>
        </form>
    </div>
</div>
</body>
</html>
HTML;
    }

    public function submit()
    {
        $db       = \Config\Database::connect();
        $session  = session();
        $userSess = $session->get('username') ?? $session->get('nuptk') ?? $session->get('nip') ?? 'KIKI HANDAYANI';
        $cleanName = trim(str_replace(['Guru_', 'Tendik_', 'guru_', 'tendik_'], '', $userSess));
        $idGuru   = $session->get('id_guru') ?? 1;

        if ($db->tableExists('tb_guru')) {
            $pk      = $db->fieldExists('id_guru', 'tb_guru') ? 'id_guru' : 'id';
            $builder = $db->table('tb_guru');
            
            if ($db->fieldExists('nuptk', 'tb_guru')) {
                $builder->where('nuptk', $userSess)->orWhere('nuptk', $cleanName);
            }
            if ($db->fieldExists('nip', 'tb_guru')) {
                $builder->orWhere('nip', $userSess)->orWhere('nip', $cleanName);
            }
            if ($db->fieldExists('nama_guru', 'tb_guru')) {
                $builder->orLike('nama_guru', $cleanName);
            }
            
            $row = $builder->get()->getRowArray();
            if (!$row) {
                $row = $db->table('tb_guru')->get()->getRowArray();
            }
            if ($row) {
                $idGuru = $row[$pk];
            }
        }

        $jenisIzin      = $this->request->getPost('jenis_izin') ?? 'Izin';
        $tanggalMulai   = $this->request->getPost('tanggal_mulai') ?? date('Y-m-d');
        $tanggalSelesai = $this->request->getPost('tanggal_selesai') ?? $tanggalMulai;
        $keterangan     = $this->request->getPost('keterangan') ?? '-';

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
            'id_tendik'       => null,
            'status'          => 'pending',
            'tanggal'         => $tanggalMulai,
            'tanggal_mulai'   => $tanggalMulai,
            'tanggal_selesai' => $tanggalSelesai,
            'tipe_izin'       => $jenisIzin,
            'jenis_izin'      => $jenisIzin,
            'keterangan'      => $keterangan,
            'alasan'          => $keterangan,
            'id_kehadiran'    => (strtolower($jenisIzin) === 'sakit') ? 2 : 3,
            'bukti'           => $fileName
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
        }

        return redirect()->to(base_url('teacher/attendance'))->with('success', 'Pengajuan izin Guru berhasil dikirim!');
    }
}