<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\TendikModel;
use App\Models\JabatanModel;

class Jabatan extends BaseController
{
    protected $tendikModel;
    protected $jabatanModel;

    public function __construct()
    {
        $this->tendikModel = new TendikModel();
        $this->jabatanModel = new JabatanModel();
    }

    public function index()
    {
        $data = [
            'title'       => 'Data Jabatan Tendik',
            'listTendik'  => $this->tendikModel->findAll(),
            'listJabatan' => $this->jabatanModel->getJabatanWithTendik()
        ];

        return view('admin/jabatan/index', $data);
    }

    public function simpan()
    {
        $this->jabatanModel->save([
            'id_tendik'    => $this->request->getPost('id_tendik'),
            'nama_jabatan' => $this->request->getPost('nama_jabatan')
        ]);

        return redirect()->to('admin/jabatan')->with('msg', 'Jabatan berhasil ditambahkan!');
    }

    public function hapus($id)
    {
        $this->jabatanModel->delete($id);
        return redirect()->to('admin/jabatan')->with('msg', 'Jabatan berhasil dihapus!');
    }
}