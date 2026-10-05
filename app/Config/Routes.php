<?php

namespace Config;

use CodeIgniter\Router\RouteCollection;

$routes = Services::routes();

$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();

// ── RUTE AKTIVASI / KLAIM AKUN GURU & TENDIK (PUBLIK) ──
$routes->group('daftar', ['namespace' => 'App\Controllers'], function (RouteCollection $routes) {
    $routes->get('/', 'Aktivasi::index');
    $routes->get('cek', 'Aktivasi::index');
    $routes->post('verifikasi', 'Aktivasi::cekData');
    $routes->post('cek', 'Aktivasi::cekData');
    $routes->get('buat-akun', 'Aktivasi::buatAkun');
    $routes->post('simpan', 'Aktivasi::simpanAkun');
    $routes->post('simpan-akun', 'Aktivasi::simpanAkun');
});

service('auth')->routes($routes);

// ── Home route (Role-based redirect setelah login) ──
$routes->get('/', function () {
    helper('user');
    
    if (!auth()->loggedIn()) {
        return redirect()->to(base_url('login'));
    }

    $user = auth()->user();

    if ($user && $user->inGroup('superadmin', 'admin')) {
        return redirect()->to(base_url('admin/dashboard'));
    }

    if ($user && $user->inGroup('guru')) {
        return redirect()->to(base_url('teacher/dashboard'));
    }

    if ($user && $user->inGroup('tendik')) {
        return redirect()->to(base_url('tendik/dashboard'));
    }

    $role = user_role();
    if ($role === 'scanner') {
        return redirect()->to(base_url('scan'));
    }

    return redirect()->to(base_url('login'));
});

// ── Scan ──
$routes->group('scan', function (RouteCollection $routes) {
    $routes->get('', 'Scan::index');
    $routes->get('masuk', 'Scan::index/Masuk');
    $routes->get('pulang', 'Scan::index/Pulang');
    $routes->post('cek', 'Scan::cekKode');
});

// ── Perizinan Umum & Fallback ──
$routes->get('perizinan', 'Tendik\Perizinan::index');
$routes->post('perizinan/submit', 'Tendik\Perizinan::submit');

$routes->group('izin', function (RouteCollection $routes) {
    $routes->get('', 'Tendik\Perizinan::index');
    $routes->post('submit', 'Tendik\Perizinan::submit');
});

// Portal Cek Kehadiran Mandiri
$routes->group('cek-kehadiran', function (RouteCollection $routes) {
    $routes->get('', 'CekKehadiran::index');
    $routes->post('view', 'CekKehadiran::view');
});

