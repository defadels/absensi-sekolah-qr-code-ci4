<?= $this->extend('templates/admin_page_layout') ?>
<?= $this->section('content') ?>
<div class="content">
   <div class="container-fluid">
      <div class="row">
         <div class="col-lg-12 col-md-12">
            <div class="card">
               <div class="card-header card-header-success">
                  <h4 class="card-title"><b>Import Data Guru (Excel / CSV)</b></h4>
               </div>
               <div class="card-body mx-5 my-3">

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

                  <form action="<?= base_url('admin/guru/importCSVItemPost'); ?>" method="post" enctype="multipart/form-data">
                     <?= csrf_field() ?>

                     <div class="form-group mt-4">
                        <label for="file_csv" class="font-weight-bold text-dark">Pilih File Excel / CSV (.xlsx, .xls, .csv)</label>
                        <input type="file" id="file_csv" name="file_csv" class="form-control-file mt-2" accept=".csv, .xls, .xlsx, .txt, application/vnd.ms-excel, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" required>
                     </div>

                     <div class="alert alert-info mt-4">
                        <b>Ketentuan Impor Data Guru:</b>
                        <ul class="mb-0 pl-3">
                           <li>Format yang didukung: <b>Excel (.xlsx / .xls)</b> dan <b>CSV (.csv)</b>.</li>
                           <li>Header kolom yang disarankan: <code>NUPTK</code>, <code>Nama</code>, <code>Mapel</code>, <code>Jenis Kelamin</code>, <code>No HP</code>, <code>Alamat</code>.</li>
                           <li>Jika ada sel/kolom data yang tidak diisi, sistem akan memberikan tanda default agar data tetap berhasil masuk dan bisa Anda lengkapi secara manual lewat menu Edit.</li>
                           <li>Data dengan <b>NUPTK yang sudah terdaftar akan otomatis dilewati</b> agar tidak ada duplikasi data.</li>
                        </ul>
                     </div>

                     <div class="d-flex justify-content-between mt-4">
                        <a href="<?= base_url('admin/guru'); ?>" class="btn btn-secondary">Kembali</a>
                        <button type="submit" class="btn btn-success font-weight-bold">Proses & Import Data</button>
                     </div>
                  </form>

                  <hr>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
<?= $this->endSection() ?>