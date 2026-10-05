<!DOCTYPE html>
<html lang="id">
<head>
   <meta charset="UTF-8">
   <title>Buat Password Akun</title>
   <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
   <style>
      body { background: #f4f6f9; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
      .card-aktivasi { width: 100%; max-width: 480px; border: none; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
      .card-header { background: #27ae60; color: white; border-radius: 10px 10px 0 0 !important; text-align: center; }
   </style>
</head>
<body>

<div class="card card-aktivasi">
   <div class="card-header py-3">
      <h4 class="m-0"><b>Langkah Terakhir</b></h4>
      <small>Buat Username & Password Login</small>
   </div>
   <div class="card-body p-4">

      <div class="alert alert-info py-2 small">
         Data Terverifikasi:<br>
         <b>Nama:</b> <?= esc($data['nama']); ?><br>
         <b>NIP/NUPTK:</b> <?= esc($data['nip']); ?>
      </div>

      <?php if (session()->getFlashdata('error')): ?>
         <div class="alert alert-danger small">
            <?= esc(session()->getFlashdata('error')) ?>
         </div>
      <?php endif; ?>

      <?php if (session()->getFlashdata('errors')): ?>
         <div class="alert alert-danger small">
            <ul class="mb-0 pl-3">
               <?php foreach (session()->getFlashdata('errors') as $err): ?>
                  <li><?= esc($err) ?></li>
               <?php endforeach; ?>
            </ul>
         </div>
      <?php endif; ?>

      <!-- ACTION DIARAHKAN KE DAFTAR/SIMPAN -->
      <form action="<?= base_url('daftar/simpan'); ?>" method="post">
         <?= csrf_field() ?>

         <div class="form-group">
            <label><b>Username Login</b></label>
            <input type="text" name="username" class="form-control" placeholder="Contoh: guru_afandi" value="<?= old('username'); ?>" required>
         </div>

         <div class="form-group">
            <label><b>Email</b></label>
            <input type="email" name="email" class="form-control" placeholder="email@sekolah.sch.id" value="<?= old('email'); ?>" required>
         </div>

         <div class="form-group">
            <label><b>Password Baru</b></label>
            <input type="password" name="password" class="form-control" placeholder="Minimal 6 karakter" minlength="6" required>
         </div>

         <div class="form-group">
            <label><b>Konfirmasi Password</b></label>
            <input type="password" name="pass_confirm" class="form-control" placeholder="Ulangi password di atas" minlength="6" required>
         </div>

         <button type="submit" class="btn btn-success btn-block mt-4 py-2 font-weight-bold">
            SIMPAN & AKTIFKAN AKUN
         </button>
      </form>
   </div>
</div>

</body>
</html>