// ═══════════════════════════════════════════
// ADMIN AREA (Superadmin / Admin)
// ═══════════════════════════════════════════
$routes->group('admin', ['filter' => 'group:superadmin,admin'], function (RouteCollection $routes) {
    $routes->get('', 'Admin\Dashboard::index');
    $routes->get('dashboard', 'Admin\Dashboard::index');
    $routes->get('dashboard/live-stats', 'Admin\Dashboard::getLiveStats');
    $routes->post('dashboard/filter-data', 'Admin\Dashboard::filterData');
    $routes->get('admin/perizinan/konfirmasi/(:num)', 'Admin\Perizinan::konfirmasi/$1');
    $routes->get('admin/perizinan/hapus/(:num)', 'Admin\Perizinan::hapus/$1');

    $routes->group('perizinan', ['namespace' => 'App\Controllers\Admin'], function ($routes) {
        $routes->get('/', 'Perizinan::index');
        $routes->post('list', 'Perizinan::list');
        $routes->get('konfirmasi/(:num)', 'Perizinan::konfirmasiGet/$1'); 
        $routes->post('konfirmasi', 'Perizinan::konfirmasi');
        $routes->get('delete/(:any)', 'Perizinan::delete/$1');         
        $routes->delete('delete/(:any)', 'Perizinan::delete/$1');
    });

    $routes->get('mata-pelajaran', 'Admin\MataPelajaran::index');
    $routes->post('mata-pelajaran/simpan', 'Admin\MataPelajaran::simpan');
    $routes->get('mata-pelajaran/hapus/(:num)', 'Admin\MataPelajaran::hapus/$1');

    $routes->group('holiday', ['namespace' => 'App\Controllers\Admin'], function ($routes) {
        $routes->get('/', 'Holiday::index');
        $routes->get('generate-weekend', 'Holiday::generateWeekend');
        $routes->post('save', 'Holiday::save');
        $routes->post('bulk-delete', 'Holiday::bulkDelete');
        $routes->delete('delete/(:any)', 'Holiday::delete/$1');
    });

    $routes->get('audit-log', 'Admin\Dashboard::auditLog');

    $routes->group('absen-tendik', function ($routes) {
        $routes->get('/', 'Admin\DataAbsenTendik::index');
        $routes->post('/', 'Admin\DataAbsenTendik::ambilDataTendik');
        $routes->post('kehadiran', 'Admin\DataAbsenTendik::ambilKehadiran');
        $routes->post('edit', 'Admin\DataAbsenTendik::ubahKehadiran');
    });

    $routes->group('absen-guru', function ($routes) {
        $routes->get('/', 'Admin\DataAbsenGuru::index');
        $routes->post('/', 'Admin\DataAbsenGuru::ambilDataGuru');
        $routes->post('kehadiran', 'Admin\DataAbsenGuru::ambilKehadiran');
        $routes->post('edit', 'Admin\DataAbsenGuru::ubahKehadiran');
    });

    $routes->group('tendik', function ($routes) {
        $routes->get('/', 'Admin\DataTendik::index');
        $routes->post('/', 'Admin\DataTendik::ambilDataTendik');
        $routes->post('ambilDataTendik', 'Admin\DataTendik::ambilDataTendik');
        $routes->get('create', 'Admin\DataTendik::formTambahTendik');
        $routes->post('create', 'Admin\DataTendik::saveTendik');
        $routes->get('edit/(:any)', 'Admin\DataTendik::formEditTendik/$1');
        $routes->post('edit', 'Admin\DataTendik::updateTendik');
        $routes->delete('delete/(:any)', 'Admin\DataTendik::delete/$1');
        $routes->get('bulk', 'Admin\DataTendik::bulkPostTendik');
        $routes->post('downloadCSVFilePost', 'Admin\DataTendik::downloadCSVFilePost');
        $routes->post('generateCSVObjectPost', 'Admin\DataTendik::generateCSVObjectPost');
        $routes->post('importCSVItemPost', 'Admin\DataTendik::importCSVItemPost');
        $routes->post('deleteSelectedTendik', 'Admin\DataTendik::deleteSelectedTendik');
        $routes->post('deleteAll', 'Admin\DataTendik::deleteAllTendik');
    });

    $routes->get('jabatan', 'Admin\Jabatan::index');
    $routes->post('jabatan/simpan', 'Admin\Jabatan::simpan');
    $routes->get('jabatan/hapus/(:num)', 'Admin\Jabatan::hapus/$1');

    $routes->group('guru', function ($routes) {
        $routes->get('/', 'Admin\DataGuru::index');
        $routes->post('/', 'Admin\DataGuru::ambilDataGuru');
        $routes->get('create', 'Admin\DataGuru::formTambahGuru');
        $routes->post('create', 'Admin\DataGuru::saveGuru');
        $routes->get('edit/(:any)', 'Admin\DataGuru::formEditGuru/$1');
        $routes->post('edit', 'Admin\DataGuru::updateGuru');
        $routes->delete('delete/(:any)', 'Admin\DataGuru::delete/$1');
        $routes->get('bulk', 'Admin\DataGuru::bulkPost');
        $routes->post('downloadCSVFilePost', 'Admin\DataGuru::downloadCSVFilePost');
        $routes->post('generateCSVObjectPost', 'Admin\DataGuru::generateCSVObjectPost');
        $routes->post('importCSVItemPost', 'Admin\DataGuru::importCSVItemPost');
        $routes->post('deleteAll', 'Admin\DataGuru::deleteAllGuru');
    });

    $routes->group('generate', function ($routes) {
        $routes->get('/', 'Admin\GenerateQR::index');
        $routes->post('tendik', 'Admin\QRGenerator::generateQrTendik');
        $routes->post('guru', 'Admin\QRGenerator::generateQrGuru');
    });

    $routes->group('qr', function ($routes) {
        $routes->get('tendik/download', 'Admin\QRGenerator::downloadAllQrTendik');
        $routes->get('tendik/(:any)/download', 'Admin\QRGenerator::downloadQrTendik/$1');
        $routes->get('tendik/(:any)/view', 'Admin\QRGenerator::viewQrTendik/$1');
        $routes->get('tendik/print', 'Admin\QRGenerator::printQrTendik');
        $routes->get('tendik/print/(:any)', 'Admin\QRGenerator::printQrTendik/$1');
        $routes->get('tendik/print-single/(:any)', 'Admin\QRGenerator::printQrTendikSingle/$1');
        $routes->get('guru/download', 'Admin\QRGenerator::downloadAllQrGuru');
        $routes->get('guru/(:any)/download', 'Admin\QRGenerator::downloadQrGuru/$1');
        $routes->get('guru/(:any)/view', 'Admin\QRGenerator::viewQrGuru/$1');
        $routes->get('guru/print', 'Admin\QRGenerator::printQrGuru');
        $routes->get('guru/print-single/(:any)', 'Admin\QRGenerator::printQrGuruSingle/$1');
    });

    $routes->group('laporan', function ($routes) {
        $routes->get('/', 'Admin\GenerateLaporan::index');
        $routes->post('tendik', 'Admin\GenerateLaporan::generateLaporanTendik');
        $routes->post('guru', 'Admin\GenerateLaporan::generateLaporanGuru');
    });

    $routes->group('petugas', function ($routes) {
        $routes->get('/', 'Admin\DataPetugas::index');
        $routes->post('/', 'Admin\DataPetugas::ambilDataPetugas');
        $routes->get('register', 'Admin\DataPetugas::registerPetugas');
        $routes->post('register', 'Admin\DataPetugas::registerPetugasPost');
        $routes->get('edit/(:any)', 'Admin\DataPetugas::formEditPetugas/$1');
        $routes->post('edit', 'Admin\DataPetugas::updatePetugas');
        $routes->delete('delete/(:any)', 'Admin\DataPetugas::delete/$1');
        $routes->get('activate/(:any)', 'Admin\DataPetugas::toggleActivation/$1');
        $routes->get('bulk', 'Admin\DataPetugas::bulkPost');
        $routes->post('downloadCSVFilePost', 'Admin\DataPetugas::downloadCSVFilePost');
        $routes->post('generateCSVObjectPost', 'Admin\DataPetugas::generateCSVObjectPost');
        $routes->post('importCSVItemPost', 'Admin\DataPetugas::importCSVItemPost');
    });

    $routes->group('general-settings', function ($routes) {
        $routes->get('/', 'Admin\GeneralSettings::index');
        $routes->post('update', 'Admin\GeneralSettings::generalSettingsPost');
    });

    $routes->group('backup', function ($routes) {
        $routes->get('', 'Admin\Backup::index');
        $routes->get('db/backup', 'Admin\Backup::dbBackup');
        $routes->post('db/restore', 'Admin\Backup::dbRestore');
        $routes->get('photos/backup', 'Admin\Backup::photosBackup');
        $routes->post('photos/restore', 'Admin\Backup::photosRestore');
    });
});

