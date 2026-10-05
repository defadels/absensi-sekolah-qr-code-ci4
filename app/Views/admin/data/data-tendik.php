<?= $this->extend('templates/admin_page_layout') ?>
<?= $this->section('content') ?>
<div class="content">
   <div class="container-fluid">
      <div class="row">
         <div class="col-lg-12 col-md-12">
            <?php if (session()->getFlashdata('msg')): ?>
               <div class="pb-2 px-3">
                  <div class="alert alert-<?= session()->getFlashdata('error') == true ? 'danger' : 'success' ?> ">
                     <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <i class="material-icons">close</i>
                     </button>
                     <?= session()->getFlashdata('msg') ?>
                  </div>
               </div>
            <?php endif; ?>

            <a class="btn btn-primary ml-3 pl-3 py-3" href="<?= base_url('admin/tendik/create'); ?>">
               <i class="material-icons mr-2">add</i> Tambah Data Tendik
            </a>
            <a class="btn btn-success ml-3 pl-3 py-3" href="<?= base_url('admin/tendik/bulk'); ?>">
               <i class="material-icons mr-2">publish</i> Import CSV
            </a>
            <button class="btn btn-warning ml-3 pl-3 py-3 btn-table-delete" onclick="deleteSelectedTendik('Data yang sudah dihapus tidak bisa dikembalikan');">
               <i class="material-icons mr-2">delete</i> Hapus Terpilih
            </button>
            <form action="<?= base_url('admin/tendik/deleteAll'); ?>" method="post" class="d-inline" onsubmit="return confirm('PERINGATAN! Apakah Anda YAKIN ingin menghapus SEMUA data Tendik? Data yang dihapus tidak bisa dikembalikan!');">
               <?= csrf_field(); ?>
               <button type="submit" class="btn btn-danger ml-3 pl-3 py-3">
                  <i class="material-icons mr-2">delete_sweep</i> Hapus Semua Data
               </button>
            </form>

            <div class="card mt-3">
               <div class="card-header card-header-primary">
                  <h4 class="card-title"><b>Daftar Tenaga Kependidikan (Tendik)</b></h4>
                  <p class="card-category">Angkatan <?= $generalSettings->school_year ?? '2026/2027'; ?></p>
               </div>
               <div class="card-body">
                  <div id="dataTendik">
                     <p class="text-center mt-3"><i class="fa fa-spinner fa-spin"></i> Memuat data tendik...</p>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
   $(document).ready(function () {
      getDataTendik();
   });

   function getDataTendik() {
      jQuery.ajax({
         url: "<?= base_url('admin/tendik'); ?>",
         type: 'post',
         data: typeof setAjaxData === 'function' ? setAjaxData({}) : {},
         success: function (response) {
            $('#dataTendik').html(response);
         },
         error: function (xhr, status, thrown) {
            console.log("Error response:", xhr.responseText);
            $('#dataTendik').html('<div class="alert alert-danger text-center">Terjadi kesalahan saat memuat data tabel.</div>');
         }
      });
   }

   $(document).on('click', '#checkAll', function () {
      $('input:checkbox').not(this).prop('checked', this.checked);
   });

   function deleteSelectedTendik(msg) {
      let selectedIds = [];
      $('input[name="checkbox-table"]:checked').each(function () {
         selectedIds.push($(this).val());
      });

      if (selectedIds.length === 0) {
         alert('Pilih minimal satu data untuk dihapus');
         return;
      }

      if (confirm(msg)) {
         jQuery.ajax({
            url: "<?= base_url('admin/tendik/deleteSelectedTendik'); ?>",
            type: 'post',
            data: typeof setAjaxData === 'function' ? setAjaxData({ tendik_ids: selectedIds }) : { tendik_ids: selectedIds },
            success: function () {
               getDataTendik();
            },
            error: function (xhr, status, thrown) {
               console.log(thrown);
               alert('Gagal menghapus data terpilih');
            }
         });
      }
   }
</script>
<?= $this->endSection() ?>