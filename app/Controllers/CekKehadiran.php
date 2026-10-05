<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\GuruModel;
use App\Models\TendikModel;
use App\Models\PresensiGuruModel;
use App\Models\PresensiTendikModel;

class CekKehadiran extends BaseController
{
   protected GuruModel $guruModel;
   protected TendikModel $tendikModel;
   protected PresensiGuruModel $presensiGuruModel;
   protected PresensiTendikModel $presensiTendikModel;

   public function __construct()
   {
      $this->guruModel = new GuruModel();
      $this->tendikModel = new TendikModel();
      $this->presensiGuruModel = new PresensiGuruModel();
      $this->presensiTendikModel = new PresensiTendikModel();
      helper(['form', 'url']);
   }

   public function index()
   {
      $keyword = $this->request->getVar('keyword');
      $tanggal = $this->request->getVar('tanggal') ?? date('Y-m-d');

      $hasil = null;
      $tipe  = null;

      if ($keyword) {
         $db = \Config\Database::connect();

         // 1. Cari Data di Tendik
         $tendikBuilder = $this->tendikModel->groupStart();
         if ($db->fieldExists('nip', 'tb_tendik')) {
            $tendikBuilder->where('nip', $keyword);
         }
         if ($db->fieldExists('unique_code', 'tb_tendik')) {
            $tendikBuilder->orWhere('unique_code', $keyword);
         }
         if ($db->fieldExists('rfid_code', 'tb_tendik')) {
            $tendikBuilder->orWhere('rfid_code', $keyword);
         }
         if ($db->fieldExists('nama_tendik', 'tb_tendik')) {
            $tendikBuilder->orLike('nama_tendik', $keyword);
         } elseif ($db->fieldExists('nama', 'tb_tendik')) {
            $tendikBuilder->orLike('nama', $keyword);
         }
         $tendik = $tendikBuilder->groupEnd()->first();

         if ($tendik) {
            $tipe = 'tendik';
            $idTendik = $tendik['id_tendik'] ?? $tendik['id'] ?? 0;

            $presensi = method_exists($this->presensiTendikModel, 'getPresensiByIdTendikTanggal')
               ? $this->presensiTendikModel->getPresensiByIdTendikTanggal($idTendik, $tanggal)
               : $this->presensiTendikModel->where('id_tendik', $idTendik)->where('tanggal', $tanggal)->first();

            $hasil = [
               'pegawai'  => $tendik,
               'presensi' => $presensi
            ];
         } else {
            // 2. Jika Tidak Ada di Tendik, Cari di Guru
            $guruBuilder = $this->guruModel->groupStart();
            if ($db->fieldExists('nuptk', 'tb_guru')) {
               $guruBuilder->where('nuptk', $keyword);
            }
            if ($db->fieldExists('nip', 'tb_guru')) {
               $guruBuilder->orWhere('nip', $keyword);
            }
            if ($db->fieldExists('unique_code', 'tb_guru')) {
               $guruBuilder->orWhere('unique_code', $keyword);
            }
            if ($db->fieldExists('rfid_code', 'tb_guru')) {
               $guruBuilder->orWhere('rfid_code', $keyword);
            }
            if ($db->fieldExists('nama_guru', 'tb_guru')) {
               $guruBuilder->orLike('nama_guru', $keyword);
            } elseif ($db->fieldExists('nama', 'tb_guru')) {
               $guruBuilder->orLike('nama', $keyword);
            }
            $guru = $guruBuilder->groupEnd()->first();

            if ($guru) {
               $tipe = 'guru';
               $idGuru = $guru['id_guru'] ?? $guru['id'] ?? 0;

               $presensi = method_exists($this->presensiGuruModel, 'getPresensiByIdGuruTanggal')
                  ? $this->presensiGuruModel->getPresensiByIdGuruTanggal($idGuru, $tanggal)
                  : $this->presensiGuruModel->where('id_guru', $idGuru)->where('tanggal', $tanggal)->first();

               $hasil = [
                  'pegawai'  => $guru,
                  'presensi' => $presensi
               ];
            }
         }
      }

      $data = [
         'title'   => 'Cek Kehadiran Guru & Tendik',
         'keyword' => $keyword,
         'tanggal' => $tanggal,
         'tipe'    => $tipe,
         'hasil'   => $hasil
      ];

      return view('cek_kehadiran/index', $data);
   }
}