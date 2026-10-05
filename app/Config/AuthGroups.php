<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter Shield.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Config;

use CodeIgniter\Shield\Config\AuthGroups as ShieldAuthGroups;

class AuthGroups extends ShieldAuthGroups
{
    /**
     * --------------------------------------------------------------------
     * Default Group
     * --------------------------------------------------------------------
     * The group that a newly registered user is added to.
     */
    public string $defaultGroup = 'user';

    /**
     * --------------------------------------------------------------------
     * Groups
     * --------------------------------------------------------------------
     */
    public array $groups = [
        'superadmin' => [
            'title'       => 'Super Admin',
            'description' => 'Akses penuh ke seluruh fitur aplikasi.',
        ],
        'admin' => [
            'title'       => 'Staf Petugas',
            'description' => 'Mengelola absensi, generate QR, dan laporan.',
        ],
        'kepsek' => [
            'title'       => 'Kepala Sekolah',
            'description' => 'Melihat laporan absensi.',
        ],
        'scanner' => [
            'title'       => 'Scanner',
            'description' => 'Hanya dapat melakukan scan QR untuk presensi.',
        ],
        'guru' => [
            'title'       => 'Guru',
            'description' => 'Guru pengajar yang mengakses dashboard guru.',
        ],
        'tendik' => [
            'title'       => 'Tenaga Kependidikan',
            'description' => 'Staff tendik yang mengakses dashboard tendik.',
        ],
    ];

    /**
     * --------------------------------------------------------------------
     * Permissions
     * --------------------------------------------------------------------
     */
    public array $permissions = [
        'dashboard.view-admin'  => 'Dapat melihat dashboard admin',
        'admin.access'          => 'Dapat mengakses area admin',
        'students.manage'       => 'Dapat mengelola data siswa',
        'teachers.manage'       => 'Dapat mengelola data guru',
        'classes.manage'        => 'Dapat mengelola data kelas dan jurusan',
        'attendance.edit'       => 'Dapat mengubah data presensi',
        'attendance.view'       => 'Dapat melihat laporan presensi',
        'qr.generate'           => 'Dapat generate QR Code',
        'petugas.manage'        => 'Dapat mengelola akun petugas',
        'settings.manage'       => 'Dapat mengelola pengaturan aplikasi',
        'backup.manage'         => 'Dapat melakukan backup dan restore',
        'teacher.access'        => 'Dapat mengakses dashboard guru',
        'tendik.access'         => 'Dapat mengakses dashboard tendik',
    ];

    /**
     * --------------------------------------------------------------------
     * Permissions Matrix
     * --------------------------------------------------------------------
     */
    public array $matrix = [
        'superadmin' => [
            'dashboard.view-admin',
            'admin.*',
            'students.*',
            'teachers.*',
            'classes.*',
            'attendance.*',
            'qr.*',
            'petugas.*',
            'settings.*',
            'backup.*',
            'teacher.*',
            'tendik.*',
        ],
        'admin' => [
            'dashboard.view-admin',
            'admin.access',
            'attendance.edit',
            'attendance.view',
            'qr.generate',
        ],
        'kepsek' => [
            'dashboard.view-admin',
            'admin.access',
            'attendance.view',
        ],
        'scanner' => [
            'admin.access',
            'attendance.view',
        ],
        'guru' => [
            'teacher.access',
            'attendance.edit',
            'attendance.view',
        ],
        'tendik' => [
            'tendik.access',
        ],
    ];
}