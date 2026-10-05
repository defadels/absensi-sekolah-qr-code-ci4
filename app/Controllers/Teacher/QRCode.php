<?php

namespace App\Controllers\Teacher;

use App\Controllers\BaseController;
use App\Models\GuruModel;

class QRCode extends BaseController
{
    protected GuruModel $guruModel;

    public function __construct()
    {
        $this->guruModel = new GuruModel();
    }

    private function getGuruData()
    {
        $db = \Config\Database::connect();
        
        $sessionUser = session()->get('username') 
                    ?? session()->get('nama_lengkap') 
                    ?? session()->get('nuptk') 
                    ?? session()->get('nip') 
                    ?? (function_exists('auth') && auth()->user() ? auth()->user()->username : '');

        $cleanName = trim(str_replace(['Guru_', 'Tendik_', 'guru_', 'tendik_'], '', $sessionUser));

        $guru = null;
        if ($db->tableExists('tb_guru')) {
            $builder = $db->table('tb_guru');
            $builder->groupStart();
            if ($db->fieldExists('nuptk', 'tb_guru')) $builder->where('nuptk', $sessionUser)->orWhere('nuptk', $cleanName);
            if ($db->fieldExists('nip', 'tb_guru')) $builder->orWhere('nip', $sessionUser)->orWhere('nip', $cleanName);
            if ($db->fieldExists('nama_guru', 'tb_guru')) $builder->orLike('nama_guru', $cleanName);
            $builder->groupEnd();

            $guru = $builder->get()->getRowArray();
            if (!$guru) {
                $guru = $db->table('tb_guru')->get()->getRowArray();
            }
        }
        return $guru;
    }

    public function index()
    {
        $guru = $this->getGuruData();
        if (!$guru) {
            return redirect()->to(base_url('teacher/dashboard'));
        }

        return view('teacher/qr/index', [
            'title'   => 'QR Code Saya',
            'ctx'     => 'teacher',
            'guru'    => $guru,
            'isPrint' => false
        ]);
    }

    public function download()
    {
        $guru = $this->getGuruData();
        if (!$guru) {
            return redirect()->to(base_url('teacher/dashboard'));
        }

        // Payload menggunakan NUPTK / NIP murni agar sinkron dengan scanner
        $qrData = trim($guru['nuptk'] ?? $guru['nip'] ?? $guru['id_guru'] ?? '');
        $qrUrl  = "https://api.qrserver.com/v1/create-qr-code/?size=500x500&data=" . urlencode($qrData);

        $imageContent = @file_get_contents($qrUrl);

        if ($imageContent) {
            $filename = 'QR_Code_' . preg_replace('/[^a-zA-Z0-9_]/', '_', $guru['nama_guru'] ?? 'Guru') . '.png';

            return $this->response
                ->setHeader('Content-Type', 'image/png')
                ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->setBody($imageContent);
        }

        return redirect()->to(base_url('teacher/qr'));
    }

    public function print()
    {
        $guru = $this->getGuruData();
        if (!$guru) {
            return redirect()->to(base_url('teacher/dashboard'));
        }

        return view('teacher/qr/index', [
            'title'   => 'Cetak QR Code',
            'ctx'     => 'teacher',
            'guru'    => $guru,
            'isPrint' => true
        ]);
    }
}