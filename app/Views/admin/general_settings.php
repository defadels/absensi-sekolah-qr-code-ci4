<?= $this->extend('templates/admin_page_layout') ?>
<?= $this->section('content') ?>
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12 col-md-12">
                
                <!-- Notifikasi Berhasil / Gagal -->
                <?php if (session()->getFlashdata('message')) : ?>
                    <div class="alert alert-success" style="background-color: #4caf50; color: white; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                        <b>BERHASIL:</b> <?= session()->getFlashdata('message'); ?>
                    </div>
                <?php endif; ?>
                <?php if (session()->getFlashdata('error')) : ?>
                    <div class="alert alert-danger" style="background-color: #f44336; color: white; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                        <b>GAGAL:</b> <?= session()->getFlashdata('error'); ?>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header card-header-info">
                        <h4 class="card-title"><b>Pengaturan Utama</b></h4>
                    </div>
                    <div class="card-body mx-5 my-3">

                        <!-- Action Form diarahkan ke URL update -->
                        <form action="<?= base_url('admin/general-settings/update'); ?>" method="post" enctype="multipart/form-data">
                            <?= csrf_field() ?>
                            
                            <div class="form-group mt-4">
                                <label for="school_name">Nama Sekolah</label>
                                <input type="text" id="school_name" class="form-control" name="school_name" placeholder="SMK 1 Indonesia" value="<?= $generalSettings->school_name ?? ''; ?>" required>
                            </div>

                            <div class="form-group mt-4">
                                <label for="school_year">Tahun Ajaran</label>
                                <input type="text" id="school_year" class="form-control" name="school_year" placeholder="2024/2025" value="<?= $generalSettings->school_year ?? ''; ?>" required>
                            </div>

                            <div class="form-group mt-4">
                                <label for="jam_masuk_limit">Batas Jam Masuk</label>
                                <input type="time" id="jam_masuk_limit" class="form-control" name="jam_masuk_limit" value="<?= $generalSettings->jam_masuk_limit ?? '07:15'; ?>" required>
                            </div>

                            <div class="form-group mt-4">
                                <label for="jam_pulang_standard">Batas Jam Pulang</label>
                                <input type="time" id="jam_pulang_standard" class="form-control" name="jam_pulang_standard" value="<?= $generalSettings->jam_pulang_standard ?? '14:00'; ?>" required>
                            </div>

                            <hr class="mt-4 mb-4">

                            <h4 class="mb-3"><b>Pengaturan GPS Absensi</b></h4>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Latitude Sekolah</label>
                                        <input type="text" id="latitude" name="latitude" class="form-control" placeholder="3.595196" value="<?= esc($generalSettings->latitude ?? '') ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Longitude Sekolah</label>
                                        <input type="text" id="longitude" name="longitude" class="form-control" placeholder="98.678901" value="<?= esc($generalSettings->longitude ?? '') ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Radius Absensi (Meter)</label>
                                        <input type="number" id="radius" name="radius" class="form-control" min="1" max="50000" placeholder="100" value="<?= esc($generalSettings->radius ?? 100) ?>">
                                    </div>
                                </div>
                                <div class="col-md-12 mt-2">
                                    <button type="button" id="btnLokasi" class="btn btn-success">
                                        <i class="material-icons">my_location</i> Ambil Lokasi Sekolah
                                    </button>
                                </div>
                            </div>

                            <div class="form-group mt-4">
                                <label>Hari Kerja</label>
                                <div class="row">
                                    <?php
                                    $hariList = ['1' => 'Senin', '2' => 'Selasa', '3' => 'Rabu', '4' => 'Kamis', '5' => "Jum'at", '6' => 'Sabtu', '7' => 'Minggu'];
                                    $hariKerja = !empty($generalSettings->hari_kerja) ? explode(',', $generalSettings->hari_kerja) : ['1','2','3','4','5'];
                                    foreach ($hariList as $val => $label):
                                        $checked = in_array($val, $hariKerja) ? 'checked' : '';
                                    ?>
                                    <div class="col-md-3 col-6 mb-2">
                                        <div class="form-check">
                                            <label class="form-check-label">
                                                <input class="form-check-input" type="checkbox" name="hari_kerja[]" value="<?= $val ?>" <?= $checked ?>>
                                                <?= $label ?>
                                                <span class="form-check-sign"><span class="check"></span></span>
                                            </label>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mt-4">
                                        <label for="copyright">Copyright</label>
                                        <input type="text" id="copyright" class="form-control" name="copyright" placeholder="© 2026 All rights reserved" value="<?= $generalSettings->copyright ?? ''; ?>" required>
                                    </div>
                                </div>

                                <!-- Bagian Logo -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="logo">Logo Sekolah</label>
                                        <div style="margin-bottom: 10px; border: 1px solid #eee; padding: 10px; width: auto; text-align: center;">
                                            <?php 
                                                $logoSrc = base_url('assets/img/logo.png'); // Default gambar bawaan
                                                if (isset($generalSettings->logo) && !empty($generalSettings->logo)) {
                                                    $logoSrc = base_url('uploads/logo/' . $generalSettings->logo);
                                                } elseif (function_exists('getLogo')) {
                                                    $logoSrc = getLogo();
                                                }
                                            ?>
                                            <img id="logo" src="<?= $logoSrc; ?>" alt="logo" style="max-width: 250px; max-height: 250px; object-fit: contain;">
                                        </div>
                                        <div class="display-block">
                                            <button type="button" onclick="$('#logo-upload').trigger('click');" class="btn btn-info btn-sm btn-file-upload">
                                                Ganti Logo
                                            </button>
                                            <input type="file" id="logo-upload" name="logo" style="display: none;" accept="image/jpg,image/jpeg,image/png,image/gif,image/svg+xml"
                                                onchange="previewLogo(this); $('#upload-file-info1').html($(this).val().replace(/.*[\/\\]/, ''));">
                                            <span class="text-sm text-secondary">(.png, .jpg, .jpeg, .gif, .svg)</span>
                                        </div>
                                        <span class='label label-info' id="upload-file-info1"></span>
                                    </div>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-danger btn-block mt-4" style="font-weight: bold; height: 45px;">SIMPAN PENGATURAN</button>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function previewLogo(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('logo').src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}

document.addEventListener("DOMContentLoaded", function () {
    const btnLokasi = document.getElementById("btnLokasi");
    if (!btnLokasi) return;
    btnLokasi.addEventListener("click", function () {
        if (!navigator.geolocation) {
            alert("Browser tidak mendukung GPS."); return;
        }
        navigator.geolocation.getCurrentPosition(
            function(position){
                document.getElementById("latitude").value = position.coords.latitude;
                document.getElementById("longitude").value = position.coords.longitude;
            },
            function(error) { alert("Gagal mendapatkan lokasi GPS."); },
            { enableHighAccuracy: true, timeout: 50000, maximumAge: 0 }
        );
    });
});
</script>
<?= $this->endSection() ?>