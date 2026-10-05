<?= $this->extend('templates/admin_page_layout') ?>

<?= $this->section('styles') ?>
<style>
    .chart-container {
        position: relative;
        height: 300px;
        width: 100%;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="content">
    <div class="container-fluid">
        <!-- REKAP JUMLAH DATA (DESKTOP) -->
        <div class="row d-none d-sm-flex">
            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="card card-stats">
                    <div class="card-header card-header-primary card-header-icon">
                        <div class="card-icon">
                            <a href="<?= base_url('admin/tendik'); ?>" class="text-white">
                                <i class="material-icons">badge</i>
                            </a>
                        </div>
                        <p class="card-category">Jumlah Tendik</p>
                        <h3 class="card-title"><?= count($tendik); ?></h3>
                    </div>
                    <div class="card-footer">
                        <div class="stats">
                            <i class="material-icons text-primary">check</i>
                            Terdaftar
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="card card-stats">
                    <div class="card-header card-header-success card-header-icon">
                        <div class="card-icon">
                            <a href="<?= base_url('admin/guru'); ?>" class="text-white">
                                <i class="material-icons">person_4</i>
                            </a>
                        </div>
                        <p class="card-category">Jumlah Guru</p>
                        <h3 class="card-title"><?= count($guru); ?></h3>
                    </div>
                    <div class="card-footer">
                        <div class="stats">
                            <i class="material-icons text-success">check</i>
                            Terdaftar
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="card card-stats">
                    <div class="card-header card-header-info card-header-icon">
                        <div class="card-icon">
                            <a href="<?= base_url('admin/mata-pelajaran'); ?>" class="text-white">
                                <i class="material-icons">work</i>
                            </a>
                        </div>
                        <p class="card-category">Jabatan / Mapel</p>
                        <h3 class="card-title text-nowrap"><?= count($jabatan) . ' / ' . count($mapel); ?></h3>
                    </div>
                    <div class="card-footer">
                        <div class="stats">
                            <i class="material-icons">home</i>
                            <?= $generalSettings->school_name ?? 'SMK Swasta Bina Satria Medan'; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="card card-stats">
                    <div class="card-header card-header-danger card-header-icon">
                        <div class="card-icon">
                            <a href="<?= base_url('admin/petugas'); ?>" class="text-white">
                                <i class="material-icons">settings</i>
                            </a>
                        </div>
                        <p class="card-category">Jumlah Petugas</p>
                        <h3 class="card-title"><?= count($petugas); ?></h3>
                    </div>
                    <div class="card-footer">
                        <div class="stats">
                            <i class="material-icons">person</i>
                            Petugas dan Administrator
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- REKAP JUMLAH DATA (MOBILE) -->
        <div class="row d-sm-none">
            <div class="col-6">
                <div class="card">
                    <div class="card-header card-header-primary">
                        <a href="<?= base_url('admin/tendik'); ?>" class="text-white">
                            <div class="d-flex justify-content-end">
                                <div class="text-right">
                                    <p class="card-category">Jumlah Tendik</p>
                                    <h3 class="card-title text-nowrap">
                                        <i class="material-icons">badge</i>
                                        <?= count($tendik); ?>
                                    </h3>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="card-footer">
                        <div class="stats">
                            <i class="material-icons text-primary">check</i>
                            Terdaftar
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="card">
                    <div class="card-header card-header-success">
                        <a href="<?= base_url('admin/guru'); ?>" class="text-white">
                            <div class="d-flex justify-content-end">
                                <div class="text-right">
                                    <p class="card-category">Jumlah Guru</p>
                                    <h3 class="card-title text-nowrap">
                                        <i class="material-icons">person_4</i>
                                        <?= count($guru); ?>
                                    </h3>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="card-footer">
                        <div class="stats">
                            <i class="material-icons text-success">check</i>
                            Terdaftar
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php 
            $now = \CodeIgniter\I18n\Time::now();
            $limit = $generalSettings->jam_pulang_standard ?? '14:00:00';
            $isAfterSchool = $now->toTimeString() > $limit;
        ?>

        <div class="row">
            <!-- STATS TENDIK HARI INI -->
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header card-header-primary">
                        <h4 class="card-title"><b>Absensi Tendik Hari Ini</b></h4>
                        <p class="card-category"><?= $dateNow; ?></p>
                    </div>
                    <div class="card-body">
                        <div class="row text-center flex-nowrap">
                            <div class="col-2">
                                <h5 class="text-success text-nowrap"><b>Hadir</b></h5>
                                <h4 class="text-nowrap"><?= $jumlahKehadiranTendik['hadir']; ?></h4>
                            </div>
                            <div class="col-2">
                                <h5 class="text-warning text-nowrap"><b>Sakit</b></h5>
                                <h4 class="text-nowrap"><?= $jumlahKehadiranTendik['sakit']; ?></h4>
                            </div>
                            <div class="col-2">
                                <h5 class="text-info text-nowrap"><b>Izin</b></h5>
                                <h4 class="text-nowrap"><?= $jumlahKehadiranTendik['izin']; ?></h4>
                            </div>
                            <div class="col-2">
                                <?php if ($isAfterSchool): ?>
                                    <h5 class="text-danger text-nowrap"><b>Alfa</b></h5>
                                    <h4 class="text-nowrap"><?= $jumlahKehadiranTendik['alfa']; ?></h4>
                                <?php else: ?>
                                    <h5 class="text-muted text-nowrap"><b>Belum Scan</b></h5>
                                    <h4 class="text-nowrap"><?= $jumlahKehadiranTendik['alfa']; ?></h4>
                                <?php endif; ?>
                            </div>
                            <div class="col-1">
                                <div class="border-right mx-auto h-100" style="width: 0;"></div>
                            </div>
                            <div class="col-3">
                                <h5 class="text-primary text-nowrap"><b>Total Tendik</b></h5>
                                <h4 class="text-nowrap"><?= $totalTendik; ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- STATS GURU HARI INI -->
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header card-header-success">
                        <h4 class="card-title"><b>Absensi Guru Hari Ini</b></h4>
                        <p class="card-category"><?= $dateNow; ?></p>
                    </div>
                    <div class="card-body">
                        <div class="row text-center flex-nowrap">
                            <div class="col-2">
                                <h5 class="text-success text-nowrap"><b>Hadir</b></h5>
                                <h4 class="text-nowrap"><?= $jumlahKehadiranGuru['hadir']; ?></h4>
                            </div>
                            <div class="col-2">
                                <h5 class="text-warning text-nowrap"><b>Sakit</b></h5>
                                <h4 class="text-nowrap"><?= $jumlahKehadiranGuru['sakit']; ?></h4>
                            </div>
                            <div class="col-2">
                                <h5 class="text-info text-nowrap"><b>Izin</b></h5>
                                <h4 class="text-nowrap"><?= $jumlahKehadiranGuru['izin']; ?></h4>
                            </div>
                            <div class="col-2">
                                <?php if ($isAfterSchool): ?>
                                    <h5 class="text-danger text-nowrap"><b>Alfa</b></h5>
                                    <h4 class="text-nowrap"><?= $jumlahKehadiranGuru['alfa']; ?></h4>
                                <?php else: ?>
                                    <h5 class="text-muted text-nowrap"><b>Belum Scan</b></h5>
                                    <h4 class="text-nowrap"><?= $jumlahKehadiranGuru['alfa']; ?></h4>
                                <?php endif; ?>
                            </div>
                            <div class="col-1">
                                <div class="border-right mx-auto h-100" style="width: 0;"></div>
                            </div>
                            <div class="col-3">
                                <h5 class="text-primary text-nowrap"><b>Total Guru</b></h5>
                                <h4 class="text-nowrap"><?= $totalGuru; ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- CHART TENDIK -->
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header card-header-primary">
                        <h4 class="card-title">Tingkat Kehadiran Tendik</h4>
                        <p class="card-category">Statistik kehadiran 7 hari terakhir | <?= $dateNow; ?></p>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="kehadiranTendik"></canvas>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="stats">
                            <i class="material-icons text-primary">checklist</i> 
                            <a class="text-primary" href="<?= base_url('admin/absen-tendik'); ?>">Lihat data</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CHART GURU -->
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header card-header-success">
                        <h4 class="card-title">Tingkat Kehadiran Guru</h4>
                        <p class="card-category">Statistik kehadiran 7 hari terakhir | <?= $dateNow; ?></p>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="kehadiranGuru"></canvas>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="stats">
                            <i class="material-icons text-success">checklist</i> 
                            <a class="text-success" href="<?= base_url('admin/absen-guru'); ?>">Lihat data</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- Chart.js CDN -->
<script src="<?= base_url('assets/js/plugins/chartjs/chart.umd.min.js') ?>"></script>
<script>
    let kehadiranTendikChart;
    let kehadiranGuruChart;

    const chartLabels = <?= json_encode($dateRange) ?>;
    const chartLabelColors = <?= json_encode($chartLabelColors) ?>;

    const chartColors = {
        hadir: { border: '#4caf50', bg: 'rgba(76, 175, 80, 1)' },
        sakit: { border: '#ff9800', bg: 'rgba(255, 152, 0, 1)' },
        izin: { border: '#00bcd4', bg: 'rgba(0, 188, 212, 1)' },
        alfa: { border: '#f44336', bg: 'rgba(244, 67, 54, 1)' },
        belum_absen: { border: '#999', bg: 'rgba(153, 153, 153, 0.8)' }
    };

    function createChartConfig(data) {
        return {
            type: 'bar',
            data: {
                labels: chartLabels,
                datasets: [
                    {
                        label: 'Hadir',
                        data: data.hadir || [],
                        borderColor: chartColors.hadir.border,
                        backgroundColor: chartColors.hadir.bg,
                    },
                    {
                        label: 'Sakit',
                        data: data.sakit || [],
                        borderColor: chartColors.sakit.border,
                        backgroundColor: chartColors.sakit.bg,
                    },
                    {
                        label: 'Izin',
                        data: data.izin || [],
                        borderColor: chartColors.izin.border,
                        backgroundColor: chartColors.izin.bg,
                    },
                    {
                        label: 'Belum Absen',
                        data: data.belum_absen || [],
                        borderColor: chartColors.belum_absen.border,
                        backgroundColor: chartColors.belum_absen.bg,
                    },
                    {
                        label: 'Alfa',
                        data: data.alfa || [],
                        borderColor: chartColors.alfa.border,
                        backgroundColor: chartColors.alfa.bg,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 }
                    },
                    x: {
                        ticks: { color: chartLabelColors }
                    }
                }
            }
        };
    }

    function initDashboardPageCharts() {
        const tendikCtx = document.getElementById('kehadiranTendik');
        if (tendikCtx) {
            const dataTendik = {
                hadir: <?= json_encode($grafikKehadiranTendik['hadir'] ?? []) ?>,
                sakit: <?= json_encode($grafikKehadiranTendik['sakit'] ?? []) ?>,
                izin: <?= json_encode($grafikKehadiranTendik['izin'] ?? []) ?>,
                alfa: <?= json_encode($grafikKehadiranTendik['alfa'] ?? []) ?>,
                belum_absen: <?= json_encode($grafikKehadiranTendik['belum_absen'] ?? []) ?>
            };
            kehadiranTendikChart = new Chart(tendikCtx, createChartConfig(dataTendik));
        }

        const guruCtx = document.getElementById('kehadiranGuru');
        if (guruCtx) {
            const dataGuru = {
                hadir: <?= json_encode($grafikKehadiranGuru['hadir'] ?? []) ?>,
                sakit: <?= json_encode($grafikKehadiranGuru['sakit'] ?? []) ?>,
                izin: <?= json_encode($grafikKehadiranGuru['izin'] ?? []) ?>,
                alfa: <?= json_encode($grafikKehadiranGuru['alfa'] ?? []) ?>,
                belum_absen: <?= json_encode($grafikKehadiranGuru['belum_absen'] ?? []) ?>
            };
            kehadiranGuruChart = new Chart(guruCtx, createChartConfig(dataGuru));
        }
    }

    $(document).ready(function () {
        initDashboardPageCharts();
    });
</script>
<?= $this->endSection() ?>