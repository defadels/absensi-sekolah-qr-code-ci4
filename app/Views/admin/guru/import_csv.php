<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Import Data Guru'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-4">

<div class="container" style="max-width: 600px;">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-success text-white py-3">
            <h5 class="mb-0 fw-bold">Import File Excel / CSV Data Guru</h5>
        </div>
        <div class="card-body p-4">

            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger py-2 small mb-3">
                    <?= esc(session()->getFlashdata('error')); ?>
                </div>
            <?php endif; ?>

            <form action="<?= base_url('admin/guru/importCSVItemPost'); ?>" method="POST" enctype="multipart/form-data">
                <?= csrf_field(); ?>

                <div class="mb-3">
                    <label class="form-label fw-bold">Pilih File Excel (.csv / .txt)</label>
                    <input type="file" name="file_csv" class="form-control" accept=".csv, .txt" required>
                </div>

                <div class="alert alert-info py-2 small">
                    <b>Keunggulan Fitur Impor Otomatis:</b>
                    <ul class="mb-0 pl-3">
                        <li>Sistem membaca header kolom Excel secara otomatis (misal: <i>NUPTK, Nama Guru, Mapel, Jenis Kelamin, No HP, Alamat</i>).</li>
                        <li>Urutan kolom di Excel bebas (boleh diacak).</li>
                        <li>Data tetap berhasil masuk meskipun ada sel/kolom yang kosong.</li>
                    </ul>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <a href="<?= base_url('admin/guru'); ?>" class="btn btn-secondary">Kembali</a>
                    <button type="submit" class="btn btn-success font-weight-bold">Proses & Import Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>