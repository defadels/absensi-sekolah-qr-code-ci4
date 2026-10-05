<?php

namespace App\Controllers\Tendik;

use App\Controllers\BaseController;
use App\Models\TendikModel;

class QRCode extends BaseController
{
    protected TendikModel $tendikModel;

    public function __construct()
    {
        $this->tendikModel = new TendikModel();
    }

    private function getTendikData()
    {
        $db = \Config\Database::connect();
        
        $sessionUser = session()->get('username') 
                    ?? session()->get('nama_lengkap') 
                    ?? session()->get('nip') 
                    ?? (function_exists('auth') && auth()->user() ? auth()->user()->username : '');

        $cleanName = trim(str_replace(['Guru_', 'Tendik_', 'guru_', 'tendik_'], '', $sessionUser));

        $tendik = null;
        if ($db->tableExists('tb_tendik')) {
            $builder = $db->table('tb_tendik');
            $builder->groupStart();
            if ($db->fieldExists('nip', 'tb_tendik')) $builder->where('nip', $sessionUser)->orWhere('nip', $cleanName);
            if ($db->fieldExists('nama_tendik', 'tb_tendik')) $builder->orLike('nama_tendik', $cleanName);
            $builder->groupEnd();

            $tendik = $builder->get()->getRowArray();
            if (!$tendik) {
                $tendik = $db->table('tb_tendik')->get()->getRowArray();
            }
        }
        return $tendik;
    }

    public function index()
    {
        $tendik = $this->getTendikData();
        if (!$tendik) {
            return redirect()->to(base_url('tendik/dashboard'));
        }

        return view('tendik/qr/index', [
            'title'   => 'QR Code Saya',
            'ctx'     => 'tendik',
            'tendik'  => $tendik,
            'isPrint' => false
        ]);
    }

    public function download()
    {
        $tendik = $this->getTendikData();
        if (!$tendik) {
            return redirect()->to(base_url('tendik/dashboard'));
        }

        // Payload menggunakan NIP murni agar sinkron dengan scanner
        $qrData = trim($tendik['nip'] ?? $tendik['id_tendik'] ?? '');
        $qrUrl  = "https://api.qrserver.com/v1/create-qr-code/?size=500x500&data=" . urlencode($qrData);

        $imageContent = @file_get_contents($qrUrl);

        if ($imageContent) {
            $filename = 'QR_Code_' . preg_replace('/[^a-zA-Z0-9_]/', '_', $tendik['nama_tendik'] ?? 'Tendik') . '.png';

            return $this->response
                ->setHeader('Content-Type', 'image/png')
                ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->setBody($imageContent);
        }

        return redirect()->to(base_url('tendik/qr'));
    }

    public function print()
    {
        $tendik = $this->getTendikData();
        if (!$tendik) {
            return redirect()->to(base_url('tendik/dashboard'));
        }

        return view('tendik/qr/index', [
            'title'   => 'Cetak QR Code',
            'ctx'     => 'tendik',
            'tendik'  => $tendik,
            'isPrint' => true
        ]);
    }
}