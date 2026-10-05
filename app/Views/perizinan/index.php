<?= $this->extend('templates/starting_page_layout') ?>

<?= $this->section('content') ?>
<div class="main-panel">
    <div class="content">
        <div class="container-fluid">
            <div class="row d-flex justify-content-center">
                <div class="col-md-6 col-sm-12">
                    <div class="card">
                        <div class="card-header card-header-primary">
                            <h4 class="card-title">Form Pengajuan Izin/Sakit</h4>
                            <p class="card-category">Isi formulir di bawah ini dengan benar (Khusus Tendik & Guru)</p>
                        </div>
                        <div class="card-body">
                            <?php if (session()->getFlashdata('success')): ?>
                                <div class="alert alert-success font-weight-bold">
                                    <?= session()->getFlashdata('success') ?>
                                </div>
                            <?php endif; ?>

                            <?php if (session()->getFlashdata('errors')): ?>
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        <?php foreach (session()->getFlashdata('errors') as $error): ?>
                                            <li><?= $error ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>

                            <form action="<?= base_url('izin/submit') ?>" method="post" enctype="multipart/form-data">
                                <?= csrf_field() ?>
                                
                                <!-- PILIHAN TENDIK / GURU -->
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label class="font-weight-bold">Saya adalah:</label>
                                            <div class="form-check form-check-radio">
                                                <label class="form-check-label mr-4">
                                                    <input class="form-check-input" type="radio" name="type" id="typeTendik" value="tendik" checked> Tendik
                                                    <span class="circle"><span class="check"></span></span>
                                                </label>
                                                <label class="form-check-label">
                                                    <input class="form-check-input" type="radio" name="type" id="typeGuru" value="guru"> Guru
                                                    <span class="circle"><span class="check"></span></span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- INPUT NIP/NUPTK -->
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label id="labelId" class="bmd-label-floating">NIP Tendik</label>
                                            <div class="input-group">
                                                <input type="text" name="nis" id="nis" class="form-control" required value="<?= old('nis') ?>" placeholder="Masukkan NIP / NUPTK">
                                                <div class="input-group-append">
                                                    <button class="btn btn-primary btn-sm my-0" type="button" id="btnCekNis">Verifikasi</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- PREVIEW PEGAWAI (OPSIONAL) -->
                                <div class="row" id="containerPegawai" style="display:none;">
                                    <div class="col-md-12">
                                        <div class="alert alert-info py-2">
                                            Nama Pegawai: <b id="namaPegawai"></b>
                                            <input type="hidden" name="id_target" id="id_target">
                                        </div>
                                    </div>
                                </div>

                                <!-- TANGGAL -->
                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="label-control">Tanggal Mulai</label>
                                            <input type="date" name="tanggal_mulai" class="form-control" required value="<?= old('tanggal_mulai', date('Y-m-d')) ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="label-control">Tanggal Selesai</label>
                                            <input type="date" name="tanggal_selesai" class="form-control" required value="<?= old('tanggal_selesai', date('Y-m-d')) ?>">
                                        </div>
                                    </div>
                                </div>

                                <!-- TIPE IZIN -->
                                <div class="row mt-3">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>Tipe Izin</label>
                                            <select name="tipe_izin" class="form-control" required>
                                                <option value="Sakit" <?= old('tipe_izin') == 'Sakit' ? 'selected' : '' ?>>Sakit</option>
                                                <option value="Izin" <?= old('tipe_izin') == 'Izin' ? 'selected' : '' ?>>Izin (Acara Keluarga/Kepentingan Lainnya)</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- ALASAN -->
                                <div class="row mt-3">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label class="bmd-label-floating">Alasan / Keterangan</label>
                                            <textarea name="alasan" class="form-control" rows="3" required><?= old('alasan') ?></textarea>
                                        </div>
                                    </div>
                                </div>

                                <!-- BUKTI -->
                                <div class="row mt-3">
                                    <div class="col-md-12">
                                        <label class="font-weight-bold">Unggah Bukti (Foto Surat Dokter/Izin)</label>
                                        <input type="file" name="bukti" class="form-control-file" accept="image/*,.pdf">
                                        <small class="text-muted d-block mt-1">Maksimal 2MB (Format: JPG, JPEG, PNG, PDF)</small>
                                    </div>
                                </div>

                                <!-- TOMBOL KIRIM (TIDAK DILOCK) -->
                                <button type="submit" id="btnSubmit" class="btn btn-primary btn-block mt-4 py-3 font-weight-bold">KIRIM PENGAJUAN</button>
                                <a href="<?= base_url() ?>" class="btn btn-default btn-block">Kembali ke Beranda</a>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const btnCekNis = document.getElementById('btnCekNis');
        const inputNis = document.getElementById('nis');
        const labelId = document.getElementById('labelId');
        const containerPegawai = document.getElementById('containerPegawai');
        const namaPegawai = document.getElementById('namaPegawai');
        const idTarget = document.getElementById('id_target');
        const typeRadios = document.querySelectorAll('input[name="type"]');

        typeRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                labelId.innerText = this.value === 'guru' ? 'NUPTK / NIP Guru' : 'NIP Tendik';
                containerPegawai.style.display = 'none';
            });
        });

        btnCekNis.addEventListener('click', function() {
            const idValue = inputNis.value;
            const typeValue = document.querySelector('input[name="type"]:checked').value;

            if (!idValue) {
                alert("Masukkan NIP / NUPTK terlebih dahulu");
                return;
            }

            fetch('<?= base_url('izin/get-siswa') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: `nis=${encodeURIComponent(idValue)}&type=${encodeURIComponent(typeValue)}&<?= csrf_token() ?>=<?= csrf_hash() ?>`
                })
                .then(response => response.json())
                .then(result => {
                    if (result.status === 'success') {
                        containerPegawai.style.display = 'block';
                        namaPegawai.innerText = result.data.nama;
                        idTarget.value = result.data.id;
                        alert("Data ditemukan: " + result.data.nama);
                    } else {
                        containerPegawai.style.display = 'none';
                        alert(result.message || "Data tidak ditemukan");
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert("Terjadi kesalahan saat mengecek data.");
                });
        });
    });
</script>
<?= $this->endSection() ?>