<?= $this->extend('templates/admin_page_layout') ?>
<?= $this->section('content') ?>
<style>
  .progress-tendik {
    height: 5px;
    border-radius: 0px;
    background-color: rgb(186, 124, 222);
  }

  .progress-guru {
    height: 5px;
    border-radius: 0px;
    background-color: rgb(58, 192, 85);
  }

  .my-progress-bar {
    height: 5px;
    border-radius: 0px;
  }
</style>
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
        <div class="card">
          <div class="card-header card-header-danger">
            <h4 class="card-title"><b>Generate QR Code</b></h4>
            <p class="card-category">Generate QR berdasarkan kode unik data tendik/guru</p>
          </div>
          <div class="card-body">
            <div class="row">
              <!-- DATA TENDIK -->
              <div class="col-md-6">
                <div class="card">
                  <div class="card-body">
                    <h4 class="text-primary"><b>Data Tendik</b></h4>
                    <p>Total jumlah tendik : <b><?= count($tendik ?? []); ?></b>
                      <br>
                      <a href="<?= base_url('admin/tendik'); ?>">Lihat data</a>
                    </p>
                    <div class="row px-2">
                      <div class="col-12 col-xl-4 px-1 mb-2 mb-xl-0">
                        <button onclick="generateAllQrTendik()" class="btn btn-primary btn-block p-2 font-weight-bold">
                          <i class="material-icons align-middle" style="font-size: 20px;">qr_code</i>
                          <span class="align-middle">Generate All</span>
                          <div id="progressTendik" class="d-none mt-1 small">
                            <span id="progressTextTendik"></span>
                            <i id="progressSelesaiTendik" class="material-icons d-none" style="font-size: 16px;">check</i>
                            <div class="progress progress-tendik" style="height: 3px;">
                              <div id="progressBarTendik" class="progress-bar my-progress-bar bg-white"
                                style="width: 0%;" role="progressbar"></div>
                            </div>
                          </div>
                        </button>
                      </div>
                      <div class="col-12 col-xl-4 px-1 mb-2 mb-xl-0">
                        <a href="<?= base_url('admin/qr/tendik/download'); ?>" class="btn btn-primary btn-block p-2 font-weight-bold">
                          <i class="material-icons align-middle" style="font-size: 20px;">cloud_download</i>
                          <span class="align-middle">Download All</span>
                        </a>
                      </div>
                      <div class="col-12 col-xl-4 px-1 mb-2 mb-xl-0">
                        <a href="<?= base_url('admin/qr/tendik/print'); ?>" class="btn btn-primary btn-block p-2 font-weight-bold" target="_blank">
                          <i class="material-icons align-middle" style="font-size: 20px;">print</i>
                          <span class="align-middle">Cetak All</span>
                        </a>
                      </div>
                    </div>
                    <br>
                    <br>
                    <p>
                      Untuk generate/download QR Code per masing-masing tendik kunjungi
                      <a href="<?= base_url('admin/tendik'); ?>"><b>data tendik</b></a>
                    </p>
                  </div>
                </div>
              </div>

              <!-- DATA GURU -->
              <div class="col-md-6">
                <div class="card">
                  <div class="card-body">
                    <h4 class="text-success"><b>Data Guru</b></h4>
                    <p>Total jumlah guru : <b><?= count($guru ?? []); ?></b>
                      <br>
                      <a href="<?= base_url('admin/guru'); ?>" class="text-success">Lihat data</a>
                    </p>
                    <div class="row px-2">
                      <div class="col-12 col-xl-4 px-1 mb-2 mb-xl-0">
                        <button onclick="generateAllQrGuru()" class="btn btn-success btn-block p-2 font-weight-bold">
                          <i class="material-icons align-middle" style="font-size: 20px;">qr_code</i>
                          <span class="align-middle">Generate All</span>
                          <div id="progressGuru" class="d-none mt-1 small">
                            <span id="progressTextGuru"></span>
                            <i id="progressSelesaiGuru" class="material-icons d-none" style="font-size: 16px;">check</i>
                            <div class="progress progress-guru" style="height: 3px;">
                              <div id="progressBarGuru" class="progress-bar my-progress-bar bg-white"
                                style="width: 0%;" role="progressbar"></div>
                            </div>
                          </div>
                        </button>
                      </div>
                      <div class="col-12 col-xl-4 px-1 mb-2 mb-xl-0">
                        <a href="<?= base_url('admin/qr/guru/download'); ?>" class="btn btn-success btn-block p-2 font-weight-bold">
                          <i class="material-icons align-middle" style="font-size: 20px;">cloud_download</i>
                          <span class="align-middle">Download All</span>
                        </a>
                      </div>
                      <div class="col-12 col-xl-4 px-1 mb-2 mb-xl-0">
                        <a href="<?= base_url('admin/qr/guru/print'); ?>" class="btn btn-success btn-block p-2 font-weight-bold" target="_blank">
                          <i class="material-icons align-middle" style="font-size: 20px;">print</i>
                          <span class="align-middle">Cetak All</span>
                        </a>
                      </div>
                    </div>
                    <br>
                    <br>
                    <p>
                      Untuk generate/download QR Code per masing-masing guru kunjungi
                      <a href="<?= base_url('admin/guru'); ?>" class="text-success"><b>data guru</b></a>
                    </p>
                  </div>
                </div>
              </div>

            </div>
            <p class="text-danger">
              <i class="material-icons" style="font-size: 16px;">warning</i>
              File image QR Code tersimpan di [folder website]/public/uploads/
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
  const dataGuru = [
    <?php if (!empty($guru)): foreach ($guru as $value) {
      $nama = $value['nama_guru'] ?? $value['nama'] ?? '';
      $code = $value['unique_code'] ?? '';
      $nomor = $value['nuptk'] ?? $value['nip'] ?? '';
      echo "{
              'nama' : `$nama`,
              'unique_code' : `$code`,
              'nomor' : `$nomor`
            },";
    } endif; ?>
  ];

  const dataTendik = [
    <?php if (!empty($tendik)): foreach ($tendik as $value) {
      $nama = $value['nama_tendik'] ?? $value['nama'] ?? '';
      $code = $value['unique_code'] ?? '';
      $nomor = $value['nip'] ?? '';
      echo "{
              'nama' : `$nama`,
              'unique_code' : `$code`,
              'nomor' : `$nomor`
            },";
    } endif; ?>
  ];

  function generateAllQrTendik() {
    var i = 1;
    if (dataTendik.length === 0) return;

    $('#progressTendik').removeClass('d-none');
    $('#progressBarTendik')
      .attr('aria-valuenow', '0')
      .attr('aria-valuemin', '0')
      .attr('aria-valuemax', dataTendik.length)
      .attr('style', 'width: 0%;');

    dataTendik.forEach(element => {
      jQuery.ajax({
        url: "<?= base_url('admin/generate/tendik'); ?>",
        type: 'post',
        data: setAjaxData({
          nama: element['nama'],
          unique_code: element['unique_code'],
          nomor: element['nomor']
        }),
        success: function (response) {
          if (i != dataTendik.length) {
            $('#progressTextTendik').html('Progres: ' + i + '/' + dataTendik.length);
          } else {
            $('#progressTextTendik').html('Progres: ' + i + '/' + dataTendik.length + ' selesai');
            $('#progressSelesaiTendik').removeClass('d-none');
          }

          $('#progressBarTendik')
            .attr('aria-valuenow', i)
            .attr('style', 'width: ' + (i / dataTendik.length) * 100 + '%;');
          i++;
        }
      });
    });
  }

  function generateAllQrGuru() {
    var i = 1;
    if (dataGuru.length === 0) return;

    $('#progressGuru').removeClass('d-none');
    $('#progressBarGuru')
      .attr('aria-valuenow', '0')
      .attr('aria-valuemin', '0')
      .attr('aria-valuemax', dataGuru.length)
      .attr('style', 'width: 0%;');

    dataGuru.forEach(element => {
      jQuery.ajax({
        url: "<?= base_url('admin/generate/guru'); ?>",
        type: 'post',
        data: setAjaxData({
          nama: element['nama'],
          unique_code: element['unique_code'],
          nomor: element['nomor']
        }),
        success: function (response) {
          if (i != dataGuru.length) {
            $('#progressTextGuru').html('Progres: ' + i + '/' + dataGuru.length);
          } else {
            $('#progressTextGuru').html('Progres: ' + i + '/' + dataGuru.length + ' selesai');
            $('#progressSelesaiGuru').removeClass('d-none');
          }

          $('#progressBarGuru')
            .attr('aria-valuenow', i)
            .attr('style', 'width: ' + (i / dataGuru.length) * 100 + '%;');
          i++;
        }
      });
    });
  }
</script>
<?= $this->endSection() ?>