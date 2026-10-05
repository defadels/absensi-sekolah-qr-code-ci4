<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Riwayat Kehadiran & Perizinan'); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        .badge-status {
            font-weight: bold;
            border-radius: 6px;
            padding: 5px 12px;
            display: inline-block;
            font-size: 13px;
        }
        .status-hadir {
            background-color: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #c8e6c9;
        }
        .status-tidak-hadir {
            background-color: #ffebee;
            color: #c62828;
            border: 1px solid #ffcdd2;
        }
        .status-pending {
            background-color: #fff8e1;
            color: #f57f17;
            border: 1px solid #ffecb3;
        }
    </style>
</head>
<body class="grey lighten-4">

    <!-- Navbar -->
    <nav class="indigo darken-2">
        <div class="nav-wrapper container">
            <a href="<?= base_url('teacher/dashboard'); ?>" class="brand-logo">Portal Kepegawaian</a>
            <ul id="nav-mobile" class="right">
                <li><a href="<?= base_url('teacher/dashboard'); ?>"><i class="material-icons left">dashboard</i>Dashboard</a></li>
            </ul>
        </div>
    </nav>

    <div class="container" style="margin-top: 35px; margin-bottom: 50px;">

        <!-- 1. TABEL RIWAYAT PRESENSI GURU -->
        <div class="card white" style="padding: 25px; border-radius: 8px; margin-bottom: 30px;">
            <div class="row" style="margin-bottom: 10px;">
                <div class="col s12 m8">
                    <h5 style="margin-top: 0; font-weight: bold; display: flex; align-items: center; gap: 8px;">
                        <i class="material-icons indigo-text">history</i> Catatan Riwayat Presensi
                    </h5>
                    <p class="grey-text">Status akan berubah menjadi <b>Hadir</b> jika sudah melakukan scan masuk dan scan pulang.</p>
                </div>
                <div class="col s12 m4 right-align">
                    <a href="<?= base_url('teacher/dashboard'); ?>" class="btn grey darken-1 waves-effect waves-light">
                        <i class="material-icons left">arrow_back</i>Kembali
                    </a>
                </div>
            </div>

            <div class="divider" style="margin-bottom: 20px;"></div>

            <table class="striped responsive-table highlight">
                <thead>
                    <tr class="indigo lighten-5">
                        <th class="center-align" style="width: 60px;">No</th>
                        <th>Tanggal</th>
                        <th>Jam Masuk</th>
                        <th>Jam Pulang</th>
                        <th class="center-align">Status Kehadiran</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($riwayat)) : ?>
                        <?php $no = 1; foreach ($riwayat as $row) : 
                            $masuk  = $row['jam_masuk'] ?? $row['waktu_masuk'] ?? $row['masuk'] ?? null;
                            // Menarik jam_keluar dari database scan
                            $pulang = $row['jam_keluar'] ?? $row['jam_pulang'] ?? $row['waktu_pulang'] ?? $row['pulang'] ?? null;

                            // Cek apakah guru sudah scan pulang
                            $sudahPulang = (!empty($pulang) && $pulang != '-' && $pulang != '00:00:00');
                        ?>
                            <tr>
                                <td class="center-align"><?= $no++; ?></td>
                                <td><?= date('d-m-Y', strtotime($row['tanggal'] ?? $row['tgl'] ?? $row['created_at'] ?? date('Y-m-d'))); ?></td>
                                <td>
                                    <b class="blue-text text-darken-2"><?= esc($masuk ?: '-'); ?></b>
                                </td>
                                <td>
                                    <b class="orange-text text-darken-3"><?= esc($sudahPulang ? $pulang : '-'); ?></b>
                                </td>
                                <td class="center-align">
                                    <?php if ($sudahPulang) : ?>
                                        <span class="badge-status status-hadir">
                                            <i class="material-icons tiny left" style="vertical-align: middle;">check_circle</i> Hadir
                                        </span>
                                    <?php else : ?>
                                        <span class="badge-status status-tidak-hadir">
                                            <i class="material-icons tiny left" style="vertical-align: middle;">cancel</i> Tidak Hadir
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="5" class="center-align grey-text" style="padding: 30px;">
                                <i class="material-icons medium" style="opacity: 0.3;">event_busy</i>
                                <p style="margin-top: 8px;">Belum ada catatan presensi tersimpan.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- 2. TABEL RIWAYAT PENGAJUAN PERIZINAN -->
        <div class="card white" style="padding: 25px; border-radius: 8px;">
            <div class="row" style="margin-bottom: 10px;">
                <div class="col s12 m8">
                    <h5 style="margin-top: 0; font-weight: bold; display: flex; align-items: center; gap: 8px;">
                        <i class="material-icons blue-text">assignment</i> Catatan Riwayat Perizinan
                    </h5>
                    <p class="grey-text">Rekapitulasi permohonan izin/sakit beserta keputusan persetujuan Admin kantor.</p>
                </div>
                <div class="col s12 m4 right-align">
                    <a href="<?= base_url('teacher/perizinan'); ?>" class="btn blue waves-effect waves-light">
                        <i class="material-icons left">add</i>Ajukan Izin Baru
                    </a>
                </div>
            </div>

            <div class="divider" style="margin-bottom: 20px;"></div>

            <table class="striped responsive-table highlight">
                <thead>
                    <tr class="blue lighten-5">
                        <th class="center-align" style="width: 60px;">No</th>
                        <th>Tanggal Pengajuan</th>
                        <th>Jenis Izin</th>
                        <th>Keterangan / Alasan</th>
                        <th class="center-align">Status Persetujuan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($riwayatPerizinan)) : ?>
                        <?php $no = 1; foreach ($riwayatPerizinan as $izin) : 
                            $statusIzin = strtolower($izin['status'] ?? 'pending');
                        ?>
                            <tr>
                                <td class="center-align"><?= $no++; ?></td>
                                <td><?= date('d-m-Y', strtotime($izin['tanggal_izin'] ?? $izin['tanggal_mulai'] ?? $izin['tanggal'] ?? $izin['created_at'] ?? date('Y-m-d'))); ?></td>
                                <td><b><?= esc($izin['jenis_izin'] ?? $izin['kategori'] ?? 'Izin'); ?></b></td>
                                <td><?= esc($izin['keterangan'] ?? $izin['alasan'] ?? '-'); ?></td>
                                <td class="center-align">
                                    <?php if ($statusIzin == 'disetujui' || $statusIzin == 'approved' || $statusIzin == 'setuju') : ?>
                                        <span class="badge-status status-hadir">
                                            <i class="material-icons tiny left" style="vertical-align: middle;">check_circle</i> Disetujui
                                        </span>
                                    <?php elseif ($statusIzin == 'ditolak' || $statusIzin == 'rejected' || $statusIzin == 'tolak') : ?>
                                        <span class="badge-status status-tidak-hadir">
                                            <i class="material-icons tiny left" style="vertical-align: middle;">cancel</i> Ditolak
                                        </span>
                                    <?php else : ?>
                                        <span class="badge-status status-pending">
                                            <i class="material-icons tiny left" style="vertical-align: middle;">hourglass_empty</i> Menunggu Persetujuan
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="5" class="center-align grey-text" style="padding: 30px;">
                                <i class="material-icons medium" style="opacity: 0.3;">assignment_late</i>
                                <p style="margin-top: 8px;">Belum ada riwayat pengajuan perizinan.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
</body>
</html>