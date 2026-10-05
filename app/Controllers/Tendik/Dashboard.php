<?php

namespace App\Controllers\Tendik;

use App\Controllers\BaseController;
use App\Models\TendikModel;
use App\Models\PerizinanModel;

class Dashboard extends BaseController
{
    protected $tendikModel;
    protected $perizinanModel;

    public function __construct()
    {
        $this->tendikModel    = new TendikModel();
        $this->perizinanModel = new PerizinanModel();
        helper(['form', 'url', 'session']);
    }

    /**
     * Mengambil data user yang login dan mencocokkan ke tb_tendik
     */
    protected function getTendikData()
    {
        $user     = auth()->user();
        $idTendik = $user->id_tendik ?? null;
        $tendik   = null;

        // 1. Cari berdasarkan id_tendik yang terhubung di tabel users
        if ($idTendik) {
            $tendik = $this->tendikModel->find($idTendik);
        }

        // 2. Fallback jika id_tendik belum terikat langsung di tabel users
        if (!$tendik) {
            $db = \Config\Database::connect();
            if ($db->tableExists('tb_tendik')) {
                $sessionUser = session()->get('username') 
                            ?? session()->get('nama_lengkap') 
                            ?? session()->get('nip') 
                            ?? ($user ? $user->username : '');

                $cleanName = trim(str_replace(['Tendik_', 'Guru_', 'tendik_', 'guru_'], '', $sessionUser));

                $builder = $db->table('tb_tendik');
                if ($db->fieldExists('nip', 'tb_tendik')) {
                    $builder->where('nip', $sessionUser)->orWhere('nip', $cleanName);
                }
                if ($db->fieldExists('nama_tendik', 'tb_tendik') && !empty($cleanName)) {
                    $builder->orLike('nama_tendik', $cleanName);
                }
                $tendik = $builder->get()->getRowArray();
            }
        }

        if (!$tendik) {
            $tendik = [
                'nama_tendik' => $user->username ?? 'Pegawai Tendik',
                'nip'         => '-'
            ];
        }

        return [$user, $tendik];
    }

    public function index()
    {
        [$user, $tendik] = $this->getTendikData();
        $db = \Config\Database::connect();

        $jabatanFound = '';

        // Prioritas 1: Ambil langsung dari kolom string di tb_tendik (jabatan / nama_jabatan)
        foreach (['nama_jabatan', 'jabatan', 'posisi', 'tugas'] as $col) {
            if (!empty($tendik[$col]) && !is_numeric($tendik[$col])) {
                $jabatanFound = $tendik[$col];
                break;
            }
        }

        // Prioritas 2: Jika bernilai ID/angka, cari relasinya di tabel master jabatan
        if (empty($jabatanFound)) {
            $foreignJabatanId = $tendik['id_jabatan'] ?? $tendik['jabatan_id'] ?? null;
            if ($foreignJabatanId) {
                foreach (['tb_jabatan', 'tb_jabatan_tendik', 'jabatan'] as $tj) {
                    if ($db->tableExists($tj)) {
                        $jRow = $db->table($tj)
                                   ->groupStart()
                                       ->where('id', $foreignJabatanId)
                                       ->orWhere('id_jabatan', $foreignJabatanId)
                                   ->groupEnd()
                                   ->get()->getRowArray();
                        if ($jRow) {
                            $jabatanFound = $jRow['nama_jabatan'] ?? $jRow['jabatan'] ?? $jRow['nama'] ?? '';
                            if (!empty($jabatanFound)) break;
                        }
                    }
                }
            }
        }

        $data = [
            'title'   => 'Dashboard Tenaga Kependidikan',
            'user'    => $user,
            'tendik'  => $tendik,
            'jabatan' => !empty($jabatanFound) ? $jabatanFound : 'Operator'
        ];

        return view('tendik/dashboard', $data);
    }

    public function attendance()
    {
        [$user, $tendik] = $this->getTendikData();
        $db = \Config\Database::connect();

        $idTendik = $tendik['id_tendik'] ?? $tendik['id'] ?? null;

        // 1. Tarik Riwayat Presensi
        $riwayat = [];
        if ($idTendik && $db->tableExists('tb_presensi_tendik')) {
            $riwayat = $db->table('tb_presensi_tendik')
                          ->where('id_tendik', $idTendik)
                          ->orderBy('tanggal', 'DESC')
                          ->get()->getResultArray();
        }

        // 2. Tarik Riwayat Perizinan
        $riwayatPerizinan = [];
        if ($db->tableExists('tb_perizinan')) {
            $pk = $db->fieldExists('id_perizinan', 'tb_perizinan') ? 'id_perizinan' : 'id';
            $builderIzin = $db->table('tb_perizinan');
            
            if ($idTendik && $db->fieldExists('id_tendik', 'tb_perizinan')) {
                $builderIzin->where('id_tendik', $idTendik);
            }
            
            $riwayatPerizinan = $builderIzin->orderBy($pk, 'DESC')->get()->getResultArray();
        }

        $data = [
            'title'            => 'Riwayat Kehadiran & Perizinan Tendik',
            'user'             => $user,
            'tendik'           => $tendik,
            'riwayat'          => $riwayat,
            'riwayatPresensi'  => $riwayat,
            'riwayatPerizinan' => $riwayatPerizinan,
            'perizinan'        => $riwayatPerizinan,
            'data_perizinan'   => $riwayatPerizinan
        ];

        return view('tendik/attendance', $data);
    }
}