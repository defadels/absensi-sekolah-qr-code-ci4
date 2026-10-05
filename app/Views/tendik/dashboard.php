<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Dashboard Tenaga Kependidikan'); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body class="grey lighten-4">

    <!-- Navbar -->
    <nav class="indigo darken-2">
        <div class="nav-wrapper container">
            <a href="<?= base_url('tendik/dashboard'); ?>" class="brand-logo">Portal Absensi Tendik</a>
            <ul id="nav-mobile" class="right">
                <li><a href="<?= base_url('logout'); ?>"><i class="material-icons left">exit_to_app</i>Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="container" style="margin-top: 30px; margin-bottom: 50px;">
        
        <!-- HEADER BANNER: PROFIL TENDIK -->
        <div class="row">
            <div class="col s12">
                <div class="card white" style="padding: 25px; border-radius: 8px;">
                    <h4 style="margin-top: 0; font-weight: bold;">
                        Selamat Datang, <?= esc($tendik['nama_tendik'] ?? $tendik['nama'] ?? 'Pengguna'); ?>!
                    </h4>
                    <p class="grey-text text-darken-2" style="font-size: 15px; margin-bottom: 0; line-height: 1.8;">
                        <b>NIP / ID:</b> <?= esc($tendik['nip'] ?? '-'); ?><br>
                        <b>Jabatan / Unit Tugas:</b> 
                        <span class="chip green lighten-4 green-text text-darken-3" style="font-weight: bold; font-size: 14px;">
                            <?= esc($jabatan ?? $tendik['jabatan'] ?? $tendik['nama_jabatan'] ?? 'Tenaga Kependidikan'); ?>
                        </span>
                    </p>
                </div>
            </div>
        </div>

        <!-- 4 KARTU MENU UTAMA -->
        <div class="row">
            
            <!-- KARTU 1: QR CODE SAYA -->
            <div class="col s12 m6">
                <div class="card white" style="padding: 20px; border-radius: 8px; min-height: 250px;">
                    <div class="center-align">
                        <i class="material-icons teal-text" style="font-size: 48px;">qr_code_2</i>
                        <h5><b>QR Code Saya</b></h5>
                        <p class="grey-text">Tunjukkan QR Code di HP kepada Admin kantor, atau unduh & cetak ID Card.</p>
                        
                        <div style="margin-top: 20px; display: flex; gap: 8px; justify-content: center; flex-wrap: wrap;">
                            <a href="<?= base_url('tendik/qr'); ?>" class="btn teal waves-effect waves-light">
                                <i class="material-icons left">visibility</i>Buka
                            </a>
                            <a href="<?= base_url('tendik/qr'); ?>" class="btn blue waves-effect waves-light">
                                <i class="material-icons left">file_download</i>Unduh
                            </a>
                            <a href="javascript:void(0)" onclick="let p = window.open('<?= base_url('tendik/qr'); ?>', '_blank'); p.focus(); setTimeout(() => { p.print(); }, 1000);" class="btn indigo waves-effect waves-light">
                                <i class="material-icons left">print</i>Cetak
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KARTU 2: PENGAJUAN PERIZINAN -->
            <div class="col s12 m6">
                <div class="card white" style="padding: 20px; border-radius: 8px; min-height: 250px;">
                    <div class="center-align">
                        <i class="material-icons blue-text" style="font-size: 48px;">assignment</i>
                        <h5><b>Pengajuan Perizinan</b></h5>
                        <p class="grey-text">Ajukan permohonan izin atau sakit secara online beserta dokumen pendukung.</p>
                        <a href="<?= base_url('tendik/perizinan'); ?>" class="btn blue waves-effect waves-light" style="width: 100%; margin-top: 20px;">Ajukan Izin</a>
                    </div>
                </div>
            </div>

        </div>

        <div class="row">

            <!-- KARTU 3: AUDIT LOG / RIWAYAT -->
            <div class="col s12 m6">
                <div class="card white" style="padding: 20px; border-radius: 8px; min-height: 250px;">
                    <div class="center-align">
                        <i class="material-icons brown-text" style="font-size: 48px;">history</i>
                        <h5><b>Audit Log / Riwayat</b></h5>
                        <p class="grey-text">Lihat riwayat aktivitas dan rekapitulasi catatan jam kehadiran Anda.</p>
                        <a href="<?= base_url('tendik/attendance'); ?>" class="btn brown waves-effect waves-light" style="width: 100%; margin-top: 20px;">Lihat Riwayat</a>
                    </div>
                </div>
            </div>

            <!-- KARTU 4: INFORMASI JABATAN -->
            <div class="col s12 m6">
                <div class="card white" style="padding: 20px; border-radius: 8px; min-height: 250px;">
                    <div class="center-align">
                        <i class="material-icons purple-text" style="font-size: 48px;">badge</i>
                        <h5><b>Informasi Jabatan / Tugas</b></h5>
                        <p class="grey-text" style="margin-bottom: 5px;">Posisi / Unit Kerja Aktif saat ini:</p>
                        <h6 class="purple-text text-darken-2" style="font-weight: bold; margin-top: 10px; font-size: 18px;">
                            <?= esc($jabatan ?? $tendik['jabatan'] ?? $tendik['nama_jabatan'] ?? 'Tenaga Kependidikan'); ?>
                        </h6>
                        <div style="margin-top: 25px; padding-top: 10px; border-top: 1px solid #e0e0e0;">
                            <span class="grey-text text-darken-1" style="font-size: 13px;">Status Kepegawaian: <b class="green-text text-darken-2">Aktif</b></span>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
</body>
</html>