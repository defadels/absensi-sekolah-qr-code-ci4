<div class="card-body table-responsive">
   <?php if (!$empty) : ?>
      <table id="tableGuru" class="table table-hover">
         <thead class="text-success">
            <th><b>No</b></th>
            <th><b>NUPTK</b></th>
            <th><b>Nama Guru</b></th>
            <th><b>Mata Pelajaran</b></th> <!-- KOLOM TAMBAHAN MAPEL -->
            <th><b>Jenis Kelamin</b></th>
            <th><b>No HP</b></th>
            <th><b>Alamat</b></th>
            <th width="1%"><b>Aksi</b></th>
         </thead>
         <tbody>
            <?php $i = 1;
            foreach ($data as $value) : ?>
               <tr>
                  <td><?= $i; ?></td>
                  <td><?= esc($value['nuptk'] ?? '-'); ?></td>
                  <td><b><?= esc($value['nama_guru'] ?? $value['nama'] ?? '-'); ?></b></td>
                  <!-- TAMPILAN MATA PELAJARAN -->
                  <td>
                     <span class="badge badge-success" style="font-size: 12px; background-color: #4caf50; color: white; padding: 5px 10px; border-radius: 4px;">
                        <?= esc($value['nama_mapel'] ?? 'Belum ada mapel'); ?>
                     </span>
                  </td>
                  <td><?= esc($value['jenis_kelamin'] ?? '-'); ?></td>
                  <td><?= esc($value['no_hp'] ?? '-'); ?></td>
                  <td><?= esc($value['alamat'] ?? '-'); ?></td>
                  <td>
                      <div class="d-flex justify-content-center">
                         <a title="Edit" href="<?= base_url('admin/guru/edit/' . ($value['id_guru'] ?? $value['id'])); ?>" class="btn btn-success p-2">
                            <i class="material-icons">edit</i>
                         </a>
                         <form action="<?= base_url('admin/guru/delete/' . ($value['id_guru'] ?? $value['id'])); ?>" method="post" class="d-inline">
                            <?= csrf_field(); ?>
                            <input type="hidden" name="_method" value="DELETE">
                            <button title="Delete" onclick="return confirm('Konfirmasi untuk menghapus data');" type="submit" class="btn btn-danger p-2">
                               <i class="material-icons">delete_forever</i>
                            </button>
                         </form>
                         <a title="Download QR Code" href="<?= base_url('admin/qr/guru/' . ($value['id_guru'] ?? $value['id']) . '/download'); ?>" class="btn btn-info p-2">
                            <i class="material-icons">qr_code</i>
                         </a>
                         <a title="Cetak QR Code" href="<?= base_url('admin/qr/guru/print-single/' . ($value['id_guru'] ?? $value['id'])); ?>" class="btn btn-primary p-2" target="_blank">
                            <i class="material-icons">print</i>
                         </a>
                      </div>
                  </td>
               </tr>
            <?php $i++;
            endforeach; ?>
         </tbody>
      </table>
      <script>
         $(document).ready(function(){
            if ($.fn.DataTable.isDataTable('#tableGuru')) {
               $('#tableGuru').DataTable().destroy();
            }
            $('#tableGuru').DataTable({columnDefs:[{orderable:false,targets:[-1]}]});
         });
      </script>
   <?php else : ?>
      <div class="row">
         <div class="col">
            <h4 class="text-center text-danger">Data tidak ditemukan</h4>
         </div>
      </div>
   <?php endif; ?>
</div>