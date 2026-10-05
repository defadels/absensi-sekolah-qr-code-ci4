<?php

namespace App\Models;

use CodeIgniter\Model;

class JabatanModel extends Model
{
    protected $table            = 'tb_jabatan';
    protected $primaryKey       = 'id_jabatan';
    protected $allowedFields    = ['id_tendik', 'nama_jabatan'];

    public function getJabatanWithTendik()
    {
        return $this->db->table('tb_jabatan')
            ->select('tb_jabatan.*, tb_tendik.*') 
            ->join('tb_tendik', 'tb_tendik.id_tendik = tb_jabatan.id_tendik', 'left')
            ->get()
            ->getResultArray();
    }
}