// ═══════════════════════════════════════════
// TEACHER AREA (Guru)
// ═══════════════════════════════════════════
$routes->group('teacher', ['filter' => 'group:guru,superadmin'], function (RouteCollection $routes) {
    $routes->get('/', 'Teacher\Dashboard::index');
    $routes->get('dashboard', 'Teacher\Dashboard::index');
    $routes->get('dashboard/live-stats', 'Teacher\Dashboard::getLiveStats');
    $routes->get('laporan', 'Teacher\Reports::index');
    $routes->post('laporan/generate', 'Teacher\Reports::generate');
    $routes->get('qr', 'Teacher\QRCode::index');
    $routes->get('qr/download', 'Teacher\QRCode::download');
    $routes->get('qr/print', 'Teacher\QRCode::print');
    $routes->get('attendance', 'Teacher\Dashboard::attendance');
    $routes->get('attendance/(:any)', 'Teacher\Dashboard::attendance/$1');
    $routes->post('attendance/get-list', 'Teacher\Dashboard::getAttendanceList');
    $routes->post('attendance/get-edit-modal', 'Teacher\Dashboard::getEditModal');
    $routes->post('attendance/update-single', 'Teacher\Dashboard::updateSingleAttendance');

    $routes->get('perizinan', 'Teacher\Perizinan::index');
    $routes->post('perizinan/submit', 'Teacher\Perizinan::submit'); 
    $routes->post('perizinan/konfirmasi', 'Teacher\Perizinan::konfirmasi');
});

// ═══════════════════════════════════════════
// TENDIK AREA (Tendik)
// ═══════════════════════════════════════════
$routes->group('tendik', ['filter' => 'group:tendik,superadmin'], function (RouteCollection $routes) {
    $routes->get('/', 'Tendik\Dashboard::index');
    $routes->get('dashboard', 'Tendik\Dashboard::index');
    $routes->get('perizinan', 'Tendik\Perizinan::index');
    $routes->post('perizinan/submit', 'Tendik\Perizinan::submit'); 
    $routes->get('attendance', 'Tendik\Dashboard::attendance');
    $routes->get('qr', 'Tendik\QRCode::index');
    $routes->get('qr/download', 'Tendik\QRCode::download');
    $routes->get('qr/print', 'Tendik\QRCode::print');
});

if (is_file(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}