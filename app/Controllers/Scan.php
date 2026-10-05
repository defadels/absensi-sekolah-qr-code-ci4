<?php

namespace App\Controllers;

use CodeIgniter\I18n\Time;
use App\Models\GuruModel;
use App\Models\TendikModel;
use App\Models\PresensiGuruModel;
use App\Models\PresensiTendikModel;
use App\Models\HariLiburModel;

class Scan extends BaseController
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
   }

   public function index($t = 'Masuk')
   {
      $data = ['waktu' => ucfirst($t), 'title' => 'Absensi Guru dan Tendik Berbasis QR Code'];
      return view('scan/scan', $data);
   }

   public function cekKode()
   {
      try {
         $db = \Config\Database::connect();
         $holidayModel = new HariLiburModel();
         $today = date('Y-m-d');
         
         if ($db->tableExists('tb_hari_libur')) {
            $holiday = $holidayModel->where('tanggal', $today)->first();
            if ($holiday) {
               return $this->showErrorView("Hari ini sistem presensi dinonaktifkan karena: " . ($holiday['keterangan'] ?? 'Hari Libur'));
            }
         }

         $rawInput = $this->request->getVar('unique_code') 
                  ?? $this->request->getVar('kode') 
                  ?? $this->request->getVar('qr_code') 
                  ?? $this->request->getVar('rfid') 
                  ?? $this->request->getVar('content') 
                  ?? '';

         $waktuAbsen = strtolower($this->request->getVar('waktu') ?? 'masuk');

         if (empty($rawInput)) {
            return $this->showErrorView('Kode QR / RFID tidak terbaca oleh sistem.');
         }

         $cleanCode = trim((string)$rawInput);
         if (filter_var($cleanCode, FILTER_VALIDATE_URL)) {
            $urlParts = explode('/', rtrim($cleanCode, '/'));
            $cleanCode = end($urlParts);
         }
         
         $strippedCode = trim(str_replace(['GURU_', 'TENDIK_', 'guru_', 'tendik_', 'QR_', 'qr_'], '', $cleanCode));

         $type = null;
         $result = null;

         // 1. PENCARIAN KE TABEL GURU (Guru menggunakan NUPTK)
         if ($db->tableExists('tb_guru')) {
            $builderG = $db->table('tb_guru');
            $builderG->groupStart();
            $hasQuery = false;

            if ($db->fieldExists('nuptk', 'tb_guru')) {
               $builderG->where('nuptk', $cleanCode)->orWhere('nuptk', $strippedCode);
               $hasQuery = true;
            }
            if ($db->fieldExists('unique_code', 'tb_guru')) {
               if (!$hasQuery) {
                  $builderG->where('unique_code', $cleanCode)->orWhere('unique_code', $strippedCode);
                  $hasQuery = true;
               } else {
                  $builderG->orWhere('unique_code', $cleanCode)->orWhere('unique_code', $strippedCode);
               }
            }
            if ($db->fieldExists('id_guru', 'tb_guru') && is_numeric($strippedCode)) {
               if (!$hasQuery) {
                  $builderG->where('id_guru', (int)$strippedCode);
               } else {
                  $builderG->orWhere('id_guru', (int)$strippedCode);
               }
            }

            $builderG->groupEnd();
            $result = $builderG->get()->getRowArray();

            if (!empty($result)) {
               $type = 'Guru';
            }
         }

         // 2. PENCARIAN KE TABEL TENDIK (Tendik menggunakan NIP)
         if (empty($result) && $db->tableExists('tb_tendik')) {
            $builderT = $db->table('tb_tendik');
            $builderT->groupStart();
            $hasQueryT = false;

            if ($db->fieldExists('nip', 'tb_tendik')) {
               $builderT->where('nip', $cleanCode)->orWhere('nip', $strippedCode);
               $hasQueryT = true;
            }
            if ($db->fieldExists('unique_code', 'tb_tendik')) {
               if (!$hasQueryT) {
                  $builderT->where('unique_code', $cleanCode)->orWhere('unique_code', $strippedCode);
                  $hasQueryT = true;
               } else {
                  $builderT->orWhere('unique_code', $cleanCode)->orWhere('unique_code', $strippedCode);
               }
            }
            if ($db->fieldExists('id_tendik', 'tb_tendik') && is_numeric($strippedCode)) {
               if (!$hasQueryT) {
                  $builderT->where('id_tendik', (int)$strippedCode);
               } else {
                  $builderT->orWhere('id_tendik', (int)$strippedCode);
               }
            }

            $builderT->groupEnd();
            $result = $builderT->get()->getRowArray();

            if (!empty($result)) {
               $type = 'Tendik';
            }
         }

         if (empty($result) || empty($type)) {
            return $this->showErrorView('Data pegawai tidak terdaftar untuk kode: ' . esc($cleanCode));
         }

         if ($waktuAbsen === 'masuk') {
            return $this->absenMasuk($type, $result);
         } elseif ($waktuAbsen === 'pulang') {
            return $this->absenPulang($type, $result);
         } else {
            return $this->showErrorView('Waktu absensi tidak valid.');
         }

      } catch (\Throwable $e) {
         log_message('error', 'Error scan cekKode: ' . $e->getMessage());
         return $this->showErrorView('Terjadi kesalahan server: ' . $e->getMessage());
      }
   }

   public function absenMasuk($type, $result)
   {
      $db   = \Config\Database::connect();
      $data['waktu'] = 'masuk';
      $date = Time::today()->toDateString();
      $time = Time::now()->toTimeString();

      if ($type === 'Tendik') {
         $idTendik = $result['id_tendik'] ?? $result['id'] ?? 1;
         $data['type'] = 'Tendik';
         $namaAsli = $result['nama_tendik'] ?? $result['nama'] ?? 'Tendik';
         $result['nama_tendik'] = $namaAsli;
         $data['data'] = $result;

         $cek = $db->table('tb_presensi_tendik')->where(['id_tendik' => $idTendik, 'tanggal' => $date])->get()->getRowArray();
         if ($cek && !empty($cek['jam_masuk']) && $cek['jam_masuk'] !== '00:00:00' && $cek['jam_masuk'] !== '-') {
            $cek['nama_tendik'] = $namaAsli;
            $data['presensi'] = $cek;
            return $this->showErrorView('Anda sudah absen masuk hari ini', $data);
         }

         if ($cek) {
            $db->table('tb_presensi_tendik')->where(['id_tendik' => $idTendik, 'tanggal' => $date])->update([
               'jam_masuk'    => $time,
               'id_kehadiran' => 1
            ]);
         } else {
            $fields = $db->getFieldNames('tb_presensi_tendik');
            $insertData = [
               'id_tendik'    => $idTendik,
               'tanggal'      => $date,
               'jam_masuk'    => $time,
               'id_kehadiran' => 1
            ];
            $insertData = array_filter($insertData, fn($k) => in_array($k, $fields), ARRAY_FILTER_USE_KEY);
            $db->table('tb_presensi_tendik')->insert($insertData);
         }

         $presensi = $db->table('tb_presensi_tendik')->where(['id_tendik' => $idTendik, 'tanggal' => $date])->get()->getRowArray();
         $presensi['nama_tendik'] = $namaAsli;
         $data['presensi'] = $presensi;

      } else {
         $idGuru = $result['id_guru'] ?? $result['id'] ?? 1;
         $data['type'] = 'Guru';
         $namaAsli = $result['nama_guru'] ?? $result['nama'] ?? 'Guru';
         $result['nama_guru'] = $namaAsli;
         $data['data'] = $result; 

         $cek = $db->table('tb_presensi_guru')->where(['id_guru' => $idGuru, 'tanggal' => $date])->get()->getRowArray();
         if ($cek && !empty($cek['jam_masuk']) && $cek['jam_masuk'] !== '00:00:00' && $cek['jam_masuk'] !== '-') {
            $cek['nama_guru'] = $namaAsli;
            $data['presensi'] = $cek;
            return $this->showErrorView('Anda sudah absen masuk hari ini', $data);
         }

         if ($cek) {
            $db->table('tb_presensi_guru')->where(['id_guru' => $idGuru, 'tanggal' => $date])->update([
               'jam_masuk'    => $time,
               'id_kehadiran' => 1
            ]);
         } else {
            $fields = $db->getFieldNames('tb_presensi_guru');
            $insertData = [
               'id_guru'      => $idGuru,
               'tanggal'      => $date,
               'jam_masuk'    => $time,
               'id_kehadiran' => 1
            ];
            $insertData = array_filter($insertData, fn($k) => in_array($k, $fields), ARRAY_FILTER_USE_KEY);
            $db->table('tb_presensi_guru')->insert($insertData);
         }

         $presensi = $db->table('tb_presensi_guru')->where(['id_guru' => $idGuru, 'tanggal' => $date])->get()->getRowArray();
         $presensi['nama_guru'] = $namaAsli;
         $data['presensi'] = $presensi;
      }

      return view('scan/scan-result', $data);
   }

   public function absenPulang($type, $result)
   {
      $db   = \Config\Database::connect();
      $data['waktu'] = 'pulang';
      $date = Time::today()->toDateString();
      $time = Time::now()->toTimeString();

      if ($type === 'Tendik') {
         $idTendik = $result['id_tendik'] ?? $result['id'] ?? 1;
         $data['type'] = 'Tendik';
         $namaAsli = $result['nama_tendik'] ?? $result['nama'] ?? 'Tendik';
         $result['nama_tendik'] = $namaAsli;
         $data['data'] = $result;

         $cek = $db->table('tb_presensi_tendik')->where(['id_tendik' => $idTendik, 'tanggal' => $date])->get()->getRowArray();
         if (!$cek) {
            return $this->showErrorView('Anda belum absen masuk hari ini', $data);
         }

         $db->table('tb_presensi_tendik')->where(['id_tendik' => $idTendik, 'tanggal' => $date])->update([
            'jam_keluar'   => $time,
            'id_kehadiran' => 1
         ]);

         $presensi = $db->table('tb_presensi_tendik')->where(['id_tendik' => $idTendik, 'tanggal' => $date])->get()->getRowArray();
         $presensi['nama_tendik'] = $namaAsli;
         $data['presensi'] = $presensi;

      } else {
         $idGuru = $result['id_guru'] ?? $result['id'] ?? 1;
         $data['type'] = 'Guru';
         $namaAsli = $result['nama_guru'] ?? $result['nama'] ?? 'Guru';
         $result['nama_guru'] = $namaAsli;
         $data['data'] = $result;

         $cek = $db->table('tb_presensi_guru')->where(['id_guru' => $idGuru, 'tanggal' => $date])->get()->getRowArray();
         if (!$cek) {
            return $this->showErrorView('Anda belum absen masuk hari ini', $data);
         }

         $db->table('tb_presensi_guru')->where(['id_guru' => $idGuru, 'tanggal' => $date])->update([
            'jam_keluar'   => $time,
            'id_kehadiran' => 1
         ]);

         $presensi = $db->table('tb_presensi_guru')->where(['id_guru' => $idGuru, 'tanggal' => $date])->get()->getRowArray();
         $presensi['nama_guru'] = $namaAsli;
         $data['presensi'] = $presensi;
      }

      return view('scan/scan-result', $data);
   }

   public function showErrorView(string $msg = 'no error message', $data = NULL)
   {
      $errdata = $data ?? [];
      $errdata['msg'] = $msg;
      return view('scan/error-scan-result', $errdata);
   }
}