<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'QR Code Saya'); ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body { 
            background: #f4f6f9; 
            min-height: 100vh; 
            font-family: 'Poppins', system-ui, -apple-system, sans-serif;
            margin: 0;
            padding: 0;
        }
        /* Top Navigation Bar */
        .top-bar {
            background: #2b3990;
            color: white;
            padding: 14px 28px;
            display: flex;
            align-items: center;
        }
        .top-bar a {
            color: white;
            text-decoration: none;
            font-size: 18px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
        }
        .top-bar a:hover {
            color: #d0d7ff;
            text-decoration: none;
        }
        .main-container {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }
        .qr-card { 
            max-width: 480px; 
            width: 100%; 
            border: none; 
            border-radius: 16px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.08); 
            background: #fff; 
            text-align: center; 
            padding: 35px 25px; 
        }
        .qr-title {
            color: #2b3990;
            font-weight: 700;
            font-size: 22px;
            margin-bottom: 8px;
        }
        .qr-subtitle {
            color: #8c8c8c;
            font-size: 13px;
            line-height: 1.5;
            margin-bottom: 25px;
        }
        .qr-image-wrapper { 
            width: 240px; 
            height: 240px; 
            margin: 0 auto 20px auto; 
            border: 2px solid #e2e8f0; 
            border-radius: 12px; 
            padding: 12px; 
            background: #fff; 
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .qr-image-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        .user-name {
            font-size: 18px;
            font-weight: 700;
            color: #1a202c;
            margin-bottom: 2px;
        }
        .user-nip {
            font-size: 14px;
            color: #718096;
            margin-bottom: 25px;
        }
        .btn-unduh {
            background-color: #00a65a;
            border-color: #00a65a;
            color: white;
            font-weight: 600;
            padding: 10px 28px;
            border-radius: 6px;
            transition: all 0.2s;
        }
        .btn-unduh:hover {
            background-color: #008d4c;
            color: white;
            text-decoration: none;
        }
        .btn-cetak {
            background-color: #2196F3;
            border-color: #2196F3;
            color: white;
            font-weight: 600;
            padding: 10px 28px;
            border-radius: 6px;
            transition: all 0.2s;
        }
        .btn-cetak:hover {
            background-color: #0c7cd5;
            color: white;
        }
        @media print {
            body { background: #fff; padding: 0; }
            .no-print { display: none !important; }
            .qr-card { box-shadow: none; border: none; padding: 0; }
            .main-container { padding: 0; }
        }
    </style>
</head>
<body>

<!-- BAR KEMBALI DI BAGIAN ATAS -->
<div class="top-bar no-print">
    <a href="<?= base_url('teacher/dashboard'); ?>">
        <i class="fas fa-arrow-left mr-2"></i> Kembali
    </a>
</div>

<div class="main-container">
    <div class="qr-card">
        <h4 class="qr-title">QR Code Presensi Saya</h4>
        <p class="qr-subtitle">Gunakan QR Code ini untuk melakukan scan absensi masuk dan keluar.</p>

        <!-- QR Code Image -->
        <?php 
            $qrData = $guru['unique_code'] ?? $guru['nuptk'] ?? $guru['nip'] ?? 'INVALID';
            $qrUrl  = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($qrData);
        ?>
        <div class="qr-image-wrapper">
            <img src="<?= $qrUrl; ?>" alt="QR Code <?= esc($guru['nama_guru'] ?? ''); ?>">
        </div>

        <div class="user-name"><?= esc($guru['nama_guru'] ?? ''); ?></div>
        
        <?php $nomorInduk = !empty($guru['nuptk']) ? $guru['nuptk'] : (!empty($guru['nip']) ? $guru['nip'] : '-'); ?>
        <div class="user-nip">NUPTK/NIP: <?= esc($nomorInduk); ?></div>

        <div class="no-print d-flex justify-content-center">
            <a href="<?= base_url('teacher/qr/download'); ?>" class="btn btn-unduh mr-2">
                <i class="fas fa-download mr-2"></i>UNDUH
            </a>
            <button onclick="window.print()" class="btn btn-cetak">
                <i class="fas fa-print mr-2"></i>CETAK
            </button>
        </div>
    </div>
</div>

<?php if (!empty($isPrint) && $isPrint): ?>
<script>
    window.onload = function() {
        window.print();
    }
</script>
<?php endif; ?>

</body>
</html>