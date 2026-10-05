<?php

namespace App\Models;

use CodeIgniter\Model;

class MapelModel extends Model
{
    protected $table            = 'tb_mapel';
    protected $primaryKey       = 'id_mapel';
    protected $allowedFields    = ['id_guru', 'nama_mapel'];

    // Fungsi untuk mengambil data mapel sekaligus join dengan tabel guru
    public function getMapelWithGuru()
    {
        return $this->db->table('tb_mapel')
            // Ubah bagian select ini menjadi mengambil semua data (*) 
            // agar otomatis menyesuaikan dengan kolom yang ada di database Anda
            ->select('tb_mapel.*, tb_guru.*') 
            ->join('tb_guru', 'tb_guru.id_guru = tb_mapel.id_guru', 'left')
            ->get()
            ->getResultArray();
    }
}