<?= $this->extend('templates/admin_page_layout') ?>
<?= $this->section('content') ?>
<div class="content">
   <div class="container-fluid">
      <div class="row">
         <div class="col-lg-12 col-md-12">
            <div class="card">
               <div class="card-body">
                  <div class="row justify-content-between">
                     <div class="col">
                        <div class="pt-3 pl-3">
                          <h4><b>Absensi Tenaga Kependidikan (Tendik)</b></h4>
                           <p>Halaman pengelolaan absensi tenaga kependidikan</p>
                        </div>
                     </div>
                  </div>

                  <div class="row">
                     <div class="col-md-3">
                        <div class="pt-3 pl-3 pb-2">
                           <h4><b>Tanggal</b></h4>
                           <input class="form-control" type="date" name="tanggal" id="tanggal" value="<?= date('Y-m-d'); ?>" onchange="onDateChange()">
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
      <div class="card" id="dataTendik">
         <div class="card-body">
            <div class="row justify-content-between">
               <div class="col-auto me-auto">
                  <div class="pt-3 pl-3">
                    <h4><b>Absen Tendik</b></h4>
                     <p>Daftar tenaga kependidikan muncul di sini</p>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>

   <!-- Modal ubah kehadiran -->
   <div class="modal fade" id="ubahModal" tabindex="-1" aria-labelledby="modalUbahKehadiran" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
         <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title" id="modalUbahKehadiran">Ubah Kehadiran Tendik</h5>
               <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
               </button>
            </div>
            <div id="modalFormUbahTendik"></div>
         </div>
      </div>
   </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
   $(document).ready(function () {
      loadTendikData();
   });

   function onDateChange() {
      loadTendikData();
   }

   function loadTendikData() {
      var tanggal = $('#tanggal').val();

      jQuery.ajax({
         url: "<?= base_url('/admin/absen-tendik'); ?>",
         type: 'post',
         data: setAjaxData({
            'tanggal': tanggal
         }),
         success: function (response, status, xhr) {
            $('#dataTendik').html(response);
         },
         error: function (xhr, status, thrown) {
            console.log(thrown);
            $('#dataTendik').html(thrown);
         }
      });
   }

   function getDataKehadiran(idPresensi, idTendik) {
      jQuery.ajax({
         url: "<?= base_url('/admin/absen-tendik/kehadiran'); ?>",
         type: 'post',
         data: setAjaxData({
            'id_presensi': idPresensi,
            'id_tendik': idTendik
         }),
         success: function (response, status, xhr) {
            $('#modalFormUbahTendik').html(response);
         },
         error: function (xhr, status, thrown) {
            console.log(thrown);
            $('#modalFormUbahTendik').html(thrown);
         }
      });
   }

   function ubahKehadiran() {
      var tanggal = $('#tanggal').val();
      var form = $('#formUbah').serializeArray();

      form.push({
         name: 'tanggal',
         value: tanggal
      });

      jQuery.ajax({
         url: "<?= base_url('/admin/absen-tendik/edit'); ?>",
         type: 'post',
         data: setSerializedData(form),
         success: function (response, status, xhr) {
            if (response['status']) {
               loadTendikData();
               $('#ubahModal').modal('hide');
               alert('Berhasil ubah kehadiran : ' + response['nama_tendik']);
            } else {
               alert('Gagal ubah kehadiran : ' + response['nama_tendik']);
            }
         },
         error: function (xhr, status, thrown) {
            console.log(thrown);
            alert('Gagal ubah kehadiran\n' + thrown);
         }
      });
   }
</script>
<?= $this->endSection() ?>