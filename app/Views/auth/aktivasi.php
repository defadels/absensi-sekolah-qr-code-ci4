<!DOCTYPE html>
<html lang="id">
<head>
   <meta charset="UTF-8">
   <title>Aktivasi Akun Guru & Tendik</title>
   <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
   <style>
      body { background: #f4f6f9; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
      .card-aktivasi { width: 100%; max-width: 450px; border: none; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
      .card-header { background: #2c3e50; color: white; border-radius: 10px 10px 0 0 !important; text-align: center; }
   </style>
</head>
<body>

<div class="card card-aktivasi">
   <div class="card-header py-3">
      <h4 class="m-0"><b>Aktivasi Akun</b></h4>
      <small>Verifikasi Data Guru & Tendik</small>
   </div>
   <div class="card-body p-4">

      <?php if (session()->getFlashdata('error')): ?>
         <div class="alert alert-danger font-weight-bold" style="font-size: 14px;">
            <?= esc(session()->getFlashdata('error')) ?>
         </div>
      <?php endif; ?>

      <!-- ACTION DIARAHKAN KE DAFTAR/CEK -->
      <form action="<?= base_url('daftar/cek'); ?>" method="post">
         <?= csrf_field() ?>

         <div class="form-group">
            <label><b>Nama Lengkap</b></label>
            <input type="text" name="nama_lengkap" class="form-control" placeholder="Masukkan Nama Lengkap Anda" value="<?= old('nama_lengkap'); ?>" required>
         </div>

         <div class="form-group">
            <label><b>NUPTK / NIP</b></label>
            <input type="text" name="nomor_induk" class="form-control" placeholder="Masukkan NUPTK atau NIP Anda" value="<?= old('nomor_induk'); ?>" required>
            <small class="text-muted">*Pastikan Nama & NUPTK/NIP sesuai dengan data Admin</small>
         </div>

         <button type="submit" class="btn btn-primary btn-block mt-4 py-2 font-weight-bold">
            VERIFIKASI DATA ➔
         </button>

         <div class="text-center mt-3">
            <a href="<?= base_url('login'); ?>" class="text-secondary small">← Kembali ke Halaman Login</a>
         </div>
      </form>
   </div>
</div>

</body>
</html>