<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

use App\Models\GuruModel;
use App\Models\TendikModel;
use App\Models\JabatanModel; // <-- Tambah Model Jabatan
use App\Models\MapelModel;   // <-- Tambah Model Mapel
use App\Models\PetugasModel;
use App\Models\PresensiGuruModel;
use App\Models\PresensiTendikModel;
use CodeIgniter\I18n\Time;

class Dashboard extends BaseController
{
    protected TendikModel $tendikModel;
    protected GuruModel $guruModel;

    protected JabatanModel $jabatanModel; // <-- Ubah Kelas ke Jabatan
    protected MapelModel $mapelModel;     // <-- Ubah Jurusan ke Mapel

    protected PresensiTendikModel $presensiTendikModel;
    protected PresensiGuruModel $presensiGuruModel;

    protected PetugasModel $petugasModel;

    public function __construct()
    {
        $this->tendikModel         = new TendikModel();
        $this->guruModel           = new GuruModel();
        $this->jabatanModel        = new JabatanModel(); // <-- Inisialisasi Jabatan
        $this->mapelModel          = new MapelModel();   // <-- Inisialisasi Mapel
        $this->presensiTendikModel = new PresensiTendikModel();
        $this->presensiGuruModel   = new PresensiGuruModel();
        $this->petugasModel        = new PetugasModel();
    }

    public function index()
    {
        $now = Time::now();

        $dateRange = [];
        $chartLabelColors = [];
        $holidayModel = new \App\Models\HariLiburModel();
        for ($i = 6; $i >= 0; $i--) {
            $date = $now->subDays($i)->toDateString();
            $isHoliday = $holidayModel->isHoliday($date);
            if ($i == 0) {
                $formattedDate = "Hari ini";
            } else {
                $t = $now->subDays($i);
                $formattedDate = "{$t->getDay()} " . substr($t->toFormattedDateString(), 0, 3);
            }
            array_push($dateRange, $formattedDate);
            array_push($chartLabelColors, $isHoliday ? '#f44336' : '#333');
        }

        $today = $now->toDateString();
        
        $jamPulangStandard = $this->generalSettings->jam_pulang_standard ?? '14:00:00';
        $isAfterSchool = $now->toTimeString() > $jamPulangStandard;

        // Mendapatkan tren kehadiran Guru dan Tendik
        $grafikKehadiranTendik = method_exists($this->presensiTendikModel, 'getAttendanceTrend') 
            ? $this->presensiTendikModel->getAttendanceTrend() 
            : [];
        $grafikKehadiranGuru   = method_exists($this->presensiGuruModel, 'getAttendanceTrend') 
            ? $this->presensiGuruModel->getAttendanceTrend() 
            : [];

        $data = [
            'title' => 'Dashboard',
            'ctx'   => 'admin-dashboard',

            'tendik' => $this->tendikModel->findAll(),
            'guru'   => $this->guruModel->findAll(),

            // Kirim data Jabatan dan Mapel ke View
            'jabatan' => $this->jabatanModel->findAll(),
            'mapel'   => $this->mapelModel->findAll(),

            'dateRange'        => $dateRange,
            'chartLabelColors' => $chartLabelColors,
            'dateNow'          => $now->toLocalizedString('d MMMM Y'),

            'grafikKehadiranTendik' => $grafikKehadiranTendik,
            'grafikKehadiranGuru'   => $grafikKehadiranGuru,

            'jumlahKehadiranTendik' => [
                'hadir' => method_exists($this->presensiTendikModel, 'getPresensiByKehadiran') ? count($this->presensiTendikModel->getPresensiByKehadiran('1', $today)) : 0,
                'sakit' => method_exists($this->presensiTendikModel, 'getPresensiByKehadiran') ? count($this->presensiTendikModel->getPresensiByKehadiran('2', $today)) : 0,
                'izin'  => method_exists($this->presensiTendikModel, 'getPresensiByKehadiran') ? count($this->presensiTendikModel->getPresensiByKehadiran('3', $today)) : 0,
                'alfa'  => method_exists($this->presensiTendikModel, 'getPresensiByKehadiran') ? count($this->presensiTendikModel->getPresensiByKehadiran('4', $today)) : 0,
            ],

            'jumlahKehadiranGuru' => [
                'hadir' => method_exists($this->presensiGuruModel, 'getPresensiByKehadiran') ? count($this->presensiGuruModel->getPresensiByKehadiran('1', $today)) : 0,
                'sakit' => method_exists($this->presensiGuruModel, 'getPresensiByKehadiran') ? count($this->presensiGuruModel->getPresensiByKehadiran('2', $today)) : 0,
                'izin'  => method_exists($this->presensiGuruModel, 'getPresensiByKehadiran') ? count($this->presensiGuruModel->getPresensiByKehadiran('3', $today)) : 0,
                'alfa'  => method_exists($this->presensiGuruModel, 'getPresensiByKehadiran') ? count($this->presensiGuruModel->getPresensiByKehadiran('4', $today)) : 0,
            ],

            'totalTendik' => $this->tendikModel->countAllResults(),
            'totalGuru'   => $this->guruModel->countAllResults(),

            'petugas' => $this->petugasModel->findAll(),
        ];

        return view('admin/dashboard', $data);
    }

