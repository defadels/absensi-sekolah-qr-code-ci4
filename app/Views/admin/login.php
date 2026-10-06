<?= $this->extend('templates/starting_page_layout'); ?>

<?= $this->section('navaction') ?>
<?= $this->endSection() ?>

<?= $this->section('content'); ?>
<div class="main-panel">
   <div class="content pt-5 pt-md-2 px-0 px-sm-1 px-md-2">
      <div class="container-fluid px-0 px-md-2">
         <div class="row">
            <div class="col-xxl-5 col-lg-7 col-md-8 col-sm-10 m-auto">
               <div class="card">
                  <div class="card-header card-header-primary mb-48">
                     <h4 class="card-title">Login</h4>
                     <p class="card-category">Silahkan masukkan email dan password anda</p>
                  </div>

                  <div class="card-body mx-5 my-3">
                     <?= view('\App\Views\admin\_message_block') ?>

                     <form action="<?= url_to('login') ?>" method="post">
                        <?= csrf_field() ?>

                        <div class="row">
                           <div class="col-md-12">
                              <div class="form-group">
                                 <label class="bmd-label-floating"><?= lang('Auth.email') ?></label>
                                 <input type="email"
                                    class="form-control <?php if (session('errors.login')): ?>is-invalid<?php endif ?>"
                                    name="email"
                                    inputmode="email"
                                    autocomplete="email"
                                    value="<?= old('email') ?>"
                                    required>
                                 <div class="invalid-feedback">
                                    <?= session('errors.login') ?>
                                 </div>
                              </div>
                           </div>
                        </div>

                        <div class="row mt-3">
                           <div class="col-md-12">
                              <div class="form-group">
                                 <label class="bmd-label-floating">Password</label>
                                 <input type="password"
                                    name="password"
                                    class="form-control <?php if (session('errors.password')): ?>is-invalid<?php endif ?>"
                                    autocomplete="current-password"
                                    required>
                                 <div class="invalid-feedback">
                                    <?= session('errors.password') ?>
                                 </div>
                              </div>
                           </div>
                        </div>

                        <?php if (setting('Auth.sessionConfig')['allowRemembering']): ?>
                           <div class="form-check">
                              <label class="form-check-label">
                                 <input type="checkbox" name="remember" class="form-check-input" <?php if (old('remember')): ?> checked <?php endif ?>>
                                 <?= lang('Auth.rememberMe') ?>
                              </label>
                           </div>
                        <?php endif; ?>

                        <br>

                        <button type="submit" class="btn btn-primary btn-block"><?= lang('Auth.login') ?></button>

                        <div class="clearfix"></div>
                     </form>

                     <div class="text-center mt-3">
                        <button type="button" id="pwaInstallButton" class="btn btn-outline-primary btn-block" aria-controls="pwaInstallHelp" aria-expanded="false">
                           <i class="material-icons mr-2" aria-hidden="true">get_app</i> Pasang Aplikasi
                        </button>
                        <div id="pwaInstallHelp" class="alert alert-info text-left mt-2 mb-0" role="status" hidden></div>
                     </div>

                     <!-- TOMBOL AKTIVASI / KLAIM AKUN (SUDAH DIPERBAIKI KE /DAFTAR) -->
                     <div class="text-center mt-3">
                         <a href="<?= base_url('daftar'); ?>" class="btn btn-default btn-block" style="background-color: #e91e63; color: white; width: 100%; display: block; text-decoration: none; padding: 12px; border-radius: 4px;">
                             <i class="material-icons mr-2" style="vertical-align: middle;">how_to_reg</i> Aktivasi / Klaim Akun (Guru / Tendik)
                         </a>
                     </div>

                     <div class="text-center mt-3">
                        <p class="mb-1">Atau ajukan ketidakhadiran:</p>
                        <div class="d-flex flex-column">
                           <a href="<?= base_url('izin') ?>" class="btn btn-info btn-block mb-2">
                              <i class="material-icons mr-2">mail</i> Ajukan Izin / Sakit
                           </a>
                           <a href="<?= base_url('cek-kehadiran') ?>" class="btn btn-default btn-block">
                              <i class="material-icons mr-2">visibility</i> Cek Kehadiran (Guru / Tendik)
                           </a>
                        </div>
                     </div>

                     <?php if (setting('Auth.allowMagicLinkLogins')): ?>
                        <p class="text-center mt-3">
                           <a href="<?= url_to('magic-link') ?>"><?= lang('Auth.forgotPassword') ?></a>
                        </p>
                     <?php endif; ?>

                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
<?= $this->endSection(); ?>

<?= $this->section('scripts'); ?>
<script>
   (function () {
      var installButton = document.getElementById('pwaInstallButton');
      var installHelp = document.getElementById('pwaInstallHelp');
      var deferredInstallPrompt = null;

      if (!installButton || !installHelp) {
         return;
      }

      function isInstalled() {
         return window.matchMedia('(display-mode: standalone)').matches
            || window.navigator.standalone === true;
      }

      function hideInstallButton() {
         installButton.hidden = true;
         installHelp.hidden = true;
         installButton.setAttribute('aria-expanded', 'false');
      }

      function showInstallHelp(message) {
         installHelp.textContent = message;
         installHelp.hidden = false;
         installButton.setAttribute('aria-expanded', 'true');
      }

      if (isInstalled()) {
         hideInstallButton();
         return;
      }

      window.addEventListener('beforeinstallprompt', function (event) {
         event.preventDefault();
         deferredInstallPrompt = event;
      });

      window.addEventListener('appinstalled', hideInstallButton);

      installButton.addEventListener('click', async function () {
         if (deferredInstallPrompt) {
            var installPrompt = deferredInstallPrompt;
            deferredInstallPrompt = null;
            installPrompt.prompt();

            var choice = await installPrompt.userChoice;
            if (choice.outcome === 'accepted') {
               showInstallHelp('Aplikasi berhasil dipasang di perangkat ini.');
               installButton.hidden = true;
               return;
            }

            showInstallHelp('Pemasangan belum dilakukan. Muat ulang halaman nanti untuk mencoba lagi, atau pasang lewat menu browser.');
            return;
         }

         var userAgent = window.navigator.userAgent || '';
         var isAppleMobile = /iPad|iPhone|iPod/.test(userAgent)
            || (window.navigator.platform === 'MacIntel' && window.navigator.maxTouchPoints > 1);

         if (isAppleMobile) {
            showInstallHelp('Di iPhone atau iPad, buka halaman ini di Safari, ketuk Bagikan, lalu pilih “Tambahkan ke Layar Utama”.');
            return;
         }

         showInstallHelp('Buka menu browser, lalu pilih “Instal aplikasi” atau “Tambahkan ke layar utama”. Pastikan situs dibuka melalui HTTPS.');
      });
   })();
</script>
<?= $this->endSection(); ?>
