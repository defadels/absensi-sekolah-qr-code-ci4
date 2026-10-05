<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\TendikModel;
use App\Models\KehadiranModel;
use App\Models\PresensiTendikModel;
use CodeIgniter\I18n\Time;

class DataAbsenTendik extends BaseController
{
    protected TendikModel $tendikModel;
    protected KehadiranModel $kehadiranModel;
    protected PresensiTendikModel $presensiTendik;
    protected string $currentDate;

    public function __construct()
    {
        $this->currentDate     = Time::today()->toDateString();
        $this->tendikModel     = new TendikModel();
        $this->kehadiranModel  = new KehadiranModel();
        $this->presensiTendik  = new PresensiTendikModel();
    }

    public function index()
    {
        $data = [
            'title'  => 'Data Absen Tendik',
            'ctx'    => 'absen-tendik',
            'tendik' => $this->tendikModel->findAll()
        ];

        return view('admin/absen/absen-tendik', $data);
    }

    public function ambilDataTendik()
    {
        try {
            $tanggal = $this->request->getVar('tanggal') ?? $this->currentDate;
            $lewat   = Time::parse($tanggal)->isAfter(Time::today());

            $db = \Config\Database::connect();
            $builder = $db->table('tb_tendik');
            $builder->select('tb_tendik.*, tb_presensi_tendik.id_presensi, tb_presensi_tendik.tanggal, tb_presensi_tendik.jam_masuk, tb_presensi_tendik.jam_keluar, tb_presensi_tendik.id_kehadiran, tb_presensi_tendik.keterangan, tb_kehadiran.kehadiran');
            $builder->join("tb_presensi_tendik", "tb_tendik.id_tendik = tb_presensi_tendik.id_tendik AND tb_presensi_tendik.tanggal = '$tanggal'", 'left');
            $builder->join('tb_kehadiran', 'tb_presensi_tendik.id_kehadiran = tb_kehadiran.id_kehadiran', 'left');
            $builder->orderBy("nama_tendik", "ASC");
            
            $result = $builder->get()->getResultArray();

            $data = [
                'data'          => $result,
                'tanggal'       => $tanggal,
                'listKehadiran' => $this->kehadiranModel->findAll(),
                'lewat'         => $lewat
            ];

            return view('admin/absen/list-absen-tendik', $data);
        } catch (\Exception $e) {
            return '<div class="alert alert-danger m-3">Terjadi Kesalahan Server: ' . $e->getMessage() . '</div>';
        }
    }

    public function ambilKehadiran()
    {
        $idPresensi = $this->request->getVar('id_presensi');
        $idTendik   = $this->request->getVar('id_tendik');

        $data = [
            'presensi'      => $this->presensiTendik->getPresensiById($idPresensi),
            'listKehadiran' => $this->kehadiranModel->findAll(),
            'data'          => $this->tendikModel->find($idTendik)
        ];

        return view('admin/absen/ubah-kehadiran-modal', $data);
    }

    public function ubahKehadiran()
    {
        $idKehadiran = $this->request->getVar('id_kehadiran');
        $idTendik    = $this->request->getVar('id_tendik');
        $tanggal     = $this->request->getVar('tanggal');
        $jamMasuk    = $this->request->getVar('jam_masuk');
        $jamKeluar   = $this->request->getVar('jam_keluar');
        $keterangan  = $this->request->getVar('keterangan');

        $cek     = $this->presensiTendik->cekAbsen($idTendik, $tanggal);
        $oldData = $cek ? $this->presensiTendik->find($cek) : null;

        $result = $this->presensiTendik->updatePresensi(
            $cek == false ? NULL : $cek,
            $idTendik,
            $tanggal,
            $idKehadiran,
            $jamMasuk ?? NULL,
            $jamKeluar ?? NULL,
            $keterangan
        );

        $tendikData = $this->tendikModel->find($idTendik);
        $namaTendik = $tendikData['nama_tendik'] ?? $tendikData['nama_lengkap'] ?? 'Tendik';

        $response['nama_tendik'] = $namaTendik;

        if ($result) {
            $auditLogModel = new \App\Models\AuditLogModel();
            $newData = [
                'id_kehadiran' => $idKehadiran,
                'jam_masuk'    => $jamMasuk,
                'jam_keluar'   => $jamKeluar,
                'keterangan'   => $keterangan
            ];

            if (class_exists('App\Models\AuditLogModel') && method_exists($auditLogModel, 'log')) {
                $auditLogModel->log("Ubah Kehadiran Tendik: {$namaTendik}", "tb_presensi_tendik", $cek ?: null, $oldData, $newData);
            }

            $response['status'] = TRUE;
        } else {
            $response['status'] = FALSE;
        }

        return $this->response->setJSON($response);
    }
}