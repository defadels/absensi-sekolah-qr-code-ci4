<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak QR Code - <?= $guru['nama_guru'] ?? 'Guru'; ?></title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; margin-top: 50px; }
        .card { border: 2px dashed #333; display: inline-block; padding: 30px; border-radius: 10px; width: 300px; }
        img { width: 200px; height: 200px; margin: 15px 0; }
        h3 { margin: 5px 0; font-size: 20px; }
        p { color: #555; margin: 5px 0; }
    </style>
</head>
<body onload="window.print()">

    <div class="card">
        <h3>Kartu Presensi Guru</h3>
        <p>Absensi Sekolah</p>
        
        <img src="https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=<?= $guru['unique_code'] ?? 'GURU-DEFAULT'; ?>" alt="QR Code">
        
        <h3><?= $guru['nama_guru'] ?? 'Nama Guru'; ?></h3>
        <p>NUPTK/NIP: <?= $guru['nuptk'] ?? '-'; ?></p>
    </div>

</body>
</html>