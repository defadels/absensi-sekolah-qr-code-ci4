<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Form Pengajuan Izin / Sakit'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f1f5f9; font-family: system-ui, -apple-system, sans-serif; padding: 30px 15px; }
        .form-card { max-width: 520px; margin: 0 auto; background: #ffffff; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); overflow: hidden; border: 1px solid #e2e8f0; }
        .form-header { background-color: #8e24aa; color: #ffffff; padding: 18px 24px; }
        .btn-submit { background-color: #8e24aa; color: #ffffff; font-weight: 600; padding: 12px; border-radius: 8px; border: none; width: 100%; }
        .btn-submit:hover { background-color: #7b1fa2; color: #ffffff; }
    </style>
</head>
<body>

<div class="form-card">
    <div class="form-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0 fw-bold">Pengajuan Izin / Sakit</h5>
            <?php if (isset($isLoggedIn) && $isLoggedIn && !empty($loggedData)): ?>
                <small class="opacity-75">Pegawai: <b><?= esc($loggedData['nama_guru'] ?? $loggedData['nama_tendik'] ?? $loggedData['nama'] ?? 'Akun Login'); ?></b></small>
            <?php else: ?>
                <small class="opacity-75">Verifikasi Data Pegawai (Guru / Tendik)</small>
            <?php endif; ?>
        </div>
        <a href="<?= (isset($isLoggedIn) && $isLoggedIn) ? base_url('/') : base_url('login'); ?>" class="btn btn-sm btn-light text-dark fw-bold">
            Kembali
        </a>
    </div>

    <div class="p-4">
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger py-2 small mb-3">
                <?= esc(session()->getFlashdata('error')) ?>
            </div>
        <?php endif; ?>

        <form action="<?= base_url('perizinan/submit'); ?>" method="POST" enctype="multipart/form-data">
            <?= csrf_field(); ?>

            <!-- BILA BELUM LOGIN: TAMPILKAN FORM VERIFIKASI DATA -->
            <?php if (!isset($isLoggedIn) || !$isLoggedIn): ?>
                <div class="mb-3">
                    <label class="form-label fw-bold small">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" name="nama" class="form-control" placeholder="Contoh: Bagus Pratama" value="<?= old('nama'); ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold small">NUPTK / NIP <span class="text-danger">*</span></label>
                    <input type="text" name="nuptk_nip" class="form-control" placeholder="Masukkan NUPTK atau NIP Anda" value="<?= old('nuptk_nip'); ?>" required>
                    <div class="form-text text-muted" style="font-size: 11px;">*Sistem akan mencocokkan otomatis dengan data Guru atau Tendik di Admin.</div>
                </div>
                <hr class="my-3">
            <?php else: ?>
                <div class="alert alert-info py-2 small mb-3">
                    <b>Akun Terdeteksi:</b> Sistem mencatat perizinan ini langsung atas nama akun Anda.
                </div>
            <?php endif; ?>

            <div class="mb-3">
                <label class="form-label fw-bold small">Jenis Izin <span class="text-danger">*</span></label>
                <select name="jenis_izin" class="form-select" required>
                    <option value="Izin">Izin Tidak Masuk</option>
                    <option value="Sakit">Sakit</option>
                    <option value="Cuti">Cuti</option>
                    <option value="Dinas Luar">Dinas Luar</option>
                </select>
            </div>

            <div class="row">
                <div class="col-6 mb-3">
                    <label class="form-label fw-bold small">Tanggal Mulai <span class="text-danger">*</span></label>
                    <input type="date" name="tanggal_mulai" class="form-control" value="<?= date('Y-m-d'); ?>" required>
                </div>
                <div class="col-6 mb-3">
                    <label class="form-label fw-bold small">Tanggal Selesai <span class="text-danger">*</span></label>
                    <input type="date" name="tanggal_selesai" class="form-control" value="<?= date('Y-m-d'); ?>" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold small">Alasan / Keterangan <span class="text-danger">*</span></label>
                <textarea name="keterangan" class="form-control" rows="3" placeholder="Tuliskan keterangan perizinan..." required><?= old('keterangan'); ?></textarea>
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold small">Upload Bukti <small class="text-muted fw-normal">(Opsional / Surat Dokter)</small></label>
                <input type="file" name="bukti" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
            </div>

            <button type="submit" class="btn btn-submit">
                Kirim Pengajuan Izin
            </button>
        </form>
    </div>
</div>

</body>
</html>