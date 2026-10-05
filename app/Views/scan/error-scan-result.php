<div class="p-2">
   <h4 class="text-danger font-weight-bold mb-3"><?= $msg ?? 'Terjadi kesalahan'; ?></h4>

   <?php if (isset($data) && !empty($data)): ?>
      <?php 
         $typeStr = isset($type) ? strtolower(is_object($type) ? ($type->value ?? $type->name ?? '') : (string)$type) : '';
      ?>
      <div class="text-left style-result" style="font-size: 15px; line-height: 1.8;">
         <?php if (str_contains($typeStr, 'tendik')): ?>
            <p class="mb-1">Nama : <b><?= $data['nama_tendik'] ?? $data['nama_lengkap'] ?? $data['nama'] ?? '-'; ?></b></p>
            <p class="mb-1">NIP : <b><?= $data['nip'] ?? '-'; ?></b></p>
            <p class="mb-1">No HP : <b><?= $data['no_hp'] ?? '-'; ?></b></p>
         <?php elseif (str_contains($typeStr, 'guru')): ?>
            <p class="mb-1">Nama : <b><?= $data['nama_guru'] ?? $data['nama'] ?? '-'; ?></b></p>
            <p class="mb-1">NUPTK : <b><?= $data['nuptk'] ?? $data['nip'] ?? '-'; ?></b></p>
            <p class="mb-1">No HP : <b><?= $data['no_hp'] ?? '-'; ?></b></p>
         <?php endif; ?>

         <?php if (isset($presensi) && !empty($presensi)): ?>
            <div class="row mt-3 pt-2 border-top">
               <div class="col-6">
                  <p class="mb-0">Jam masuk : <span class="text-info font-weight-bold"><?= $presensi['jam_masuk'] ?? '-'; ?></span></p>
               </div>
               <div class="col-6">
                  <p class="mb-0">Jam pulang : <span class="text-info font-weight-bold"><?= $presensi['jam_keluar'] ?? '-'; ?></span></p>
               </div>
            </div>
         <?php endif; ?>
      </div>
   <?php endif; ?>
</div>