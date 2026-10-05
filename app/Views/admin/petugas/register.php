<?= $this->extend('templates/admin_page_layout') ?>
<?= $this->section('content') ?>
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header card-header-info mb-48">
                        <h4 class="card-title"><?= lang('Auth.register') ?></h4>
                        <p class="card-category">Buat akun petugas</p>
                    </div>
                    <div class="card-body mx-5 my-3">

                        <?php if (session()->has('errors')) : ?>
                            <div class="alert alert-danger">
                                <ul>
                                <?php foreach (session('errors') as $error) : ?>
                                    <li><?= $error ?></li>
                                <?php endforeach ?>
                                </ul>
                            </div>
                        <?php endif ?>

                        <form action="<?= base_url('admin/petugas/register') ?>" method="post">
                            <?= csrf_field() ?>

                            <div class="form-group mt-4">
                                <label for="email"><?= lang('Auth.email') ?></label>
                                <input type="email" id="email" class="form-control <?php if (session('errors.email')): ?>is-invalid<?php endif ?>" name="email" aria-describedby="emailHelp"
                                    placeholder="example@email.com" value="<?= old('email') ?>">
                                <?php if (session('errors.email')): ?>
                                    <div class="invalid-feedback">
                                        <?= session('errors.email') ?>
                                    </div>
                                <?php endif ?>
                            </div>

                            <div class="form-group mt-4">
                                <label for="username"><?= lang('Auth.username') ?></label>
                                <input type="text" id="username" class="form-control <?php if (session('errors.username')): ?>is-invalid<?php endif ?>" name="username" placeholder="yourusername"
                                    value="<?= old('username') ?>">
                                <div class="invalid-feedback">
                                    <?= session('errors.username') ?>
                                </div>
                            </div>

                            <div class="form-group mt-4">
                                <label for="password"><?= lang('Auth.password') ?></label>
                                <input type="password" id="password" name="password" class="form-control <?php if (session('errors.password')): ?>is-invalid<?php endif ?>" autocomplete="off">
                                <div class="invalid-feedback">
                                    <?= session('errors.password') ?>
                                </div>
                            </div>

                            <div class="form-group mt-4">
                                <label for="pass_confirm"><?= lang('Auth.passwordConfirm') ?></label>
                                <input type="password" id="pass_confirm" name="pass_confirm" class="form-control <?php if (session('errors.pass_confirm')): ?>is-invalid<?php endif ?>"
                                    autocomplete="off">
                                <div class="invalid-feedback">
                                    <?= session('errors.pass_confirm') ?>
                                </div>
                            </div>

                            <div class="row">
                                <!-- Pilih 3 Role Utama -->
                                <div class="col-md-4">
                                    <div class="form-group mt-4">
                                        <label for="role">Role</label>
                                        <select class="custom-select <?php if (session('errors.role')): ?>is-invalid<?php endif ?>" id="role" name="role" required>
                                            <option value="">--Pilih role--</option>
                                            <option value="superadmin" <?= (old('role') == 'superadmin' || old('role') == '1') ? 'selected' : ''; ?>>Super Admin</option>
                                            <option value="guru" <?= old('role') == 'guru' ? 'selected' : ''; ?>>Guru</option>
                                            <option value="tendik" <?= (old('role') == 'tendik' || old('role') == '0') ? 'selected' : ''; ?>>Tendik</option>
                                        </select>
                                        <div class="invalid-feedback">
                                            <?= session('errors.role') ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Dynamic Wrapper: Hubungkan ke Guru -->
                                <div class="col-md-4" id="guru_wrapper" style="display: none;">
                                    <div class="form-group mt-4">
                                        <label for="id_guru">Hubungkan ke Guru (Opsional)</label>
                                        <select class="custom-select" id="id_guru" name="id_guru">
                                            <option value="">--Pilih Guru--</option>
                                            <?php if (!empty($guru)): ?>
                                                <?php foreach ($guru as $g): ?>
                                                    <option value="<?= $g['id_guru']; ?>" <?= old('id_guru') == $g['id_guru'] ? 'selected' : ''; ?>><?= $g['nama_guru']; ?></option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                </div>

                                <!-- Dynamic Wrapper: Hubungkan ke Tendik -->
                                <div class="col-md-4" id="tendik_wrapper" style="display: none;">
                                    <div class="form-group mt-4">
                                        <label for="id_tendik">Hubungkan ke Tendik (Opsional)</label>
                                        <select class="custom-select" id="id_tendik" name="id_tendik">
                                            <option value="">--Pilih Tendik--</option>
                                            <?php if (!empty($tendik)): ?>
                                                <?php foreach ($tendik as $t): ?>
                                                    <option value="<?= $t['id_tendik']; ?>" <?= old('id_tendik') == $t['id_tendik'] ? 'selected' : ''; ?>><?= $t['nama_tendik']; ?></option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-info btn-block mt-4"><?= lang('Auth.register') ?></button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const roleSelect = document.getElementById('role');
        const guruWrapper = document.getElementById('guru_wrapper');
        const tendikWrapper = document.getElementById('tendik_wrapper');

        function toggleDropdowns() {
            const val = roleSelect.value;
            guruWrapper.style.display = (val === 'guru') ? 'block' : 'none';
            tendikWrapper.style.display = (val === 'tendik' || val === '0') ? 'block' : 'none';
        }

        roleSelect.addEventListener('change', toggleDropdowns);
        toggleDropdowns(); // Jalankan saat pertama load halaman
    });
</script>
<?= $this->endSection() ?>