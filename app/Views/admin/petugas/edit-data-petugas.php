<?= $this->extend('templates/admin_page_layout') ?>
<?= $this->section('content') ?>
<div class="content">
   <div class="container-fluid">
      <div class="row">
         <div class="col-lg-12 col-md-12">
            <div class="card">
               <div class="card-header card-header-primary">
                  <h4 class="card-title"><b>Form Edit Petugas</b></h4>
               </div>
               <div class="card-body mx-5 my-3">

                  <form action="<?= base_url('admin/petugas/edit'); ?>" method="post">
                     <?= csrf_field() ?>
                     <?php $validation = \Config\Services::validation(); ?>

                     <?php if (session()->getFlashdata('msg')): ?>
                        <div class="pb-2">
                           <div class="alert alert-<?= session()->getFlashdata('error') == true ? 'danger' : 'success' ?> ">
                              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                 <i class="material-icons">close</i>
                              </button>
                              <?= session()->getFlashdata('msg') ?>
                           </div>
                        </div>
                     <?php endif; ?>

                     <input type="hidden" name="id" value="<?= $data['id']; ?>">

                     <div class="form-group mt-4">
                        <label for="username">Username</label>
                        <input type="text" id="username" class="form-control <?= $validation->getError('username') ? 'is-invalid' : ''; ?>" name="username" placeholder="username123"
                           value="<?= old('username') ?? $oldInput['username'] ?? $data['username'] ?>">
                        <div class="invalid-feedback">
                           <?= $validation->getError('username'); ?>
                        </div>
                     </div>

                     <div class="form-group mt-4">
                        <label for="email">Email</label>
                        <input type="email" id="email" class="form-control <?= $validation->getError('email') ? 'is-invalid' : ''; ?>" name="email" placeholder="email@example.com"
                           value="<?= old('email') ?? $oldInput['email'] ?? $data['email'] ?>">
                        <div class="invalid-feedback">
                           <?= $validation->getError('email'); ?>
                        </div>
                     </div>

                     <div class="form-group mt-4">
                        <label for="password">Password baru</label>
                        <input type="password" id="password" class="form-control <?= $validation->getError('password') ? 'is-invalid' : ''; ?>" name="password">
                        <div class="invalid-feedback">
                           <?= $validation->getError('password'); ?>
                        </div>
                     </div>

                     <div class="row mt-4">
                        <div class="col-md-4">
                           <label for="role">Role</label>
                           <?php 
                              $activeRole = strtolower(old('role') ?? $oldInput['role'] ?? ($data['groups'][0] ?? $data['role'] ?? ''));
                           ?>
                           <select class="custom-select <?= $validation->getError('role') ? 'is-invalid' : ''; ?>" id="role" name="role">
                              <option value="">--Pilih role--</option>
                              <option value="superadmin" <?= in_array($activeRole, ['superadmin', 'admin']) ? 'selected' : ''; ?>>Super Admin</option>
                              <option value="guru" <?= ($activeRole === 'guru') ? 'selected' : ''; ?>>Guru</option>
                              <option value="tendik" <?= in_array($activeRole, ['tendik', 'scanner']) ? 'selected' : ''; ?>>Tendik</option>
                           </select>
                           <div class="invalid-feedback">
                              <?= $validation->getError('role'); ?>
                           </div>
                        </div>

                        <div class="col-md-4">
                           <label for="id_guru">Hubungkan ke Guru (Opsional)</label>
                           <select class="custom-select" id="id_guru" name="id_guru">
                              <option value="">--Pilih Guru--</option>
                              <?php foreach (($guru ?? []) as $g): ?>
                                 <option value="<?= $g['id_guru']; ?>" <?= (old('id_guru') ?? $data['id_guru'] ?? '') == $g['id_guru'] ? 'selected' : ''; ?>><?= $g['nama_guru']; ?></option>
                              <?php endforeach; ?>
                           </select>
                        </div>

                        <div class="col-md-4">
                           <label for="id_tendik">Hubungkan ke Tendik (Opsional)</label>
                           <select class="custom-select" id="id_tendik" name="id_tendik">
                              <option value="">--Pilih Tendik--</option>
                              <?php foreach (($tendik ?? []) as $t): ?>
                                 <option value="<?= $t['id_tendik']; ?>" <?= (old('id_tendik') ?? $data['id_tendik'] ?? '') == $t['id_tendik'] ? 'selected' : ''; ?>><?= $t['nama_tendik']; ?></option>
                              <?php endforeach; ?>
                           </select>
                        </div>
                     </div>

                     <button type="submit" class="btn btn-primary btn-block mt-4">Simpan</button>
                  </form>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
<?= $this->endSection() ?>