<!DOCTYPE html>
<html lang="id">

<?= $this->include("templates/head") ?>

<body>
   <div>
      <?= $this->include("templates/sidebar") ?>
      <div class="main-panel">

         <?= $this->include("templates/navbar") ?>

         <?= $this->renderSection("content") ?>

         <?= $this->include("templates/footer") ?>

      </div>
   </div>

   <?= $this->include("templates/js") ?>

   <script>
      var BaseConfig = {
         baseURL: '<?= base_url() ?>',
         csrfTokenName: '<?= csrf_token() ?>',
         textOk: "Ok",
         textCancel: "Batalkan"
      };

      // REGISTRASI SERVICE WORKER PWA
      if ('serviceWorker' in navigator) {
         window.addEventListener('load', function() {
            navigator.serviceWorker.register('<?= base_url('sw.js'); ?>')
               .then(function(reg) {
                  console.log('PWA ServiceWorker terdaftar:', reg.scope);
               })
               .catch(function(err) {
                  console.log('PWA ServiceWorker gagal:', err);
               });
         });
      }
   </script>

   <?= $this->renderSection("scripts") ?>
</body>

</html>