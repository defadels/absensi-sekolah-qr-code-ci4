<?php

namespace App\Models;

use CodeIgniter\Model;

class TendikModel extends Model
{
   protected $allowedFields = [
      'nip',
      'nuptk',
      'nama_tendik',
      'jenis_kelamin',
      'alamat',
      'no_hp',
      'unique_code',
      'rfid_code'
   ];

   protected $table = 'tb_tendik';
   protected $primaryKey = 'id_tendik';

   public function cekTendik(string $unique_code)
   {
      return $this->where(['unique_code' => $unique_code])
         ->orWhere(['rfid_code' => $unique_code])
         ->first();
   }

   public function getAllTendik()
   {
      $db = \Config\Database::connect();
      $tableJabatan = $db->tableExists('tb_jabatan') ? 'tb_jabatan' : ($db->tableExists('tb_jabatan_tendik') ? 'tb_jabatan_tendik' : 'tb_jabatan');

      return $db->table('tb_tendik')
         ->select('tb_tendik.*, ' . $tableJabatan . '.nama_jabatan, ' . $tableJabatan . '.nama_jabatan AS jabatan, ' . $tableJabatan . '.nama_jabatan AS jabatan_tendik')
         ->join($tableJabatan, $tableJabatan . '.id_tendik = tb_tendik.id_tendik', 'left')
         ->orderBy('tb_tendik.id_tendik', 'DESC')
         ->get()
         ->getResultArray();
   }

   public function getTendikById($id)
   {
      $db = \Config\Database::connect();
      $tableJabatan = $db->tableExists('tb_jabatan') ? 'tb_jabatan' : ($db->tableExists('tb_jabatan_tendik') ? 'tb_jabatan_tendik' : 'tb_jabatan');

      return $db->table('tb_tendik')
         ->select('tb_tendik.*, ' . $tableJabatan . '.nama_jabatan, ' . $tableJabatan . '.nama_jabatan AS jabatan, ' . $tableJabatan . '.nama_jabatan AS jabatan_tendik')
         ->join($tableJabatan, $tableJabatan . '.id_tendik = tb_tendik.id_tendik', 'left')
         ->where('tb_tendik.id_tendik', $id)
         ->get()
         ->getRowArray();
   }

   public function createTendik($nip, $nama, $jenisKelamin = 'Laki-laki', $noHp = '', $alamat = '', $rfid = null)
   {
      return $this->insert([
         'nip'           => $nip,
         'nama_tendik'   => $nama,
         'jenis_kelamin' => $jenisKelamin,
         'alamat'        => $alamat,
         'no_hp'         => $noHp,
         'unique_code'   => sha1($nama . md5($nip . $nama . $noHp)) . substr(sha1($nip . rand(0, 100)), 0, 24),
         'rfid_code'     => $rfid
      ]);
   }

   public function updateTendik($id, $nip, $nama, $jenisKelamin = 'Laki-laki', $noHp = '', $alamat = '', $rfid = null)
   {
      return $this->save([
         $this->primaryKey => $id,
         'nip'           => $nip,
         'nama_tendik'   => $nama,
         'jenis_kelamin' => $jenisKelamin,
         'alamat'        => $alamat,
         'no_hp'         => $noHp,
         'rfid_code'     => $rfid,
      ]);
   }
}