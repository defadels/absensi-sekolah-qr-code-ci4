<div class="card-body table-responsive">
   <?php if (!$empty): ?>
      <table id="tablePetugas" class="table table-hover">
         <thead class="text-info">
            <th><b>No</b></th>
            <th><b>Username</b></th>
            <th><b>Email</b></th>
            <th><b>Role</b></th>
            <th><b>Guru / Tendik</b></th>
            <th><b>Status</b></th>
            <th><b>Aksi</b></th>
         </thead>
         <tbody>
            <?php $i = 1;
            foreach ($data as $value): ?>
               <tr>
                  <td><?= $i; ?></td>
                  <td><?= esc($value['username']); ?></td>
                  <td><b><?= esc($value['email']); ?></b></td>
                  <td>
                     <?php foreach (($value['groups'] ?? []) as $g): ?>
                        <?php
                           $groupClean    = strtolower($g);
                           $usernameLower = strtolower($value['username'] ?? '');

                           // Deteksi otomatis akun Tendik berdasarkan Username, ID, atau Nama Tendik
                           $isTendik = !empty($value['id_tendik']) || !empty($value['nama_tendik']) || str_contains($usernameLower, 'tendik');

                           if ($isTendik && in_array($groupClean, ['guru', 'scanner', 'tendik'])) {
                              $groupClean = 'tendik';
                           }

                           $badge = match($groupClean) {
                              'superadmin'        => 'danger',
                              'admin'             => 'success',
                              'kepsek'            => 'warning',
                              'tendik', 'scanner' => 'info',
                              'guru'              => 'secondary',
                              default             => 'secondary',
                           };

                           $roleLabel = match($groupClean) {
                              'superadmin'        => 'Super Admin',
                              'admin'             => 'Admin',
                              'kepsek'            => 'Kepsek',
                              'tendik', 'scanner' => 'Tendik',
                              'guru'              => 'Guru',
                              default             => function_exists('getUserRole') ? getUserRole($g) : ucfirst($g),
                           };
                        ?>
                        <span class="h6 mr-1 my-auto badge badge-<?= $badge ?> text-capitalize"><?= $roleLabel ?></span>
                     <?php endforeach; ?>
                     <?php if (!empty($value['is_wali_kelas'])): ?>
                        <span class="h6 mr-1 my-auto badge badge-primary text-capitalize">Wali Kelas</span>
                     <?php endif; ?>
                  </td>
                  
                  <!-- TAMPILKAN NAMA GURU ATAU TENDIK -->
                  <td>
                     <?php 
                        $usernameLower = strtolower($value['username'] ?? '');
                        $isTendik      = !empty($value['id_tendik']) || !empty($value['nama_tendik']) || str_contains($usernameLower, 'tendik');

                        if ($isTendik && !empty($value['nama_tendik'])) {
                           $namaPegawai = $value['nama_tendik'];
                        } elseif (!empty($value['nama_guru'])) {
                           $namaPegawai = $value['nama_guru'];
                        } else {
                           $namaPegawai = $value['nama_tendik'] ?? $value['nama_guru'] ?? '-';
                        }
                        echo esc($namaPegawai);
                     ?>
                  </td>

                  <td>
                     <?php if (($value['active'] ?? 0) == 1): ?>
                        <span class="badge badge-success">Aktif</span>
                     <?php else: ?>
                        <span class="badge badge-danger">Non-aktif</span>
                     <?php endif; ?>
                  </td>
                  <td>
                     <?php if ($value['username'] == 'superadmin'): ?>
                        <button disabled class="btn btn-disabled p-2">
                           <i class="material-icons">edit</i>
                        </button>
                        <button disabled class="btn btn-disabled p-2">
                           <i class="material-icons">delete_forever</i>
                        </button>
                     <?php else: ?>
                        <a href="<?= base_url('admin/petugas/activate/' . $value['id']); ?>"
                           title="<?= ($value['active'] ?? 0) == 1 ? 'Non-aktifkan' : 'Aktifkan'; ?>"
                           class="btn <?= ($value['active'] ?? 0) == 1 ? 'btn-warning' : 'btn-success'; ?> p-2">
                           <i class="material-icons"><?= ($value['active'] ?? 0) == 1 ? 'block' : 'check_circle'; ?></i>
                        </a>
                        <a href="<?= base_url('admin/petugas/edit/' . $value['id']); ?>" type="button" class="btn btn-info p-2"
                           id="<?= $value['username']; ?>">
                           <i class="material-icons">edit</i>
                        </a>
                        <form action="<?= base_url('admin/petugas/delete/' . $value['id']); ?>" method="post" class="d-inline">
                           <?= csrf_field(); ?>
                           <input type="hidden" name="_method" value="DELETE">
                           <button onclick="return confirm('Konfirmasi untuk menghapus data');" type="submit"
                              class="btn btn-danger p-2" id="<?= $value['username']; ?>">
                              <i class="material-icons">delete_forever</i>
                              Delete
                           </button>
                        </form>
                     <?php endif; ?>
                  </td>
               </tr>
            <?php $i++;
            endforeach; ?>
         </tbody>
      </table>
      <script>$(document).ready(function(){$('#tablePetugas').DataTable({columnDefs:[{orderable:false,targets:[-1]}]});});</script>
   <?php else: ?>
      <div class="row">
         <div class="col">
            <h4 class="text-center text-danger">Data tidak ditemukan</h4>
         </div>
      </div>
   <?php endif; ?>
</div>