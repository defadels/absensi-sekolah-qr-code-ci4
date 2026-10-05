<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Registrasi Akun Absensi'; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body class="grey lighten-4">

    <div class="container" style="margin-top: 40px; margin-bottom: 50px; max-width: 520px;">
        <div class="card white" style="padding: 30px; border-radius: 8px;">
            <h5 class="indigo-text text-darken-4 center-align" style="font-weight: bold; margin-bottom: 5px;">Registrasi Akun Absensi</h5>
            <p class="grey-text center-align" style="margin-top: 0; margin-bottom: 25px;">Silakan daftar sesuai dengan data kepegawaian Anda</p>

            <?php if (session()->getFlashdata('error')) : ?>
                <div class="card-panel red lighten-4 red-text text-darken-4" style="padding: 10px; border-radius: 5px;">
                    <?= session()->getFlashdata('error'); ?>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('errors')) : ?>
                <div class="card-panel red lighten-4 red-text text-darken-4" style="padding: 10px; border-radius: 5px;">
                    <?php foreach (session()->getFlashdata('errors') as $error) : ?>
                        <p style="margin: 0;"><?= $error ?></p>
                    <?php endforeach ?>
                </div>
            <?php endif; ?>

            <form action="<?= base_url('daftar/store'); ?>" method="post">
                <?= csrf_field(); ?>

                <!-- Nama Lengkap -->
                <div class="input-field">
                    <i class="material-icons prefix">person</i>
                    <input type="text" name="nama" id="nama" value="<?= old('nama'); ?>" required>
                    <label for="nama">Nama Lengkap</label>
                </div>

                <!-- NIP / NUPTK -->
                <div class="input-field">
                    <i class="material-icons prefix">badge</i>
                    <input type="text" name="nip" id="nip" value="<?= old('nip'); ?>" required>
                    <label for="nip">NIP / NUPTK / Nomor Identitas</label>
                </div>

                <!-- Nomor HP -->
                <div class="input-field">
                    <i class="material-icons prefix">phone</i>
                    <input type="text" name="no_hp" id="no_hp" value="<?= old('no_hp'); ?>" required>
                    <label for="no_hp">Nomor HP (WhatsApp)</label>
                </div>

                <!-- Email -->
                <div class="input-field">
                    <i class="material-icons prefix">email</i>
                    <input type="email" name="email" id="email" value="<?= old('email'); ?>" required>
                    <label for="email">Email</label>
                </div>

                <!-- Password -->
                <div class="input-field">
                    <i class="material-icons prefix">lock</i>
                    <input type="password" name="password" id="password" required>
                    <label for="password">Password</label>
                </div>

                <!-- Pilih Role -->
                <div class="input-field">
                    <select name="role" id="role_select" required>
                        <option value="" disabled selected>-- Pilih Jenis Pekerjaan --</option>
                        <option value="guru" <?= old('role') === 'guru' ? 'selected' : ''; ?>>Guru</option>
                        <option value="tendik" <?= old('role') === 'tendik' ? 'selected' : ''; ?>>Tenaga Kependidikan (Tendik)</option>
                    </select>
                    <label>Pilih Role</label>
                </div>

                <!-- Input Khusus Guru (Ketik Sendiri) -->
                <div class="input-field" id="container_guru" style="display: none;">
                    <i class="material-icons prefix">bookmark</i>
                    <input type="text" name="mapel" id="mapel" value="<?= old('mapel'); ?>">
                    <label for="mapel">Mata Pelajaran yang Diampu (Ketik sendiri)</label>
                </div>

                <!-- Input Khusus Tendik (Ketik Sendiri) -->
                <div class="input-field" id="container_tendik" style="display: none;">
                    <i class="material-icons prefix">work</i>
                    <input type="text" name="jabatan" id="jabatan" value="<?= old('jabatan'); ?>">
                    <label for="jabatan">Jabatan / Posisi Tendik (Ketik sendiri)</label>
                </div>

                <!-- Tombol Submit -->
                <div class="center-align" style="margin-top: 30px;">
                    <button type="submit" class="btn indigo darken-3 waves-effect waves-light" style="width: 100%; font-weight: bold; height: 45px; line-height: 45px;">
                        DAFTAR SEKARANG
                    </button>
                </div>

                <div class="center-align" style="margin-top: 20px;">
                    <a href="<?= base_url('login'); ?>" class="blue-text text-darken-2">Sudah punya akun? Login di sini</a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var elems = document.querySelectorAll('select');
            M.FormSelect.init(elems);

            var roleSelect = document.getElementById('role_select');
            var containerGuru = document.getElementById('container_guru');
            var containerTendik = document.getElementById('container_tendik');

            function toggleFields() {
                var val = roleSelect.value;
                if (val === 'guru') {
                    containerGuru.style.display = 'block';
                    containerTendik.style.display = 'none';
                } else if (val === 'tendik') {
                    containerGuru.style.display = 'none';
                    containerTendik.style.display = 'block';
                } else {
                    containerGuru.style.display = 'none';
                    containerTendik.style.display = 'none';
                }
                M.updateTextFields();
            }

            // Event listener saat dropdown berubah
            roleSelect.addEventListener('change', toggleFields);

            // Cek saat pertama kali dimuat
            toggleFields();
        });
    </script>
</body>
</html>