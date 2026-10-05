<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengajuan Perizinan - Portal Guru</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body class="grey lighten-4">

    <!-- Navbar -->
    <nav class="indigo darken-2">
        <div class="nav-wrapper container">
            <a href="<?= base_url('teacher/dashboard'); ?>" class="brand-logo"><i class="material-icons left">arrow_back</i>Kembali</a>
        </div>
    </nav>

    <!-- Konten Form Perizinan -->
    <div class="container" style="margin-top: 40px; margin-bottom: 50px;">
        <div class="row">
            <div class="col s12 m8 offset-m2">
                <div class="card white" style="padding: 30px;">
                    <h5 class="indigo-text text-darken-2" style="font-weight: bold; margin-bottom: 20px;">Form Pengajuan Perizinan / Sakit</h5>
                    
                    <?php if (session()->has('message')) : ?>
                        <div class="card-panel green lighten-4 green-text text-darken-4" style="padding: 10px; margin-bottom: 15px;">
                            <?= session('message') ?>
                        </div>
                    <?php endif; ?>

                    <form action="<?= base_url('teacher/perizinan/submit'); ?>" method="POST" enctype="multipart/form-data">
                        <?= csrf_field() ?>

                        <div class="input-field">
                            <select name="jenis_izin" required>
                                <option value="" disabled selected>Pilih Keterangan</option>
                                <option value="Izin">Izin</option>
                                <option value="Sakit">Sakit</option>
                            </select>
                            <label>Jenis Perizinan</label>
                        </div>

                        <div class="input-field">
                            <input type="date" name="tanggal_mulai" id="tanggal_mulai" required>
                            <label for="tanggal_mulai" class="active">Tanggal Mulai</label>
                        </div>

                        <div class="input-field">
                            <input type="date" name="tanggal_selesai" id="tanggal_selesai" required>
                            <label for="tanggal_selesai" class="active">Tanggal Selesai</label>
                        </div>

                        <div class="input-field">
                            <textarea name="keterangan" id="keterangan" class="materialize-textarea" required></textarea>
                            <label for="keterangan">Alasan / Keterangan</label>
                        </div>

                        <div style="margin-top: 30px;">
                            <button type="submit" class="btn waves-effect waves-light indigo darken-2" style="width: 100%;">Kirim Pengajuan Izin</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var elems = document.querySelectorAll('select');
            M.FormSelect.init(elems);
        });
    </script>
</body>
</html>