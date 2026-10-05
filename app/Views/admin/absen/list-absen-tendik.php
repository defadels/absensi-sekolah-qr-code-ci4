<div class="card-body">
    <div class="row">
        <div class="col-auto me-auto">
            <div class="pt-3 pl-3">
                <h4><b>Absen Tendik</b></h4>
                <p>Daftar tenaga kependidikan muncul di sini</p>
            </div>
        </div>
        <div class="col">
            <a href="#" class="btn btn-primary pl-3 mr-3 mt-3 float-right" onclick="loadTendikData()">
                <i class="material-icons mr-2">refresh</i> Refresh
            </a>
        </div>
    </div>

    <div id="dataTendikList" class="card-body table-responsive pb-5">
        <?php if (!empty($data)): ?>
            <table id="tableAbsenTendik" class="table table-hover">
                <thead class="text-primary">
                    <th><b>No.</b></th>
                    <th><b>NIP</b></th>
                    <th><b>Nama Tendik</b></th>
                    <th><b>Kehadiran</b></th>
                    <th><b>Jam masuk</b></th>
                    <th><b>Jam pulang</b></th>
                    <th><b>Keterangan</b></th>
                    <th><b>Aksi</b></th>
                </thead>
                <tbody>
                    <?php $no = 1; ?>
                    <?php foreach ($data as $value): ?>
                        <?php
                        $idKehadiran = intval($value['id_kehadiran'] ?? ($lewat ? 5 : 4));
                        $kehadiran = kehadiranTendik($idKehadiran);
                        ?>
                        <tr>
                            <td><?= $no; ?></td>
                            <td><?= $value['nip'] ?? '-'; ?></td>
                            <td><b><?= $value['nama_lengkap'] ?? $value['nama_tendik'] ?? '-'; ?></b></td>
                            <td>
                                <p class="p-2 my-auto w-100 badge badge-<?= $kehadiran['color']; ?> text-center">
                                    <b><?= $kehadiran['text']; ?></b>
                                </p>
                            </td>
                            <td><b><?= $value['jam_masuk'] ?? '-'; ?></b></td>
                            <td><b><?= $value['jam_keluar'] ?? '-'; ?></b></td>
                            <td><?= $value['keterangan'] ?? '-'; ?></td>
                            <td>
                                <?php if (!$lewat && can_edit_attendance()): ?>
                                    <button data-toggle="modal" data-target="#ubahModal" onclick="getDataKehadiran(<?= $value['id_presensi'] ?? '-1'; ?>, <?= $value['id_tendik']; ?>)" class="btn btn-info p-2">
                                        <i class="material-icons">edit</i> Edit
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-secondary p-2" disabled>No Action</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php $no++;
                    endforeach ?>
                </tbody>
            </table>
            <script>
                $(document).ready(function(){
                    $('#tableAbsenTendik').DataTable({
                        columnDefs: [{ orderable: false, targets: [-1] }]
                    });
                });
            </script>
        <?php else: ?>
            <div class="row">
                <div class="col">
                    <h4 class="text-center text-danger">Data tidak ditemukan</h4>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
function kehadiranTendik($kehadiran): array
{
    $text = '';
    $color = '';
    switch ($kehadiran) {
        case 1:
            $color = 'success';
            $text = 'Hadir';
            break;
        case 2:
            $color = 'warning';
            $text = 'Sakit';
            break;
        case 3:
            $color = 'info';
            $text = 'Izin';
            break;
        case 4:
            $color = 'danger';
            $text = 'Tanpa keterangan';
            break;
        case 5:
        default:
            $color = 'secondary';
            $text = 'Belum tersedia';
            break;
    }

    return ['color' => $color, 'text' => $text];
}
?>