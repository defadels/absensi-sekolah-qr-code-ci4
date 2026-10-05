<?= $this->extend('templates/admin_page_layout') ?>
<?= $this->section('content') ?>
<div class="content">
   <div class="container-fluid">
      <div class="row">
         <div class="col-lg-12 col-md-12">
            <div class="card">
               <div class="card-header card-header-primary">
                  <h4 class="card-title"><b>Form Edit Tendik</b></h4>
               </div>
               <div class="card-body mx-5 my-3">

                  <?php if (session()->getFlashdata('msg')): ?>
                     <div class="pb-2">
                        <div class="alert alert-<?= session()->getFlashdata('error') == true ? 'danger' : 'success' ?>">
                           <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                              <i class="material-icons">close</i>
                           </button>
                           <?= session()->getFlashdata('msg') ?>
                        </div>
                     </div>
                  <?php endif; ?>

                  <form action="<?= base_url('admin/tendik/edit'); ?>" method="post">
                     <?= csrf_field() ?>
                     <?php $validation = \Config\Services::validation(); ?>

                     <input type="hidden" name="id" value="<?= $data['id_tendik'] ?? $data['id'] ?? ''; ?>">

                     <div class="form-group mt-4">
                        <label for="nip">NIP / NUPTK</label>
                        <input type="text" id="nip"
                           class="form-control <?= $validation->getError('nip') ? 'is-invalid' : ''; ?>" name="nip"
                           placeholder="Masukkan NIP" value="<?= old('nip') ?? $oldInput['nip'] ?? $data['nip'] ?? $data['nuptk'] ?? '' ?>">
                        <div class="invalid-feedback">
                           <?= $validation->getError('nip'); ?>
                        </div>
                     </div>

                     <div class="form-group mt-4">
                        <label for="nama">Nama Lengkap</label>
                        <input type="text" id="nama"
                           class="form-control <?= $validation->getError('nama') ? 'is-invalid' : ''; ?>" name="nama"
                           placeholder="Nama lengkap beserta gelar"
                           value="<?= old('nama') ?? $oldInput['nama'] ?? $data['nama_tendik'] ?? $data['nama_lengkap'] ?? $data['nama'] ?? '' ?>" required>
                        <div class="invalid-feedback">
                           <?= $validation->getError('nama'); ?>
                        </div>
                     </div>

                     <!-- INPUT JABATAN TENDIK -->
                     <div class="form-group mt-4">
                        <label for="jabatan">Jabatan Tendik</label>
                        <input type="text" id="jabatan"
                           class="form-control <?= $validation->getError('jabatan') ? 'is-invalid' : ''; ?>" name="jabatan"
                           placeholder="Contoh: Tata Usaha, Perpustakaan, Bendahara, Satpam"
                           value="<?= old('jabatan') ?? $oldInput['jabatan'] ?? $data['nama_jabatan'] ?? $data['jabatan'] ?? '' ?>">
                        <div class="invalid-feedback">
                           <?= $validation->getError('jabatan'); ?>
                        </div>
                     </div>

                     <div class="form-group mt-4">
                        <label for="jk">Jenis Kelamin</label>
                        <?php
                        $jenisKelamin = (old('jk') ?? (isset($oldInput) && is_array($oldInput) ? ($oldInput['jk'] ?? null) : null) ?? ($data['jenis_kelamin'] ?? ''));
                        $l = $jenisKelamin == 'Laki-laki' || $jenisKelamin == '1' ? 'checked' : '';
                        $p = $jenisKelamin == 'Perempuan' || $jenisKelamin == '2' ? 'checked' : '';
                        ?>
                        <div class="form-check form-control pt-0 mb-1 <?= $validation->getError('jk') ? 'is-invalid' : ''; ?>" id="jk">
                           <div class="row">
                              <div class="col-auto">
                                 <div class="row">
                                    <div class="col-auto pr-1">
                                       <input class="form-check" type="radio" name="jk" id="laki" value="1" <?= $l; ?>>
                                    </div>
                                    <div class="col">
                                       <label class="form-check-label pl-0 pt-1" for="laki">
                                          <h6 class="text-dark">Laki-laki</h6>
                                       </label>
                                    </div>
                                 </div>
                              </div>
                              <div class="col">
                                 <div class="row">
                                    <div class="col-auto pr-1">
                                       <input class="form-check" type="radio" name="jk" id="perempuan" value="2" <?= $p; ?>>
                                    </div>
                                    <div class="col">
                                       <label class="form-check-label pl-0 pt-1" for="perempuan">
                                          <h6 class="text-dark">Perempuan</h6>
                                       </label>
                                    </div>
                                 </div>
                              </div>
                           </div>
                        </div>
                        <div class="invalid-feedback">
                           <?= $validation->getError('jk'); ?>
                        </div>
                     </div>

                     <div class="form-group mt-4">
                        <label for="alamat">Alamat</label>
                        <input type="text" id="alamat" name="alamat" class="form-control"
                           value="<?= old('alamat') ?? $oldInput['alamat'] ?? $data['alamat'] ?? '' ?>">
                     </div>

                     <div class="form-group mt-4">
                        <label for="hp">No HP</label>
                        <input type="number" id="hp" name="no_hp"
                           class="form-control <?= $validation->getError('no_hp') ? 'is-invalid' : ''; ?>"
                           value="<?= old('no_hp') ?? $oldInput['no_hp'] ?? $data['no_hp'] ?? '' ?>">
                        <div class="invalid-feedback">
                           <?= $validation->getError('no_hp'); ?>
                        </div>
                     </div>

                     <div class="form-group mt-4">
                        <label for="rfid">RFID Code</label>
                        <input type="text" id="rfid" name="rfid"
                           class="form-control <?= $validation->getError('rfid') ? 'is-invalid' : ''; ?>"
                           value="<?= old('rfid') ?? $oldInput['rfid'] ?? $data['rfid_code'] ?? $data['rfid'] ?? '' ?>"
                           placeholder="Tap RFID Card here">
                        <div class="invalid-feedback">
                           <?= $validation->getError('rfid'); ?>
                        </div>
                     </div>

                     <button type="submit" class="btn btn-primary btn-block mt-4">Simpan Perubahan</button>
                  </form>

                  <hr>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
<?= $this->endSection() ?>