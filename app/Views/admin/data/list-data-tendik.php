<div class="card-body table-responsive">
   <?php if (!$empty && !empty($data)): ?>
      <table id="tableTendik" class="table table-hover">
         <thead class="text-primary">
            <th><b>No</b></th>
            <th><b>NIP / NUPTK</b></th>
            <th><b>Nama Tendik</b></th>
            <th><b>Jabatan</b></th>
            <th><b>Jenis Kelamin</b></th>
            <th><b>No HP</b></th>
            <th><b>Alamat</b></th> <!-- HEADER KOLOM ALAMAT -->
            <th width="1%" class="text-center"><b>Aksi</b></th>
         </thead>
         <tbody>
            <?php $i = 1; foreach ($data as $value): ?>
               <?php $idTendik = $value['id_tendik'] ?? $value['id'] ?? 0; ?>
               <tr>
                  <td><?= $i; ?></td>
                  <td><?= esc($value['nip'] ?? $value['nuptk'] ?? '-'); ?></td>
                  <td><b><?= esc($value['nama_tendik'] ?? $value['nama'] ?? '-'); ?></b></td>
                  <td>
                     <span class="badge badge-info" style="font-size: 12px; background-color: #00bcd4; color: white; padding: 5px 10px; border-radius: 4px;">
                        <?= esc($value['nama_jabatan'] ?? 'Belum ada jabatan'); ?>
                     </span>
                  </td>
                  <td><?= esc($value['jenis_kelamin'] ?? '-'); ?></td>
                  <td><?= esc($value['no_hp'] ?? '-'); ?></td>
                  <td><?= esc($value['alamat'] ?? '-'); ?></td> <!-- TAMPILAN DATA ALAMAT -->
                  <td>
                     <div class="d-flex justify-content-center" style="gap: 4px;">
                        <!-- Tombol Edit -->
                        <a title="Edit Data" href="<?= base_url('admin/tendik/edit/' . $idTendik); ?>" class="btn btn-success p-2">
                           <i class="material-icons">edit</i>
                        </a>

                        <!-- Tombol Hapus -->
                        <form action="<?= base_url('admin/tendik/delete/' . $idTendik); ?>" method="post" class="d-inline">
                           <?= csrf_field(); ?>
                           <input type="hidden" name="_method" value="DELETE">
                           <button title="Hapus Data" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?');" type="submit" class="btn btn-danger p-2">
                              <i class="material-icons">delete</i>
                           </button>
                        </form>

                        <!-- Tombol QR Code -->
                        <a title="Download QR Code" href="<?= base_url('admin/qr/tendik/' . $idTendik . '/download'); ?>" class="btn btn-info p-2">
                           <i class="material-icons">qr_code</i>
                        </a>

                        <!-- Tombol Cetak -->
                        <a title="Cetak QR Code" href="<?= base_url('admin/qr/tendik/print-single/' . $idTendik); ?>" class="btn btn-primary p-2" target="_blank">
                           <i class="material-icons">print</i>
                        </a>
                     </div>
                  </td>
               </tr>
            <?php $i++; endforeach; ?>
         </tbody>
      </table>

      <script>
         $(document).ready(function(){
            if ($.fn.DataTable.isDataTable('#tableTendik')) {
               $('#tableTendik').DataTable().destroy();
            }
            $('#tableTendik').DataTable({
               columnDefs:[{orderable:false, targets:[-1]}]
            });
         });
      </script>
   <?php else: ?>
      <div class="row py-4">
         <div class="col text-center">
            <h4 class="text-danger">Belum ada data Tendik.</h4>
         </div>
      </div>
   <?php endif; ?>
</div>