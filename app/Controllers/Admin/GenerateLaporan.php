<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\GuruModel;
use App\Models\TendikModel;

class GenerateLaporan extends BaseController
{
   protected $guruModel;
   protected $tendikModel;

   public function __construct()
   {
      $this->guruModel = new GuruModel();
      $this->tendikModel = new TendikModel();
      helper(['form', 'url', 'user_helper']);
   }

   public function index()
   {
      $data = [
         'title'  => 'Generate Laporan',
         'guru'   => $this->guruModel->findAll(),
         'tendik' => $this->tendikModel->findAll(),
      ];

      return view('admin/generate-laporan/index', $data);
   }

   public function guru()
   {
      return $this->prosesLaporan('guru');
   }

   public function generateLaporanGuru()
   {
      return $this->prosesLaporan('guru');
   }

   public function tendik()
   {
      return $this->prosesLaporan('tendik');
   }

   public function generateLaporanTendik()
   {
      return $this->prosesLaporan('tendik');
   }

   private function prosesLaporan($grup)
   {
      $db = \Config\Database::connect();
      $isGuru = ($grup === 'guru');

      $inputBulan = $this->request->getPost($isGuru ? 'tanggalGuru' : 'tanggalTendik') ?? date('Y-m');
      $type       = $this->request->getPost('type') ?? 'pdf';

      $listData = $isGuru ? $this->guruModel->findAll() : $this->tendikModel->findAll();

      $jumlahLaki = 0;
      $jumlahPerempuan = 0;
      foreach ($listData as $row) {
         $gender = strtolower($row['jenis_kelamin'] ?? $row['gender'] ?? 'L');
         if ($gender === 'p' || $gender === 'perempuan') {
            $jumlahPerempuan++;
         } else {
            $jumlahLaki++;
         }
      }

      $startOfMonth = strtotime($inputBulan . '-01');
      $endOfMonth   = strtotime('last day of ' . $inputBulan);
      $tanggal      = [];
      
      $current = $startOfMonth;
      while ($current <= $endOfMonth) {
         $tanggal[] = \CodeIgniter\I18n\Time::parse(date('Y-m-d', $current));
         $current = strtotime('+1 day', $current);
      }

      $listAbsen = [];
      $tablePresensi = $isGuru ? 'tb_presensi_guru' : 'tb_presensi_tendik';
      $tablePresensiExists = $db->tableExists($tablePresensi);
      $kolomId = $isGuru ? 'id_guru' : 'id_tendik';

      foreach ($tanggal as $tglObj) {
         $dateStr = $tglObj->format('Y-m-d');
         $rowAbsen = [];
         $isLewat = (strtotime($dateStr) > time());

         foreach ($listData as $idx => $item) {
            $idItem = $item[$kolomId] ?? $item['id'] ?? 0;
            $kehadiran = null;

            if ($tablePresensiExists && !$isLewat) {
               $presensi = $db->table($tablePresensi)
                             ->where($kolomId, $idItem)
                             ->where('tanggal', $dateStr)
                             ->get()
                             ->getRowArray();
               if ($presensi) {
                  $kehadiran = $presensi['id_kehadiran'] ?? null;
               }
            }

            $rowAbsen[$idx] = [
               'id_kehadiran' => $kehadiran
            ];
         }

         $listAbsen[] = [
            'lewat' => $isLewat,
            ...$rowAbsen
         ];
      }

      // SINKRONISASI DATA PERIZINAN KE MATRIKS LAPORAN
      if ($db->tableExists('tb_perizinan')) {
         $perizinanList = $db->table('tb_perizinan')
                             ->groupStart()
                                ->where('status', 'diterima')
                                ->orWhere('status', 'disetujui')
                             ->groupEnd()
                             ->get()
                             ->getResultArray();

         foreach ($perizinanList as $izin) {
            $idPerizinanUser = $izin[$kolomId] ?? null;
            $tglMulai        = $izin['tanggal'];
            $tglSelesai      = $izin['tanggal_selesai'] ?? $tglMulai;
            $idKehadiran     = $izin['id_kehadiran'] ?? 2; 

            if (!empty($idPerizinanUser)) {
               foreach ($listData as $idxItem => $item) {
                  $idU = $item[$kolomId] ?? $item['id'] ?? null;
                  if ($idU == $idPerizinanUser) {
                     foreach ($tanggal as $idxTanggal => $objTanggal) {
                        $strTgl = $objTanggal->format('Y-m-d');
                        if ($strTgl >= $tglMulai && $strTgl <= $tglSelesai) {
                           if (isset($listAbsen[$idxTanggal][$idxItem])) {
                              $listAbsen[$idxTanggal][$idxItem]['id_kehadiran'] = (int)$idKehadiran;
                           }
                        }
                     }
                  }
               }
            }
         }
      }

      $tableName = $db->tableExists('general_settings') ? 'general_settings' : ($db->tableExists('tb_pengaturan') ? 'tb_pengaturan' : 'tb_general_settings');
      $generalSettings = $db->tableExists($tableName) ? $db->table($tableName)->get()->getFirstRow() : null;

      $data = [
         'title'           => $isGuru ? 'Laporan Absensi Guru' : 'Laporan Absensi Tendik',
         'bulan'           => date('F Y', strtotime($inputBulan . '-01')),
         'tanggal'         => $tanggal,
         'listAbsen'       => $listAbsen,
         'generalSettings' => $generalSettings,
         'grup'            => $isGuru ? 'Guru' : 'Tendik',
         'type'            => $type
      ];

      // JIKA USER MEMILIH GENERATE DOC (Word Download)
      if ($type === 'doc') {
         $filename = "Laporan_Absensi_{$grup}_" . date('Y_m', strtotime($inputBulan)) . ".doc";
         header("Content-Type: application/vnd.ms-word");
         header("Content-Disposition: attachment; filename=\"$filename\"");
         header("Pragma: no-cache");
         header("Expires: 0");
      }

      if ($isGuru) {
         $data['listGuru'] = $listData;
         $data['jumlahGuru'] = ['laki' => $jumlahLaki, 'perempuan' => $jumlahPerempuan];
         return view('admin/generate-laporan/laporan-guru', $data);
      } else {
         $data['listTendik'] = $listData;
         $data['jumlahTendik'] = ['laki' => $jumlahLaki, 'perempuan' => $jumlahPerempuan];
         return view('admin/generate-laporan/laporan-tendik', $data);
      }
   }
}