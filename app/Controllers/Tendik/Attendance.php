<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Riwayat Kehadiran & Perizinan Tendik'); ?></title>
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
        .status-izin {
            background-color: #e3f2fd;
            color: #1565c0;
            border: 1px solid #bbdefb;
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
            <a href="<?= base_url('tendik/dashboard'); ?>" class="brand-logo">Portal Kepegawaian</a>
            <ul id="nav-mobile" class="right">
                <li><a href="<?= base_url('tendik/dashboard'); ?>"><i class="material-icons left">dashboard</i>Dashboard</a></li>
            </ul>
        </div>
    </nav>

    <div class="container" style="margin-top: 35px; margin-bottom: 50px;">

        <!-- 1. TABEL RIWAYAT PRESENSI TENDIK -->
        <div class="card white" style="padding: 25px; border-radius: 8px; margin-bottom: 30px;">
            <div class="row" style="margin-bottom: 10px;">
                <div class="col s12 m8">
                    <h5 style="margin-top: 0; font-weight: bold; display: flex; align-items: center; gap: 8px;">
                        <i class="material-icons indigo-text">history</i> Catatan Riwayat Presensi
                    </h5>
                    <p class="grey-text">Status akan otomatis berubah menjadi <b>Hadir</b> atau <b>Izin</b> sesuai catatan.</p>
                </div>
                <div class="col s12 m4 right-align">
                    <a href="<?= base_url('tendik/dashboard'); ?>" class="btn grey darken-1 waves-effect waves-light">
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
                    <?php 
                        $dataPresensi = $riwayat ?? $riwayatPresensi ?? [];
                    ?>
                    <?php if (!empty($dataPresensi)) : ?>
                        <?php $no = 1; foreach ($dataPresensi as $row) : 
                            $masuk  = $row['jam_masuk'] ?? $row['waktu_masuk'] ?? null;
                            $pulang = $row['jam_keluar'] ?? $row['jam_pulang'] ?? null;
                            $idKehadiran = (int)($row['id_kehadiran'] ?? 1);
                            $isIzin = ($idKehadiran == 2 || $idKehadiran == 3 || strtolower($row['status'] ?? '') === 'izin' || $masuk === '00:00:00');
                            $sudahPulang = (!empty($pulang) && $pulang != '-' && $pulang != '00:00:00');
                        ?>
                            <tr>
                                <td class="center-align"><?= $no++; ?></td>
                                <td><?= date('d-m-Y', strtotime($row['tanggal'] ?? $row['tgl'] ?? date('Y-m-d'))); ?></td>
                                <td>
                                    <b class="blue-text text-darken-2"><?= esc($isIzin ? '-' : ($masuk ?: '-')); ?></b>
                                </td>
                                <td>
                                    <b class="orange-text text-darken-3"><?= esc($isIzin ? '-' : ($sudahPulang ? $pulang : '-')); ?></b>
                                </td>
                                <td class="center-align">
                                    <?php if ($isIzin) : ?>
                                        <span class="badge-status status-izin">
                                            <i class="material-icons tiny left" style="vertical-align: middle;">event_available</i> Izin Disetujui
                                        </span>
                                    <?php elseif ($sudahPulang) : ?>
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

        <!-- 2. TABEL RIWAYAT PENGAJUAN PERIZINAN TENDIK -->
        <div class="card white" style="padding: 25px; border-radius: 8px;">
            <div class="row" style="margin-bottom: 10px;">
                <div class="col s12 m8">
                    <h5 style="margin-top: 0; font-weight: bold; display: flex; align-items: center; gap: 8px;">
                        <i class="material-icons blue-text">assignment</i> Catatan Riwayat Perizinan
                    </h5>
                    <p class="grey-text">Rekapitulasi permohonan izin/sakit beserta status keputusan persetujuan Admin kantor.</p>
                </div>
                <div class="col s12 m4 right-align">
                    <a href="<?= base_url('tendik/perizinan'); ?>" class="btn blue waves-effect waves-light">
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
                    <?php 
                        $dataIzin = $riwayatPerizinan ?? $perizinan ?? $data_perizinan ?? [];
                    ?>
                    <?php if (!empty($dataIzin)) : ?>
                        <?php $no = 1; foreach ($dataIzin as $izin) : 
                            $statusIzin = strtolower($izin['status'] ?? 'pending');
                            $isApproved = in_array($statusIzin, ['disetujui', 'diterima', 'approved', 'setuju']);
                            $isRejected = in_array($statusIzin, ['ditolak', 'rejected', 'tolak']);
                        ?>
                            <tr>
                                <td class="center-align"><?= $no++; ?></td>
                                <td><?= date('d-m-Y', strtotime($izin['tanggal'] ?? $izin['tanggal_mulai'] ?? date('Y-m-d'))); ?></td>
                                <td><b><?= esc($izin['tipe_izin'] ?? $izin['jenis_izin'] ?? 'Izin'); ?></b></td>
                                <td><?= esc($izin['keterangan'] ?? $izin['alasan'] ?? '-'); ?></td>
                                <td class="center-align">
                                    <?php if ($isApproved) : ?>
                                        <span class="badge-status status-hadir">
                                            <i class="material-icons tiny left" style="vertical-align: middle;">check_circle</i> Disetujui
                                        </span>
                                    <?php elseif ($isRejected) : ?>
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