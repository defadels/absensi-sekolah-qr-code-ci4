<?= $this->extend('templates/laporan') ?>

<?= $this->section('content') ?>

<!-- Style CSS Khusus Toolbar & Media Print -->
<style>
   @media print {
      .no-print {
         display: none !important;
      }
      body {
         margin: 0;
         padding: 0;
      }
   }
   .toolbar-laporan {
      background-color: #2c3e50;
      padding: 12px 20px;
      margin-bottom: 20px;
      border-radius: 6px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 2px 5px rgba(0,0,0,0.2);
   }
   .btn-action {
      padding: 8px 16px;
      border: none;
      border-radius: 4px;
      font-weight: bold;
      cursor: pointer;
      font-size: 14px;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      color: white;
      transition: 0.2s;
   }
   .btn-print { background-color: #27ae60; }
   .btn-print:hover { background-color: #219150; }
   .btn-doc { background-color: #2980b9; }
   .btn-doc:hover { background-color: #1f6391; }
   .btn-back { background-color: #7f8c8d; }
   .btn-back:hover { background-color: #636e72; }
</style>

<!-- Toolbar Tombol Aksi (Tidak Ikut Ter-print) -->
<div class="toolbar-laporan no-print">
   <div>
      <a href="<?= base_url('admin/laporan'); ?>" class="btn-action btn-back">
         ⬅️ Kembali
      </a>
   </div>
   <div style="display: flex; gap: 10px;">
      <button onclick="window.print()" class="btn-action btn-print">
         🖨️ Cetak / Simpan PDF
      </button>
      <button onclick="exportToWord('Laporan_Absensi_Guru_<?= esc($bulan ?? 'Bulan'); ?>')" class="btn-action btn-doc">
         📄 Download Word (.doc)
      </button>
   </div>
</div>

<!-- Area Cetak Laporan -->
<div id="area-laporan">
   <table>
      <tr>
         <td><img src="<?= function_exists('getLogo') ? getLogo() : base_url('assets/img/logo.png'); ?>" width="100px" height="100px"></td>
         <td width="100%">
            <h2 align="center">DAFTAR HADIR GURU</h2>
            <h4 align="center"><?= esc($generalSettings->school_name ?? 'Sekolah'); ?></h4>
            <h4 align="center">TAHUN PELAJARAN <?= esc($generalSettings->school_year ?? '-'); ?></h4>
         </td>
         <td>
            <div style="width:100px"></div>
         </td>
      </tr>
   </table>
   <span>Bulan : <?= esc($bulan ?? '-'); ?></span>

   <table align="center" border="1" cellpadding="4" cellspacing="0" style="width: 100%; border-collapse: collapse;">
      <thead>
         <tr>
            <td></td>
            <td></td>
            <th colspan="<?= count($tanggal ?? []); ?>">Hari/Tanggal</th>
         </tr>
      </thead>
      <thead>
         <tr>
            <td></td>
            <td></td>
            <?php foreach ($tanggal ?? [] as $value) : ?>
               <th align="center"><?= $value->toLocalizedString('E'); ?></th>
            <?php endforeach; ?>
            <td colspan="4" align="center">Total</td>
         </tr>
      </thead>
      <tr>
         <th align="center">No</th>
         <th width="200px">Nama Guru</th>
         <?php foreach ($tanggal ?? [] as $value) : ?>
            <th align="center"><?= $value->format('d'); ?></th>
         <?php endforeach; ?>
         <th align="center" style="background-color:lightgreen;">H</th>
         <th align="center" style="background-color:yellow;">S</th>
         <th align="center" style="background-color:yellow;">I</th>
         <th align="center" style="background-color:red; color:white;">A</th>
      </tr>

      <?php 
      $i = 0; 
      $totalSemuaHadir = 0;
      $totalSemuaSakit = 0;
      $totalSemuaIzin = 0;
      $totalSemuaAlpha = 0;
      ?>

      <?php foreach ($listGuru ?? [] as $guru) : ?>
         <?php
         $jumlahHadir = count(array_filter($listAbsen ?? [], function ($a) use ($i) {
            if (($a['lewat'] ?? false) || is_null($a[$i]['id_kehadiran'] ?? null)) return false;
            return $a[$i]['id_kehadiran'] == 1;
         }));
         $jumlahSakit = count(array_filter($listAbsen ?? [], function ($a) use ($i) {
            if (($a['lewat'] ?? false) || is_null($a[$i]['id_kehadiran'] ?? null)) return false;
            return $a[$i]['id_kehadiran'] == 2;
         }));
         $jumlahIzin = count(array_filter($listAbsen ?? [], function ($a) use ($i) {
            if (($a['lewat'] ?? false) || is_null($a[$i]['id_kehadiran'] ?? null)) return false;
            return $a[$i]['id_kehadiran'] == 3;
         }));
         $jumlahTidakHadir = count(array_filter($listAbsen ?? [], function ($a) use ($i) {
            if ($a['lewat'] ?? false) return false;
            if (is_null($a[$i]['id_kehadiran'] ?? null) || $a[$i]['id_kehadiran'] == 4) return true;
            return false;
         }));

         $totalSemuaHadir += $jumlahHadir;
         $totalSemuaSakit += $jumlahSakit;
         $totalSemuaIzin += $jumlahIzin;
         $totalSemuaAlpha += $jumlahTidakHadir;
         ?>
         <tr>
            <td align="center"><?= $i + 1; ?></td>
            <td><?= esc($guru['nama_guru'] ?? $guru['nama_lengkap'] ?? $guru['nama'] ?? '-'); ?></td>
            <?php foreach ($listAbsen ?? [] as $absen) : ?>
               <?= kehadiran($absen[$i]['id_kehadiran'] ?? (($absen['lewat'] ?? false) ? 5 : 4)); ?>
            <?php endforeach; ?>
            <td align="center"><b><?= $jumlahHadir != 0 ? $jumlahHadir : '-'; ?></b></td>
            <td align="center"><b><?= $jumlahSakit != 0 ? $jumlahSakit : '-'; ?></b></td>
            <td align="center"><b><?= $jumlahIzin != 0 ? $jumlahIzin : '-'; ?></b></td>
            <td align="center"><b><?= $jumlahTidakHadir != 0 ? $jumlahTidakHadir : '-'; ?></b></td>
         </tr>
      <?php
         $i++;
      endforeach; ?>

   </table>

   <br>

   <table border="1" cellpadding="6" cellspacing="0" style="width: 50%; border-collapse: collapse;">
      <tr style="background-color: #f2f2f2;">
         <th colspan="2" align="left">RANGKUMAN KETERANGAN KEHADIRAN GURU</th>
      </tr>
      <tr>
         <td>Total Hadir (H)</td>
         <td align="center"><b><?= $totalSemuaHadir; ?></b> Hari/Kehadiran</td>
      </tr>
      <tr>
         <td>Total Sakit (S)</td>
         <td align="center"><b><?= $totalSemuaSakit; ?></b> Hari</td>
      </tr>
      <tr>
         <td>Total Izin (I)</td>
         <td align="center"><b><?= $totalSemuaIzin; ?></b> Hari</td>
      </tr>
      <tr>
         <td>Total Alpha / Tanpa Keterangan (A)</td>
         <td align="center"><b><?= $totalSemuaAlpha; ?></b> Hari</td>
      </tr>
   </table>

   <br>

   <table>
      <tr>
         <td>Jumlah guru</td>
         <td>: <?= count($listGuru ?? []); ?> Orang</td>
      </tr>
      <tr>
         <td>Laki-laki</td>
         <td>: <?= $jumlahGuru['laki'] ?? 0; ?></td>
      </tr>
      <tr>
         <td>Perempuan</td>
         <td>: <?= $jumlahGuru['perempuan'] ?? 0; ?></td>
      </tr>
   </table>
</div>

<!-- Script Ekspor ke Word Instan -->
<script>
function exportToWord(filename = ''){
    var header = "<html xmlns:o='urn:schemas-microsoft-microsoft-com:office:office' "+
        "xmlns:w='urn:schemas-microsoft-microsoft-com:office:word' "+
        "xmlns='http://www.w3.org/TR/REC-html40'>"+
        "<head><meta charset='utf-8'><title>Laporan</title></head><body>";
    var footer = "</body></html>";
    var sourceHTML = header + document.getElementById("area-laporan").innerHTML + footer;
    
    var source = 'data:application/vnd.ms-word;charset=utf-8,' + encodeURIComponent(sourceHTML);
    var fileDownload = document.createElement("a");
    document.body.appendChild(fileDownload);
    fileDownload.href = source;
    fileDownload.download = filename ? filename + '.doc' : 'Laporan.doc';
    fileDownload.click();
    document.body.removeChild(fileDownload);
}
</script>

<?php
function kehadiran($kehadiran)
{
   $text = '';
   switch ($kehadiran) {
      case 1:
         $text = "<td align='center' style='background-color:lightgreen;'>H</td>";
         break;
      case 2:
         $text = "<td align='center' style='background-color:yellow;'>S</td>";
         break;
      case 3:
         $text = "<td align='center' style='background-color:yellow;'>I</td>";
         break;
      case 4:
         $text = "<td align='center' style='background-color:red; color:white;'>A</td>";
         break;
      case 5:
      default:
         $text = "<td></td>";
         break;
   }

   return $text;
}
?>
<?= $this->endSection() ?>