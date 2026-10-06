<?php
$user    = auth()->user();
$context = $ctx ?? 'dashboard';

// ── Sidebar color based on context ──
$sidebarColor = match ($context) {
    'absen-tendik', 'tendik', 'jabatan' => 'purple',
    'absen-guru', 'guru', 'mata-pelajaran' => 'green',
    'qr', 'backup'                       => 'danger',
    default                              => 'azure',
};

// ── Role-based logo label ──
$roleLabel = 'Operator Petugas Absensi';
if ($user) {
    if ($user->inGroup('superadmin')) {
        $roleLabel = 'Super Administrator';
    } elseif ($user->inGroup('kepsek')) {
        $roleLabel = 'Kepala Sekolah';
    } elseif ($user->inGroup('scanner') && !$user->can('admin.access')) {
        $roleLabel = 'Petugas Scanner';
    } elseif ($user->inGroup('guru') && !$user->inGroup('admin') && !$user->inGroup('superadmin')) {
        $roleLabel = 'Guru / Wali Kelas';
    }
}

// ── Build Menu Sections ──
$sections = [];

if ($user && is_guru()) {
    $sections['Wali Kelas'] = [
        ['title' => 'Dashboard Wali Kelas', 'url' => 'teacher/dashboard',  'icon' => 'dashboard',  'context' => 'teacher-dashboard'],
        ['title' => 'Pengajuan Izin',       'url' => 'teacher/perizinan',  'icon' => 'mail',       'context' => 'teacher-perizinan'],
        ['title' => 'Laporan Kelas',        'url' => 'teacher/laporan',    'icon' => 'print',      'context' => 'teacher-laporan'],
        ['title' => 'QR Code Siswa',        'url' => 'teacher/qr',         'icon' => 'qr_code',    'context' => 'teacher-qr'],
        ['title' => 'Manajemen Kehadiran',  'url' => 'teacher/attendance', 'icon' => 'event_note', 'context' => 'teacher-attendance'],
    ];
}

$adminMenus = [
    ['title' => 'Dashboard',            'url' => 'admin/dashboard',        'icon' => 'dashboard',  'context' => 'admin-dashboard',   'perm' => 'admin.access'],
    ['title' => 'Absensi Tendik',       'url' => 'admin/absen-tendik',     'icon' => 'checklist',  'context' => 'absen-tendik',      'perm' => 'attendance.edit'],
    ['title' => 'Absensi Guru',         'url' => 'admin/absen-guru',       'icon' => 'checklist',  'context' => 'absen-guru',        'perm' => 'attendance.edit'],
    ['title' => 'Data Perizinan',       'url' => 'admin/perizinan',        'icon' => 'mail',       'context' => 'perizinan',         'perm' => 'attendance.edit'],
    ['title' => 'Hari Libur',           'url' => 'admin/holiday',          'icon' => 'event_busy', 'context' => 'holiday',           'perm' => 'settings.manage'],
    ['title' => 'Data Tendik',          'url' => 'admin/tendik',           'icon' => 'person',     'context' => 'tendik',            'perm' => 'students.manage'],
    ['title' => 'Data Jabatan Tendik',  'url' => 'admin/jabatan',          'icon' => 'work',       'context' => 'jabatan',           'perm' => 'students.manage'],
    ['title' => 'Data Guru',            'url' => 'admin/guru',             'icon' => 'person_4',   'context' => 'guru',              'perm' => 'teachers.manage'],
    ['title' => 'Data Mata Pelajaran',  'url' => 'admin/mata-pelajaran',   'icon' => 'menu_book',  'context' => 'mata-pelajaran',    'perm' => 'teachers.manage'],
    ['title' => 'Generate QR Code',     'url' => 'admin/generate',         'icon' => 'qr_code',    'context' => 'admin-qr',          'perm' => 'qr.generate'],
    ['title' => 'Generate Laporan',     'url' => 'admin/laporan',          'icon' => 'print',      'context' => 'laporan',           'perm' => 'attendance.view'],
    ['title' => 'Data Petugas',         'url' => 'admin/petugas',          'icon' => 'computer',   'context' => 'petugas',           'perm' => 'petugas.manage'],
    ['title' => 'Pengaturan',           'url' => 'admin/general-settings', 'icon' => 'settings',   'context' => 'general_settings',  'perm' => 'settings.manage'],
    ['title' => 'Audit Log',            'url' => 'admin/audit-log',        'icon' => 'history',    'context' => 'audit-log',         'perm' => 'admin.access'],
    ['title' => 'Backup & Restore',     'url' => 'admin/backup',           'icon' => 'backup',     'context' => 'backup',            'perm' => 'backup.manage'],
];

$adminItems = [];
if ($user) {
    foreach ($adminMenus as $menu) {
        if ($user->can($menu['perm'])) {
            $adminItems[] = $menu;
        }
    }
}

if (!empty($adminItems)) {
    if ($user && $user->inGroup('superadmin')) {
        $adminItems[] = [
            'title' => 'Maintenance Deploy',
            'url' => 'admin/maintenance',
            'icon' => 'build',
            'context' => 'maintenance',
        ];
    }

    $sections['Admin'] = $adminItems;
}

$showSectionHeaders = count($sections) > 1;
$schoolName         = $generalSettings->school_name ?? 'Sistem Absensi';
?>

<div class="sidebar" data-color="<?= esc($sidebarColor); ?>" data-image="<?= base_url('assets/img/sidebar/sidebar-3.jpg'); ?>">
    <div class="logo">
        <a class="simple-text logo-normal">
            <b><?= esc($roleLabel); ?></b>
            <br>
            <small><?= esc($schoolName); ?></small>
        </a>
    </div>

    <div class="sidebar-wrapper">
        <ul class="nav">
            <?php foreach ($sections as $sectionTitle => $items): ?>
                <?php if ($showSectionHeaders): ?>
                    <li class="nav-item">
                        <p class="nav-link py-1 text-muted small font-weight-bold"><?= esc($sectionTitle); ?></p>
                    </li>
                <?php endif; ?>

                <?php foreach ($items as $item): ?>
                    <li class="nav-item <?= $context === $item['context'] ? 'active' : ''; ?>">
                        <a class="nav-link font-weight-bold" href="<?= base_url($item['url']); ?>">
                            <i class="material-icons"><?= esc($item['icon']); ?></i>
                            <p><?= esc($item['title']); ?></p>
                        </a>
                    </li>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </ul>
    </div>
</div>