    public function auditLog()
    {
        $auditLogModel = new \App\Models\AuditLogModel();
        $data = [
            'title' => 'Audit Log - Riwayat Perubahan',
            'ctx'   => 'audit-log',
            'logs'  => method_exists($auditLogModel, 'getLogs') ? $auditLogModel->getLogs() : $auditLogModel->findAll()
        ];
        return view('admin/audit_log', $data);
    }

    public function getLiveStats()
    {
        $now   = Time::now();
        $today = $now->toDateString();

        $jumlahKehadiranTendik = [
            'hadir' => method_exists($this->presensiTendikModel, 'getPresensiByKehadiran') ? count($this->presensiTendikModel->getPresensiByKehadiran('1', $today)) : 0,
            'sakit' => method_exists($this->presensiTendikModel, 'getPresensiByKehadiran') ? count($this->presensiTendikModel->getPresensiByKehadiran('2', $today)) : 0,
            'izin'  => method_exists($this->presensiTendikModel, 'getPresensiByKehadiran') ? count($this->presensiTendikModel->getPresensiByKehadiran('3', $today)) : 0,
            'alfa'  => method_exists($this->presensiTendikModel, 'getPresensiByKehadiran') ? count($this->presensiTendikModel->getPresensiByKehadiran('4', $today)) : 0,
        ];

        $totalTendik = $this->tendikModel->countAllResults();
        
        $jamPulangStandard = $this->generalSettings->jam_pulang_standard ?? '14:00:00';
        $isAfterSchool     = $now->toTimeString() > $jamPulangStandard;

        return $this->response->setJSON([
            'stats'         => $jumlahKehadiranTendik,
            'totalTendik'   => $totalTendik,
            'isAfterSchool' => $isAfterSchool,
            'lastUpdate'    => $now->toTimeString()
        ]);
    }

    public function filterData()
    {
        $now   = Time::now();
        $today = $now->toDateString();

        // Statistik Tendik
        $jumlahKehadiranTendik = [
            'hadir' => method_exists($this->presensiTendikModel, 'getPresensiByKehadiran') ? count($this->presensiTendikModel->getPresensiByKehadiran('1', $today)) : 0,
            'sakit' => method_exists($this->presensiTendikModel, 'getPresensiByKehadiran') ? count($this->presensiTendikModel->getPresensiByKehadiran('2', $today)) : 0,
            'izin'  => method_exists($this->presensiTendikModel, 'getPresensiByKehadiran') ? count($this->presensiTendikModel->getPresensiByKehadiran('3', $today)) : 0,
            'alfa'  => method_exists($this->presensiTendikModel, 'getPresensiByKehadiran') ? count($this->presensiTendikModel->getPresensiByKehadiran('4', $today)) : 0,
        ];

        // Grafik Tendik (7 Hari)
        $grafikKehadiranTendik = method_exists($this->presensiTendikModel, 'getAttendanceTrend') 
            ? $this->presensiTendikModel->getAttendanceTrend(7) 
            : [];

        $totalTendik = $this->tendikModel->countAllResults();

        $data = [
            'hadir'       => $jumlahKehadiranTendik['hadir'],
            'sakit'       => $jumlahKehadiranTendik['sakit'],
            'izin'        => $jumlahKehadiranTendik['izin'],
            'alfa'        => $jumlahKehadiranTendik['alfa'],
            'totalTendik' => $totalTendik,
        ];

        return $this->response->setJSON([
            'result'      => 1,
            'htmlContent' => view('admin/_dashboard_tendik_stats', $data),
            'chartData'   => $grafikKehadiranTendik,
            'totalTendik' => $totalTendik
        ]);
    }
}