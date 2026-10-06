<?= $this->extend('templates/admin_page_layout') ?>

<?= $this->section('content') ?>
<div class="content">
    <div class="container-fluid">
        <?php if ($message = session()->getFlashdata('success')): ?>
            <div class="alert alert-success"><?= esc($message) ?></div>
        <?php endif ?>

        <?php if ($message = session()->getFlashdata('error')): ?>
            <div class="alert alert-danger"><?= esc($message) ?></div>
        <?php endif ?>

        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header card-header-primary">
                        <h4 class="card-title">Migrasi Database</h4>
                        <p class="card-category">Menjalankan migration CI4 yang belum diterapkan</p>
                    </div>
                    <div class="card-body">
                        <p>Gunakan setelah upload versi aplikasi yang memiliki migration baru. Buat backup database terlebih dahulu.</p>
                        <form action="<?= base_url('admin/maintenance/run') ?>" method="post">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="migrate">
                            <div class="form-group">
                                <label for="confirm-migrate">Ketik YA untuk mengonfirmasi</label>
                                <input id="confirm-migrate" type="text" name="confirm" class="form-control" required autocomplete="off">
                            </div>
                            <button type="submit" class="btn btn-primary">Jalankan Migrasi</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header card-header-info">
                        <h4 class="card-title">Cache Aplikasi</h4>
                        <p class="card-category">Membersihkan cache aktif CI4</p>
                    </div>
                    <div class="card-body">
                        <p>Jalankan jika tampilan atau data cache belum ikut berubah setelah deployment.</p>
                        <form action="<?= base_url('admin/maintenance/run') ?>" method="post">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="cache-clear">
                            <button type="submit" class="btn btn-info" onclick="return confirm('Bersihkan cache aplikasi sekarang?')">Bersihkan Cache</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header card-header-warning">
                        <h4 class="card-title">Optimasi CI4</h4>
                        <p class="card-category">Mengaktifkan cache konfigurasi dan file locator</p>
                    </div>
                    <div class="card-body">
                        <p>File <code>app/Config/Optimize.php</code> dan folder cache harus bisa ditulis oleh PHP.</p>
                        <form action="<?= base_url('admin/maintenance/run') ?>" method="post">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="optimize">
                            <div class="form-group">
                                <label for="confirm-optimize">Ketik YA untuk mengonfirmasi</label>
                                <input id="confirm-optimize" type="text" name="confirm" class="form-control" required autocomplete="off">
                            </div>
                            <button type="submit" class="btn btn-warning">Aktifkan Optimasi</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header card-header-default">
                        <h4 class="card-title">Composer Install</h4>
                        <p class="card-category">Dependensi PHP</p>
                    </div>
                    <div class="card-body">
                        <p>Composer tidak dijalankan dari request web. Gunakan fitur Composer cPanel jika tersedia, atau jalankan di komputer lokal lalu upload folder <code>vendor/</code>.</p>
                        <pre>composer install --no-dev --optimize-autoloader</pre>
                        <p class="mb-0">Pastikan versi PHP dan extension yang dipakai cocok dengan hosting.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
