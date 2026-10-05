<?php

namespace App\Models;

use CodeIgniter\Model;

class GuruModel extends Model
{
   protected $allowedFields = [
      'nuptk',
      'nama_guru',
      'jenis_kelamin',
      'alamat',
      'no_hp',
      'unique_code',
      'rfid_code'
   ];

   protected $table = 'tb_guru';
   protected $primaryKey = 'id_guru';

   public function cekGuru(string $unique_code)
   {
      return $this->where(['unique_code' => $unique_code])
         ->orWhere(['rfid_code' => $unique_code])
         ->first();
   }

   public function getAllGuru()
   {
      $db = \Config\Database::connect();

      return $db->table('tb_guru')
         ->select('tb_guru.*, tb_mapel.nama_mapel, tb_mapel.nama_mapel AS mapel, tb_mapel.nama_mapel AS mata_pelajaran')
         ->join('tb_mapel', 'tb_mapel.id_guru = tb_guru.id_guru', 'left')
         ->orderBy('tb_guru.id_guru', 'DESC')
         ->get()
         ->getResultArray();
   }

   public function getGuruById($id)
   {
      $db = \Config\Database::connect();

      return $db->table('tb_guru')
         ->select('tb_guru.*, tb_mapel.nama_mapel, tb_mapel.nama_mapel AS mapel, tb_mapel.nama_mapel AS mata_pelajaran')
         ->join('tb_mapel', 'tb_mapel.id_guru = tb_guru.id_guru', 'left')
         ->where('tb_guru.id_guru', $id)
         ->get()
         ->getRowArray();
   }

   public function createGuru($nuptk, $nama, $jenisKelamin, $alamat, $noHp, $rfid = null)
   {
      return $this->insert([
         'nuptk'         => $nuptk,
         'nama_guru'     => $nama,
         'jenis_kelamin' => $jenisKelamin,
         'alamat'        => $alamat,
         'no_hp'         => $noHp,
         'unique_code'   => sha1($nama . md5($nuptk . $nama . $noHp)) . substr(sha1($nuptk . rand(0, 100)), 0, 24),
         'rfid_code'     => $rfid
      ]);
   }

   public function updateGuru($id, $nuptk, $nama, $jenisKelamin, $alamat, $noHp, $rfid = null)
   {
      return $this->save([
         $this->primaryKey => $id,
         'nuptk'         => $nuptk,
         'nama_guru'     => $nama,
         'jenis_kelamin' => $jenisKelamin,
         'alamat'        => $alamat,
         'no_hp'         => $noHp,
         'rfid_code'     => $rfid,
      ]);
   }

   // generate CSV object
   public function generateCSVObject($filePath)
   {
      // ... (Kode CSV Anda tetap sama) ...
   }

   // import csv item
   public function importCSVItem($txtFileName, $index)
   {
      // ... (Kode CSV Anda tetap sama) ...
   }
}