<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\GuruModel;
use App\Models\TendikModel;

class GenerateQR extends BaseController
{
   protected GuruModel $guruModel;
   protected TendikModel $tendikModel;

   public function __construct()
   {
      $this->guruModel = new GuruModel();
      $this->tendikModel = new TendikModel();
   }

   public function index()
   {
      if (function_exists('can_view_report') && !can_view_report()) {
         return redirect()->to('admin');
      }

      $guru   = $this->guruModel->getAllGuru();
      $tendik = $this->tendikModel->getAllTendik();

      $data = [
         'title'  => 'Generate QR Code',
         'ctx'    => 'admin-qr',
         'guru'   => $guru,
         'tendik' => $tendik
      ];

      return view('admin/generate-qr/generate-qr', $data);
   }
}