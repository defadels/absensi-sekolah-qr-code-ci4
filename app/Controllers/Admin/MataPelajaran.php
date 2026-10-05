<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\GuruModel;
use App\Models\MapelModel;

class MataPelajaran extends BaseController
{
    protected $guruModel;
    protected $mapelModel;

    public function __construct()
    {
        $this->guruModel = new GuruModel();
        $this->mapelModel = new MapelModel();
    }

    public function index()
    {
        $data = [
            'title'     => 'Data Mata Pelajaran',
            'listGuru'  => $this->guruModel->findAll(),
            'listMapel' => $this->mapelModel->getMapelWithGuru()
        ];

        return view('admin/mata-pelajaran/index', $data);
    }

    public function simpan()
    {
        $this->mapelModel->save([
            'id_guru'    => $this->request->getPost('id_guru'),
            'nama_mapel' => $this->request->getPost('nama_mapel')
        ]);

        return redirect()->to('admin/mata-pelajaran')->with('msg', 'Mata pelajaran berhasil ditambahkan!');
    }

    public function hapus($id)
    {
        $this->mapelModel->delete($id);
        return redirect()->to('admin/mata-pelajaran')->with('msg', 'Mata pelajaran berhasil dihapus!');
    }
}