<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\GuruModel;
use App\Models\TendikModel;

class Perizinan extends BaseController
{
   protected GuruModel $guruModel;
   protected TendikModel $tendikModel;

   public function __construct()
   {
      $this->guruModel = new GuruModel();
      $this->tendikModel = new TendikModel();
      helper(['form', 'url', 'session']);
   }

   public function index()
   {
      $data = [
         'title' => 'Form Pengajuan Izin/Sakit'
      ];
      return view('perizinan/index', $data);
   }

   public function getSiswa()
   {
      $nis  = $this->request->getPost('nis');
      $type = $this->request->getPost('type') ?? 'tendik';

      if (empty($nis)) {
         return $this->response->setJSON([
            'status'  => 'error',
            'message' => 'NIP / NUPTK wajib diisi!'
         ]);
      }

      $db = \Config\Database::connect();

      if ($type === 'guru') {
         $builder = $this->guruModel->groupStart();
         if ($db->fieldExists('nuptk', 'tb_guru')) $builder->where('nuptk', $nis);
         if ($db->fieldExists('nip', 'tb_guru')) $builder->orWhere('nip', $nis);
         if ($db->fieldExists('unique_code', 'tb_guru')) $builder->orWhere('unique_code', $nis);
         $data = $builder->groupEnd()->first();

         if ($data) {
            return $this->response->setJSON([
               'status' => 'success',
               'data'   => [
                  'id'   => $data['id_guru'] ?? $data['id'] ?? 0,
                  'nama' => $data['nama_guru'] ?? $data['nama'] ?? 'Guru'
               ]
            ]);
         }
      } else {
         $builder = $this->tendikModel->groupStart();
         if ($db->fieldExists('nip', 'tb_tendik')) $builder->where('nip', $nis);
         if ($db->fieldExists('unique_code', 'tb_tendik')) $builder->orWhere('unique_code', $nis);
         $data = $builder->groupEnd()->first();

         if ($data) {
            return $this->response->setJSON([
               'status' => 'success',
               'data'   => [
                  'id'   => $data['id_tendik'] ?? $data['id'] ?? 0,
                  'nama' => $data['nama_tendik'] ?? $data['nama_lengkap'] ?? $data['nama'] ?? 'Tendik'
               ]
            ]);
         }
      }

      return $this->response->setJSON([
         'status'  => 'error',
         'message' => 'Data Tendik / Guru tidak ditemukan!'
      ]);
   }

   public function submit()
   {
      $db         = \Config\Database::connect();
      $session    = session();
      $tglMulai   = $this->request->getPost('tanggal_mulai') ?? $this->request->getPost('tanggal') ?? date('Y-m-d');
      $tglSelesai = $this->request->getPost('tanggal_selesai') ?? $tglMulai;
      $tipeIzin   = $this->request->getPost('tipe_izin') ?? 'Izin';
      $alasan     = $this->request->getPost('alasan') ?? $this->request->getPost('keterangan') ?? '-';
      $fileBukti  = $this->request->getFile('bukti');

      $idGuru   = $session->get('id_guru') ?? $session->get('guru_id');
      $idTendik = $session->get('id_tendik') ?? $session->get('tendik_id');

      // Ambil identitas user yang sedang aktif
      $userAuth = function_exists('auth') && auth()->loggedIn() ? auth()->user() : null;

      $identifiers = array_filter([
         $this->request->getPost('id_target'),
         $this->request->getPost('nis'),
         $session->get('id_tendik'),
         $session->get('id_guru'),
         $session->get('nip'),
         $session->get('nuptk'),
         $session->get('username'),
         $session->get('nama'),
         $session->get('user_id'),
         $userAuth->username ?? null,
         $userAuth->email ?? null,
         $userAuth->id ?? null,
      ]);

      if (empty($idGuru) && empty($idTendik)) {
         foreach ($identifiers as $val) {
            if (empty($val)) continue;

            // 1. Cari dulu ke tabel Tendik (karena Bagus Pratama adalah Tendik)
            if ($db->tableExists('tb_tendik')) {
               $b = $db->table('tb_tendik')->groupStart();
               if ($db->fieldExists('id_tendik', 'tb_tendik')) $b->orWhere('id_tendik', $val);
               if ($db->fieldExists('id', 'tb_tendik')) $b->orWhere('id', $val);
               if ($db->fieldExists('user_id', 'tb_tendik')) $b->orWhere('user_id', $val);
               if ($db->fieldExists('nip', 'tb_tendik')) $b->orWhere('nip', $val);
               if ($db->fieldExists('nama_tendik', 'tb_tendik')) $b->orWhere('nama_tendik', $val);
               if ($db->fieldExists('nama_lengkap', 'tb_tendik')) $b->orWhere('nama_lengkap', $val);
               if ($db->fieldExists('nama', 'tb_tendik')) $b->orWhere('nama', $val);
               $tendik = $b->groupEnd()->get()->getRowArray();

               if ($tendik) {
                  $idTendik = $tendik['id_tendik'] ?? $tendik['id'] ?? null;
                  break;
               }
            }

            // 2. Jika tidak ditemukan di tendik, cari di tabel Guru
            if ($db->tableExists('tb_guru')) {
               $bg = $db->table('tb_guru')->groupStart();
               if ($db->fieldExists('id_guru', 'tb_guru')) $bg->orWhere('id_guru', $val);
               if ($db->fieldExists('id', 'tb_guru')) $bg->orWhere('id', $val);
               if ($db->fieldExists('user_id', 'tb_guru')) $bg->orWhere('user_id', $val);
               if ($db->fieldExists('nip', 'tb_guru')) $bg->orWhere('nip', $val);
               if ($db->fieldExists('nuptk', 'tb_guru')) $bg->orWhere('nuptk', $val);
               if ($db->fieldExists('nama_guru', 'tb_guru')) $bg->orWhere('nama_guru', $val);
               if ($db->fieldExists('nama', 'tb_guru')) $bg->orWhere('nama', $val);
               $guru = $bg->groupEnd()->get()->getRowArray();

               if ($guru) {
                  $idGuru = $guru['id_guru'] ?? $guru['id'] ?? null;
                  break;
               }
            }
         }
      }

      // Process upload file bukti
      $fileName = null;
      if ($fileBukti && $fileBukti->isValid() && !$fileBukti->hasMoved()) {
         $fileName = $fileBukti->getRandomName();
         $uploadPath = FCPATH . 'uploads/perizinan';
         if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0777, true);
         }
         $fileBukti->move($uploadPath, $fileName);
      }

      $dataSave = [
         'id_guru'         => $idGuru,
         'id_tendik'       => $idTendik,
         'tanggal'         => $tglMulai,
         'tanggal_selesai' => $tglSelesai,
         'keterangan'      => $alasan,
         'status'          => 'pending',
         'id_kehadiran'    => (strtolower($tipeIzin) === 'sakit') ? 2 : 3,
         'bukti'           => $fileName
      ];

      if ($db->tableExists('tb_perizinan')) {
         $db->table('tb_perizinan')->insert($dataSave);
      }

      return redirect()->back()->with('success', 'Pengajuan izin berhasil dikirim! Silakan tunggu konfirmasi Admin.');
   }
}