<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class GeneralSettings extends BaseController
{
    // Fungsi untuk menampilkan halaman
    public function index()
    {
        $db = \Config\Database::connect();
        $tableName = $db->tableExists('general_settings') ? 'general_settings' : 'tb_pengaturan';
        
        $generalSettings = $db->table($tableName)->get()->getFirstRow();

        $data = [
            'title'           => 'Pengaturan Utama',
            'generalSettings' => $generalSettings
        ];

        return view('admin/general_settings', $data);
    }

    // Fungsi untuk memproses penyimpanan (NAMA FUNGSI INI YANG DICARI SISTEM ANDA)
    public function generalSettingsPost()
    {
        $db = \Config\Database::connect();
        $tableName = $db->tableExists('general_settings') ? 'general_settings' : 'tb_pengaturan';

        // Cek & Buat Kolom 'logo' otomatis jika belum ada di database
        if (!$db->fieldExists('logo', $tableName)) {
            $forge = \Config\Database::forge();
            $forge->addColumn($tableName, [
                'logo' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true]
            ]);
        }

        $builder = $db->table($tableName);

        // Ambil Input Text Form
        $data = [
            'school_name'         => $this->request->getPost('school_name'),
            'school_year'         => $this->request->getPost('school_year'),
            'jam_masuk_limit'     => $this->request->getPost('jam_masuk_limit'),
            'jam_pulang_standard' => $this->request->getPost('jam_pulang_standard'),
            'latitude'            => $this->request->getPost('latitude'),
            'longitude'           => $this->request->getPost('longitude'),
            'radius'              => $this->request->getPost('radius'),
            'copyright'           => $this->request->getPost('copyright'),
        ];

        // Olah Hari Kerja (Checkbox ke bentuk teks dipisah koma)
        $hariKerjaArr = $this->request->getPost('hari_kerja');
        $data['hari_kerja'] = is_array($hariKerjaArr) ? implode(',', $hariKerjaArr) : '';

        // Olah File Gambar Logo
        $fileLogo = $this->request->getFile('logo');
        if ($fileLogo && $fileLogo->isValid() && !$fileLogo->hasMoved()) {
            $newName = $fileLogo->getRandomName();
            $uploadPath = FCPATH . 'uploads/logo';
            
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }
            
            $fileLogo->move($uploadPath, $newName);
            $data['logo'] = $newName; 
        }

        // Simpan Ke Database
        try {
            $existing = $builder->get()->getFirstRow('array');

            if ($existing) {
                // Ambil Kunci Utama (Primary Key) dari kolom pertama secara dinamis
                $primaryKey = array_key_first($existing);
                $builder->where($primaryKey, $existing[$primaryKey])->update($data);
            } else {
                $builder->insert($data);
            }

            return redirect()->to(base_url('admin/general-settings'))->with('message', 'Pengaturan & Logo Sekolah berhasil disimpan!');

        } catch (\Exception $e) {
            return redirect()->to(base_url('admin/general-settings'))->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }
}