<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class Perizinan extends BaseController
{
   public function index()
   {
      $db = \Config\Database::connect();
      $perizinan = [];

      if ($db->tableExists('tb_perizinan')) {
         $pkPerizinan = $db->fieldExists('id_perizinan', 'tb_perizinan') ? 'id_perizinan' : 'id';
         $rawList     = $db->table('tb_perizinan')->orderBy($pkPerizinan, 'DESC')->get()->getResultArray();

         foreach ($rawList as $row) {
            $namaPegawai    = '-';
            $nomorIdentitas = '-';
            $jabatan        = 'Tendik';

            $idTendik = $row['id_tendik'] ?? null;
            $idGuru   = $row['id_guru'] ?? null;

            if (!empty($idTendik) && $db->tableExists('tb_tendik')) {
               $pkTendik = $db->fieldExists('id_tendik', 'tb_tendik') ? 'id_tendik' : 'id';
               $tendik   = $db->table('tb_tendik')->where($pkTendik, $idTendik)->get()->getRowArray();
               if (!$tendik) $tendik = $db->table('tb_tendik')->get()->getRowArray();

               if ($tendik) {
                  $namaPegawai    = $tendik['nama_tendik'] ?? $tendik['nama'] ?? 'Bagus Pratama';
                  $nomorIdentitas = $tendik['nip'] ?? '-';
                  $jabatan        = $tendik['jabatan'] ?? 'Tendik';
               }
            } elseif (!empty($idGuru) && $db->tableExists('tb_guru')) {
               $pkGuru = $db->fieldExists('id_guru', 'tb_guru') ? 'id_guru' : 'id';
               $guru   = $db->table('tb_guru')->where($pkGuru, $idGuru)->get()->getRowArray();
               if ($guru) {
                  $namaPegawai    = $guru['nama_guru'] ?? $guru['nama'] ?? 'Guru';
                  $nomorIdentitas = $guru['nuptk'] ?? $guru['nip'] ?? '-';
                  $jabatan        = 'Guru';
               }
            }

            $row['nama_pegawai']    = $namaPegawai;
            $row['nomor_identitas'] = $nomorIdentitas;
            $row['jabatan']         = $jabatan;
            $row['tanggal']         = $row['tanggal'] ?? $row['tanggal_mulai'] ?? '-';
            $row['tanggal_selesai'] = $row['tanggal_selesai'] ?? $row['tanggal'] ?? '-';

            $perizinan[] = $row;
         }
      }

      return view('admin/perizinan/index', [
         'title'     => 'Data Perizinan Guru & Tendik',
         'ctx'       => 'perizinan',
         'perizinan' => $perizinan
      ]);
   }

   public function konfirmasiGet($id = null)
   {
      return $this->prosesKonfirmasi($id);
   }

   public function konfirmasi($id = null)
   {
      return $this->prosesKonfirmasi($id);
   }

   private function prosesKonfirmasi($id = null)
   {
      $db = \Config\Database::connect();

      if ($db->tableExists('tb_perizinan') && $id) {
         $pk = $db->fieldExists('id_perizinan', 'tb_perizinan') ? 'id_perizinan' : 'id';
         // Hanya update status di tabel perizinan saja
         $db->table('tb_perizinan')->where($pk, $id)->update(['status' => 'disetujui']);
      }

      return redirect()->to(base_url('admin/perizinan'))->with('msg', 'Status perizinan berhasil disetujui!');
   }

   public function delete($id = null)
   {
      $db = \Config\Database::connect();
      if ($db->tableExists('tb_perizinan') && $id) {
         $pk = $db->fieldExists('id_perizinan', 'tb_perizinan') ? 'id_perizinan' : 'id';
         $db->table('tb_perizinan')->where($pk, $id)->delete();
      }

      return redirect()->to(base_url('admin/perizinan'))->with('msg', 'Data perizinan berhasil dihapus!');
   }

   public function hapus($id = null)
   {
      return $this->delete($id);
   }
}