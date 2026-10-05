<?= $this->extend('templates/admin_page_layout') ?>
<?= $this->section('content') ?>
<div class="content">
   <div class="container-fluid">
      
      <?php if (session()->getFlashdata('msg')): ?>
         <div class="alert alert-success">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
               <i class="material-icons">close</i>
            </button>
            <?= session()->getFlashdata('msg') ?>
         </div>
      <?php endif; ?>

      <div class="row">
         <div class="col-md-4">
            <div class="card">
               <div class="card-header card-header-info">
                  <h4 class="card-title"><b>Tambah Jabatan Tendik</b></h4>
               </div>
               <div class="card-body">
                  <form action="<?= base_url('admin/jabatan/simpan'); ?>" method="post">
                     <?= csrf_field() ?>
                     
                     <div class="form-group mb-3">
                        <label class="bmd-label-floating">Pilih Tendik</label>
                        <select name="id_tendik" class="form-control" required>
                           <option value="" disabled selected>-- Pilih Tendik --</option>
                           <?php foreach ($listTendik as $tendik): ?>
                              <option value="<?= $tendik['id_tendik'] ?? $tendik['id']; ?>">
                                 <?= esc($tendik['nama_tendik'] ?? $tendik['nama_lengkap'] ?? $tendik['nama'] ?? '-'); ?>
                              </option>
                           <?php endforeach; ?>
                        </select>
                     </div>
                     
                     <div class="form-group mb-4">
                        <label class="bmd-label-floating">Nama Jabatan</label>
                        <input type="text" name="nama_jabatan" class="form-control" required>
                     </div>
                     
                     <button type="submit" class="btn btn-primary pull-right">Simpan</button>
                     <div class="clearfix"></div>
                  </form>
               </div>
            </div>
         </div>

         <div class="col-md-8">
            <div class="card">
               <div class="card-header card-header-info">
                  <h4 class="card-title"><b>Daftar Jabatan & Tendik</b></h4>
               </div>
               <div class="card-body">
                  <div class="table-responsive">
                     <table class="table table-hover">
                        <thead class="text-info">
                           <th>No</th>
                           <th>Nama Tendik</th>
                           <th>Jabatan</th>
                           <th>Aksi</th>
                        </thead>
                        <tbody>
                           <?php $no = 1; foreach ($listJabatan as $jabatan): ?>
                           <tr>
                              <td><?= $no++; ?></td>
                              <td><b><?= esc($jabatan['nama_tendik'] ?? $jabatan['nama_lengkap'] ?? $jabatan['nama'] ?? '-'); ?></b></td>
                              <td><?= esc($jabatan['nama_jabatan']); ?></td>
                              <td>
                                 <a href="<?= base_url('admin/jabatan/hapus/'.$jabatan['id_jabatan']); ?>" 
                                    class="btn btn-danger btn-sm"
                                    onclick="return confirm('Apakah Anda yakin ingin menghapus jabatan ini?');">
                                    Hapus
                                 </a>
                              </td>
                           </tr>
                           <?php endforeach; ?>
                           
                           <?php if(empty($listJabatan)): ?>
                           <tr>
                              <td colspan="4" class="text-center">Belum ada data jabatan.</td>
                           </tr>
                           <?php endif; ?>
                        </tbody>
                     </table>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
<?= $this->endSection() ?>