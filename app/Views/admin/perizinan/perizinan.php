<?= $this->extend('templates/admin_page_layout') ?>

<?= $this->section('content') ?>
<div class="content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-12">
        
        <?php if (session()->getFlashdata('msg')): ?>
          <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('msg') ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
        <?php endif; ?>

        <div class="card">
          <div class="card-header card-header-primary" style="background: linear-gradient(60deg, #ab47bc, #8e24aa);">
            <h4 class="card-title font-weight-bold text-white">Data Perizinan Guru & Tendik</h4>
            <p class="card-category text-white-50">Kelola dan konfirmasi pengajuan izin/sakit dari Guru dan Tenaga Kependidikan</p>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-hover">
                <thead class="text-primary font-weight-bold">
                  <th>No</th>
                  <th>Nama Pegawai</th>
                  <th>Jabatan</th>
                  <th>NIP / NUPTK</th>
                  <th>Tgl Mulai</th>
                  <th>Tgl Selesai</th>
                  <th>Tipe Izin</th>
                  <th>Alasan / Keterangan</th>
                  <th>Bukti</th>
                  <th>Status</th>
                  <th class="text-center">Aksi Konfirmasi</th>
                </thead>
                <tbody>
                  <?php if (!empty($perizinan)): ?>
                    <?php $no = 1; foreach ($perizinan as $p): ?>
                      <tr>
                        <td><?= $no++; ?></td>
                        <td class="font-weight-bold"><?= esc($p['nama_pegawai'] ?? 'Pegawai'); ?></td>
                        <td>
                          <span class="badge badge-<?= ($p['jabatan'] == 'Guru') ? 'info' : 'warning'; ?>">
                            <?= esc($p['jabatan']); ?>
                          </span>
                        </td>
                        <td><?= esc($p['nomor_identitas'] ?? '-'); ?></td>
                        <td><?= esc($p['tanggal'] ?? '-'); ?></td>
                        <td><?= esc($p['tanggal_selesai'] ?? $p['tanggal'] ?? '-'); ?></td>
                        <td>
                          <?php 
                            $idK = $p['id_kehadiran'] ?? 2;
                            if ($idK == 2) echo '<span class="badge badge-danger">Sakit</span>';
                            else echo '<span class="badge badge-warning">Izin</span>';
                          ?>
                        </td>
                        <td><?= esc($p['keterangan'] ?? '-'); ?></td>
                        <td>
                          <?php if (!empty($p['bukti'])): ?>
                            <a href="<?= base_url('uploads/perizinan/' . $p['bukti']); ?>" target="_blank" class="btn btn-sm btn-info py-1 px-2">
                              Lihat Bukti
                            </a>
                          <?php else: ?>
                            <span class="text-muted">-</span>
                          <?php endif; ?>
                        </td>
                        <td>
                          <?php 
                            $st = strtolower($p['status'] ?? 'pending');
                            if ($st == 'diterima' || $st == 'disetujui') {
                                echo '<span class="badge badge-success">Disetujui</span>';
                            } elseif ($st == 'ditolak') {
                                echo '<span class="badge badge-danger">Ditolak</span>';
                            } else {
                                echo '<span class="badge badge-secondary">Pending</span>';
                            }
                          ?>
                        </td>
                        <td class="td-actions text-center">
                          <!-- Tombol Setujui -->
                          <a href="<?= base_url('admin/perizinan/konfirmasi/' . $p['id_perizinan'] . '?status=diterima'); ?>" class="btn btn-success btn-sm font-weight-bold py-1 px-2" title="Setujui">
                            Setujui
                          </a>
                          <!-- Tombol Tolak -->
                          <a href="<?= base_url('admin/perizinan/konfirmasi/' . $p['id_perizinan'] . '?status=ditolak'); ?>" class="btn btn-warning btn-sm font-weight-bold py-1 px-2" title="Tolak">
                            Tolak
                          </a>
                          <!-- Tombol Hapus -->
                          <a href="<?= base_url('admin/perizinan/hapus/' . $p['id_perizinan']); ?>" onclick="return confirm('Yakin ingin menghapus data perizinan ini?')" class="btn btn-danger btn-sm font-weight-bold py-1 px-2" title="Hapus">
                            Hapus
                          </a>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <tr>
                      <td colspan="11" class="text-center text-muted py-4">Belum ada data pengajuan perizinan.</td>
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