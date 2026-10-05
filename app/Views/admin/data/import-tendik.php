<?= $this->extend('templates/admin_page_layout') ?>
<?= $this->section('content') ?>
<div class="content">
   <div class="container-fluid">
      <div class="row">
         <div class="col-lg-12 col-md-12">
            <div class="card">
               <div class="card-header card-header-primary">
                  <h4 class="card-title"><b>Import Data Tendik (Excel / CSV)</b></h4>
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

                  <form action="<?= base_url('admin/tendik/importCSVItemPost'); ?>" method="post" enctype="multipart/form-data">
                     <?= csrf_field() ?>

                     <div class="form-group mt-4">
                        <label for="file_csv" class="font-weight-bold text-dark">Pilih File Excel / CSV (.xlsx, .xls, .csv)</label>
                        <input type="file" id="file_csv" name="file_csv" class="form-control-file mt-2" accept=".csv, .xls, .xlsx, .txt, application/vnd.ms-excel, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" required>
                     </div>

                     <div class="alert alert-info mt-4">
                        <b>Ketentuan Impor Data Tendik:</b>
                        <ul class="mb-0 pl-3">
                           <li>Format file yang didukung: <b>Microsoft Excel (.xlsx / .xls)</b> dan <b>CSV (.csv)</b>.</li>
                           <li>Header kolom yang terdeteksi otomatis: <code>NAMA</code>, <code>JABATAN</code>, <code>NIP / NUPTK</code>, <code>NO HP</code>, <code>ALAMAT</code>.</li>
                           <li>Jika ada kolom yang tidak diisi, sistem akan mengisi nilai default (<code>-</code>) secara otomatis agar data tetap tersimpan dan bisa Anda sunting manual via tombol Edit.</li>
                        </ul>
                     </div>

                     <div class="d-flex justify-content-between mt-4">
                        <a href="<?= base_url('admin/tendik'); ?>" class="btn btn-secondary">Kembali</a>
                        <button type="submit" class="btn btn-primary font-weight-bold">Proses & Import Data</button>
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