<!DOCTYPE html>
<html lang="id">

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

   <?= $this->include("templates/css") ?>

   <title>Absensi QR Code</title>

   <style>
      .bg {
         background: url(<?= base_url("assets/img/city-profile.jpg") ?>) center;
         opacity: 0.4;
         background-size: cover;
         height: 100vh;
         width: 100%;
         position: fixed;
         bottom: 0;
         top: 0;
      }

      .main-panel {
         position: relative;
         float: left;
         width: calc(100%);
         transition: 0.33s, cubic-bezier(0.685, 0.0473, 0.346, 1);
      }

      video#previewKamera {
         width: 100%;
         height: auto;
         max-height: 400px;
         margin: 0;
      }

      .previewParent {
         width: auto;
         height: auto;
         margin: auto;
         border: 2px solid grey;
      }

      .unpreview {
         background-color: aquamarine;
         text-align: center;
      }

      .form-select {
         min-width: 200px;
      }

      @media (max-width: 575.98px) {
         .form-select {
            min-width: 0;
            width: 100%;
         }
      }
   </style>
</head>

<body class="pwa-shell">
   <div class="bg bg-image"></div>

   <!-- Navbar -->
   <nav class="navbar navbar-expand-lg navbar-absolute fixed-top">
      <div class="container-fluid">
         <div class="navbar-wrapper row w-100 mx-0">
            <div class="col-md-6 d-flex justify-content-center justify-content-md-start">
               <p class="navbar-brand my-auto text-center mx-0"><b><?= $title ?? "Login" ?></b></p>
            </div>
            <div class="col-md-6 d-flex justify-content-center justify-content-md-end">
               <?= $this->renderSection("navaction") ?>
            </div>
         </div>
      </div>
   </nav>
   <!-- End Navbar -->

   <?= $this->renderSection("content") ?>

   <?= $this->include("templates/js") ?>

   <script>
      var BaseConfig = {
         baseURL: '<?= base_url(); ?>',
         csrfTokenName: '<?= csrf_token() ?>',
         textOk: "Ok",
         textCancel: "Batalkan"
      };
   </script>

    <?= $this->renderSection("scripts") ?>
</body>

</html>
