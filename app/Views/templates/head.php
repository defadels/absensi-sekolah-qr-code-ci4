<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <meta name="description" content="Absensi Sekolah QR Code - Sistem absensi modern berbasis QR Code">
   <meta name="theme-color" content="#9c27b0">
   <meta name="apple-mobile-web-app-capable" content="yes">
   <meta name="apple-mobile-web-app-status-bar-style" content="default">
   <?= csrf_meta(); ?>

   <link rel="manifest" href="<?= base_url('manifest.json') ?>">
   <link rel="apple-touch-icon" sizes="192x192" href="<?= base_url('assets/img/pwa-icon-192.png'); ?>">
   <link rel="icon" type="image/png" href="<?= base_url('assets/img/favicon.png'); ?>">

   <?= $this->include('templates/css'); ?>

   <title><?= $title ?></title>
</head>
