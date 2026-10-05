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
         <!-- FORM TAMBAH MAPEL -->
         <div class="col-md-4">
            <div class="card">
               <div class="card-header card-header-info">
                  <h4 class="card-title"><b>Tambah Mata Pelajaran</b></h4>
               </div>
               <div class="card-body">
                  <form action="<?= base_url('admin/mata-pelajaran/simpan'); ?>" method="post">
                     <?= csrf_field() ?>
                     
                     <div class="form-group mb-3">
                        <label class="bmd-label-floating">Pilih Guru Pengampu</label>
                        <select name="id_guru" class="form-control" required>
                           <option value="" disabled selected>-- Pilih Guru --</option>
                           <?php foreach ($listGuru as $guru): ?>
                              <option value="<?= $guru['id_guru'] ?? $guru['id']; ?>">
                                 <?= esc($guru['nama_guru'] ?? $guru['nama'] ?? '-'); ?>
                              </option>
                           <?php endforeach; ?>
                        </select>
                     </div>
                     
                     <div class="form-group mb-4">
                        <label class="bmd-label-floating">Nama Mata Pelajaran</label>
                        <input type="text" name="nama_mapel" class="form-control" required>
                     </div>
                     
                     <button type="submit" class="btn btn-primary pull-right">Simpan Mapel</button>
                     <div class="clearfix"></div>
                  </form>
               </div>
            </div>
         </div>

         <!-- TABEL DAFTAR MAPEL -->
         <div class="col-md-8">
            <div class="card">
               <div class="card-header card-header-info">
                  <h4 class="card-title"><b>Daftar Mata Pelajaran & Pengampu</b></h4>
               </div>
               <div class="card-body">
                  <div class="table-responsive">
                     <table class="table table-hover">
                        <thead class="text-info">
                           <th>No</th>
                           <th>Nama Guru</th>
                           <th>Mata Pelajaran</th>
                           <th>Aksi</th>
                        </thead>
                        <tbody>
                           <?php $no = 1; foreach ($listMapel as $mapel): ?>
                           <tr>
                              <td><?= $no++; ?></td>
                              <td><b><?= esc($mapel['nama_guru'] ?? $mapel['nama'] ?? '-'); ?></b></td>
                              <td><?= esc($mapel['nama_mapel']); ?></td>
                              <td>
                                 <a href="<?= base_url('admin/mata-pelajaran/hapus/'.$mapel['id_mapel']); ?>" 
                                    class="btn btn-danger btn-sm"
                                    onclick="return confirm('Apakah Anda yakin ingin menghapus mapel ini?');">
                                    Hapus
                                 </a>
                              </td>
                           </tr>
                           <?php endforeach; ?>
                           
                           <?php if(empty($listMapel)): ?>
                           <tr>
                              <td colspan="4" class="text-center">Belum ada data mata pelajaran.</td>
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