<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
  <title><?= $title ?? 'Cek Kehadiran Guru & Tendik'; ?></title>
  <meta content='width=device-width, initial-scale=1.0, shrink-to-fit=no' name='viewport' />
  
  <!-- CSS Files -->
  <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Roboto+Slab:400,700|Material+Icons" />
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css">
  <link href="<?= base_url('assets/css/material-dashboard.css?v=2.1.2'); ?>" rel="stylesheet" />

  <style>
    body {
      background: url('<?= base_url('assets/img/bg-login.jpg'); ?>') no-repeat center center fixed;
      background-size: cover;
    }
    .card-login {
      margin-top: 60px;
      border-radius: 10px;
      box-shadow: 0 4px 20px 0px rgba(0, 0, 0, 0.14);
    }
    .btn-purple {
      background-color: #9c27b0 !important;
      color: #fff !important;
    }
  </style>
</head>

<body>
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-7 col-lg-6">
        
        <div class="card card-login">
          <!-- HEADER -->
          <div class="card-header card-header-primary text-center" style="background: linear-gradient(60deg, #ab47bc, #8e24aa);">
            <h4 class="card-title font-weight-bold">Portal Cek Kehadiran</h4>
            <p class="card-category text-white-50">
              Masukkan NIP, NUPTK, atau Nama Pegawai untuk melihat riwayat presensi
            </p>
          </div>

          <!-- BODY FORM -->
          <div class="card-body px-4 py-4">
            <form action="<?= base_url('cek-kehadiran'); ?>" method="get">
              
              <div class="form-group mb-4">
                <label for="keyword" class="bmd-label-floating">NIP / NUPTK / Nama Pegawai</label>
                <input type="text" class="form-control" id="keyword" name="keyword" value="<?= esc($keyword ?? ''); ?>" required>
              </div>

              <div class="form-group mb-4">
                <label for="tanggal">Tanggal Presensi</label>
                <input type="date" class="form-control" id="tanggal" name="tanggal" value="<?= esc($tanggal ?? date('Y-m-d')); ?>" required>
              </div>

              <button type="submit" class="btn btn-purple btn-block font-weight-bold py-3">
                <i class="material-icons align-middle mr-1">search</i> LIHAT RIWAYAT
              </button>

              <a href="<?= base_url(); ?>" class="btn btn-secondary btn-block font-weight-bold py-3 mt-2">
                KEMBALI
              </a>
            </form>

            <!-- HASIL PENCARIAN -->
            <?php if (isset($keyword) && !empty($keyword)): ?>
              <hr class="my-4">
              
              <?php if (!empty($hasil) && isset($hasil['pegawai'])): ?>
                <?php 
                  $pegawai = $hasil['pegawai'];
                  $presensi = $hasil['presensi'];
                  $isTendik = ($tipe === 'tendik');
                  $nama = $isTendik ? ($pegawai['nama_tendik'] ?? $pegawai['nama'] ?? '-') : ($pegawai['nama_guru'] ?? $pegawai['nama'] ?? '-');
                  $nomor = $isTendik ? ($pegawai['nip'] ?? '-') : ($pegawai['nuptk'] ?? $pegawai['nip'] ?? '-');
                  $labelNomor = $isTendik ? 'NIP' : 'NUPTK';
                  $jabatan = $isTendik ? 'Tenaga Kependidikan' : 'Guru';
                ?>

                <div class="alert alert-info bg-light text-dark border p-3 rounded">
                  <h5 class="font-weight-bold text-primary mb-2"><?= esc($nama); ?></h5>
                  <p class="mb-1">Jabatan : <b><?= $jabatan; ?></b></p>
                  <p class="mb-1"><?= $labelNomor; ?> : <b><?= esc($nomor); ?></b></p>
                  <p class="mb-3">Tanggal : <b><?= esc($tanggal); ?></b></p>

                  <div class="row text-center pt-2 border-top">
                    <div class="col-6 border-right">
                      <small class="text-muted d-block">Jam Masuk</small>
                      <b class="text-success" style="font-size: 16px;">
                        <?= !empty($presensi['jam_masuk']) ? $presensi['jam_masuk'] : 'Belum Absen'; ?>
                      </b>
                    </div>
                    <div class="col-6">
                      <small class="text-muted d-block">Jam Pulang</small>
                      <b class="text-info" style="font-size: 16px;">
                        <?= !empty($presensi['jam_keluar']) ? $presensi['jam_keluar'] : 'Belum Absen'; ?>
                      </b>
                    </div>
                  </div>
                </div>

              <?php else: ?>
                <div class="alert alert-warning text-center">
                  <i class="material-icons align-middle mr-1">warning</i>
                  Data Guru / Tendik dengan kata kunci <b>"<?= esc($keyword); ?>"</b> pada tanggal <b><?= esc($tanggal); ?></b> tidak ditemukan.
                </div>
              <?php endif; ?>

            <?php endif; ?>

          </div>
        </div>

      </div>
    </div>
  </div>

  <!-- JS Files -->
  <script src="<?= base_url('assets/js/core/jquery.min.js'); ?>"></script>
  <script src="<?= base_url('assets/js/core/popper.min.js'); ?>"></script>
  <script src="<?= base_url('assets/js/core/bootstrap-material-design.min.js'); ?>"></script>
</body>

</html>