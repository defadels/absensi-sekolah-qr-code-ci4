<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\GuruModel;
use App\Models\KelasModel;
use App\Models\SiswaModel;
use App\Models\TendikModel;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\Label\Font\Font;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Label\Label;
use Endroid\QrCode\Logo\Logo;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\SvgWriter;
use Endroid\QrCode\Writer\WriterInterface;

class QRGenerator extends BaseController
{
   protected QrCode $qrCode;
   protected WriterInterface $writer;
   protected ?Logo $logo = null;
   protected Label $label;
   protected Font $labelFont;
   protected Color $foregroundColor;
   protected Color $foregroundColor2;
   protected Color $backgroundColor;

   protected string $qrCodeFilePath;

   const UPLOADS_PATH = FCPATH . 'uploads' . DIRECTORY_SEPARATOR;

   public function __construct()
   {
      $this->setQrCodeFilePath(self::UPLOADS_PATH);

      $this->writer = new PngWriter();

      $this->labelFont = new Font(FCPATH . 'assets/fonts/Roboto-Medium.ttf', 14);

      $this->foregroundColor = new Color(44, 73, 162);
      $this->foregroundColor2 = new Color(28, 101, 90);
      $this->backgroundColor = new Color(255, 255, 255);

      if (filter_var(env('QR_LOGO'), FILTER_VALIDATE_BOOLEAN)) {
          $settings = (new \Config\School)::$generalSettings ?? null;
          $logo = ($settings ? ($settings->logo ?? false) : false);
         if (empty($logo) || !file_exists(FCPATH . $logo)) {
            $logo = 'assets/img/logo_sekolah.jpg';
         }
         if (file_exists(FCPATH . $logo)) {
            $fileExtension = pathinfo(FCPATH . $logo, PATHINFO_EXTENSION);
            if ($fileExtension === 'svg') {
               $this->writer = new SvgWriter();
               $this->logo = Logo::create(FCPATH . $logo)
                  ->setResizeToWidth(75)
                  ->setResizeToHeight(75);
            } else {
               $this->logo = Logo::create(FCPATH . $logo)
                  ->setResizeToWidth(75);
            }
         }
      }

      $this->label = Label::create('')
         ->setFont($this->labelFont)
         ->setTextColor($this->foregroundColor);

      $this->qrCode = QrCode::create('')
         ->setEncoding(new Encoding('UTF-8'))
         ->setErrorCorrectionLevel(new ErrorCorrectionLevelHigh())
         ->setSize(300)
         ->setMargin(10)
         ->setRoundBlockSizeMode(new RoundBlockSizeModeMargin())
         ->setForegroundColor($this->foregroundColor)
         ->setBackgroundColor($this->backgroundColor);
   }

   public function setQrCodeFilePath(string $qrCodeFilePath)
   {
      $this->qrCodeFilePath = $qrCodeFilePath;
      if (!file_exists($this->qrCodeFilePath))
         mkdir($this->qrCodeFilePath, 0777, true);
   }

   public function generateQrTendik()
   {
      $this->qrCode->setForegroundColor($this->foregroundColor2);
      $this->label->setTextColor($this->foregroundColor2);

      $this->qrCodeFilePath .= 'qr-tendik/';

      if (!file_exists($this->qrCodeFilePath)) {
         mkdir($this->qrCodeFilePath, 0777, true);
      }

      try {
         $this->generate(
            unique_code: $this->request->getVar('unique_code'),
            nama: $this->request->getVar('nama'),
            nomor: $this->request->getVar('nomor')
         );
         return $this->response->setJSON(true);
      } catch (\Throwable $th) {
         log_message('error', 'QR Tendik generate failed: ' . $th->getMessage());
         return $this->response->setJSON(false);
      }
   }

   public function generateQrGuru()
   {
      $this->qrCode->setForegroundColor($this->foregroundColor2);
      $this->label->setTextColor($this->foregroundColor2);

      $this->qrCodeFilePath .= 'qr-guru/';

      if (!file_exists($this->qrCodeFilePath)) {
         mkdir($this->qrCodeFilePath, 0777, true);
      }

      try {
         $this->generate(
            unique_code: $this->request->getVar('unique_code'),
            nama: $this->request->getVar('nama'),
            nomor: $this->request->getVar('nomor')
         );
         return $this->response->setJSON(true);
      } catch (\Throwable $th) {
         log_message('error', 'QR Guru generate failed: ' . $th->getMessage());
         return $this->response->setJSON(false);
      }
   }

   public function generate($nama, $nomor, $unique_code)
   {
      $fileExt = $this->writer instanceof SvgWriter ? 'svg' : 'png';
      $filename = url_title($nama, lowercase: true) . "_" . url_title($nomor, lowercase: true) . ".$fileExt";

      $this->qrCode->setData($unique_code);
      $this->label->setText($nama);

      $this->writer
         ->write(
            qrCode: $this->qrCode,
            logo: $this->logo,
            label: $this->label
         )
         ->saveToFile(
            path: $this->qrCodeFilePath . $filename
         );

      return $this->qrCodeFilePath . $filename;
   }

   protected function serveQr(string $filePath, bool $download)
   {
      if (!$filePath || !file_exists($filePath)) {
         throw new \RuntimeException('File QR tidak ditemukan');
      }

      if ($download) {
         return $this->response->download($filePath, null, true);
      }

      $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
      $mime = $ext === 'svg' ? 'image/svg+xml' : 'image/png';

      return $this->response
         ->setHeader('Content-Type', $mime)
         ->setHeader('Content-Length', (string) filesize($filePath))
         ->setBody(file_get_contents($filePath));
   }

   protected function generateAndServe(int|string $id, string $type, bool $download)
   {
      if ($type === 'tendik') {
         $tendik = (new TendikModel())->find($id);
         if (!$tendik) {
            session()->setFlashdata(['msg' => 'Data tendik tidak ditemukan', 'error' => true]);
            return redirect()->back();
         }
         $this->qrCode->setForegroundColor($this->foregroundColor2);
         $this->label->setTextColor($this->foregroundColor2);
         $this->qrCodeFilePath .= 'qr-tendik/';
         if (!file_exists($this->qrCodeFilePath)) {
            mkdir($this->qrCodeFilePath, 0777, true);
         }
         $nama = $tendik['nama_tendik'] ?? $tendik['nama'];
         $nip = $tendik['nip'];
         $filePath = $this->generate(
            nama: $nama,
            nomor: $nip,
            unique_code: $tendik['unique_code'],
         );
      } elseif ($type === 'siswa') {
         $siswa = (new SiswaModel)->find($id);
         if (!$siswa) {
            session()->setFlashdata(['msg' => 'Siswa tidak ditemukan', 'error' => true]);
            return redirect()->back();
         }
         $kelas = $this->getKelasJurusanSlug($siswa['id_kelas']) ?? 'tmp';
         $this->qrCodeFilePath .= "qr-siswa/$kelas/";
         if (!file_exists($this->qrCodeFilePath)) {
            mkdir($this->qrCodeFilePath, 0777, true);
         }
         $filePath = $this->generate(
            nama: $siswa['nama_siswa'],
            nomor: $siswa['nis'],
            unique_code: $siswa['unique_code'],
         );
      } else {
         $guru = (new GuruModel)->find($id);
         if (!$guru) {
            session()->setFlashdata(['msg' => 'Data tidak ditemukan', 'error' => true]);
            return redirect()->back();
         }
         $this->qrCode->setForegroundColor($this->foregroundColor2);
         $this->label->setTextColor($this->foregroundColor2);
         $this->qrCodeFilePath .= 'qr-guru/';
         if (!file_exists($this->qrCodeFilePath)) {
            mkdir($this->qrCodeFilePath, 0777, true);
         }
         $filePath = $this->generate(
            nama: $guru['nama_guru'],
            nomor: $guru['nuptk'],
            unique_code: $guru['unique_code'],
         );
      }

      try {
         return $this->serveQr($filePath, $download);
      } catch (\Throwable $th) {
         session()->setFlashdata(['msg' => $th->getMessage(), 'error' => true]);
         return redirect()->back();
      }
   }

   public function downloadQrTendik($idTendik = null)
   {
      return $this->generateAndServe($idTendik, 'tendik', true);
   }

   public function viewQrTendik($idTendik = null)
   {
      return $this->generateAndServe($idTendik, 'tendik', false);
   }

   public function downloadQrGuru($idGuru = null)
   {
      return $this->generateAndServe($idGuru, 'guru', true);
   }

   public function viewQrGuru($idGuru = null)
   {
      return $this->generateAndServe($idGuru, 'guru', false);
   }

   public function downloadAllQrTendik()
   {
      $this->qrCodeFilePath .= 'qr-tendik/';

      if (!file_exists($this->qrCodeFilePath) || count(glob($this->qrCodeFilePath . '*')) === 0) {
         session()->setFlashdata([
            'msg' => 'QR Code tidak ditemukan, silakan generate terlebih dahulu',
            'error' => true
         ]);
         return redirect()->back();
      }

      try {
         $output = self::UPLOADS_PATH . DIRECTORY_SEPARATOR . 'qrcode-tendik.zip';
         $this->zipFolder($this->qrCodeFilePath, $output);
         return $this->response->download($output, null, true);
      } catch (\Throwable $th) {
         session()->setFlashdata([
            'msg' => $th->getMessage(),
            'error' => true
         ]);
         return redirect()->back();
      }
   }

   public function downloadAllQrGuru()
   {
      $this->qrCodeFilePath .= 'qr-guru/';

      if (!file_exists($this->qrCodeFilePath) || count(glob($this->qrCodeFilePath . '*')) === 0) {
         session()->setFlashdata([
            'msg' => 'QR Code tidak ditemukan, generate qr terlebih dahulu',
            'error' => true
         ]);
         return redirect()->back();
      }

      try {
         $output = self::UPLOADS_PATH . DIRECTORY_SEPARATOR . 'qrcode-guru.zip';
         $this->zipFolder($this->qrCodeFilePath, $output);
         return $this->response->download($output, null, true);
      } catch (\Throwable $th) {
         session()->setFlashdata([
            'msg' => $th->getMessage(),
            'error' => true
         ]);
         return redirect()->back();
      }
   }

   private function zipFolder(string $folder, string $output)
   {
      $zip = new \ZipArchive;
      $zip->open($output, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

      /** @var \SplFileInfo[] $files */
      $files = new \RecursiveIteratorIterator(
         new \RecursiveDirectoryIterator($folder),
         \RecursiveIteratorIterator::LEAVES_ONLY
      );

      foreach ($files as $file) {
         if (!$file->isDir()) {
            $filePath = $file->getRealPath();
            $folderLength = strlen($folder);
            if ($folder[$folderLength - 1] === DIRECTORY_SEPARATOR) {
               $relativePath = substr($filePath, $folderLength);
            } else {
               $relativePath = substr($filePath, $folderLength + 1);
            }

            $zip->addFile($filePath, $relativePath);
         }
      }
      $zip->close();
   }

   protected function getFileExtension(): string
   {
      return $this->writer instanceof SvgWriter ? 'svg' : 'png';
   }

   public function printQrTendikSingle($id)
   {
      $tendik = (new TendikModel())->find($id);
      if (!$tendik) {
         session()->setFlashdata(['msg' => 'Data tendik tidak ditemukan', 'error' => true]);
         return redirect()->back();
      }

      $this->qrCode->setForegroundColor($this->foregroundColor2);
      $this->label->setTextColor($this->foregroundColor2);
      $this->qrCodeFilePath = self::UPLOADS_PATH . 'qr-tendik/';
      if (!file_exists($this->qrCodeFilePath)) {
         mkdir($this->qrCodeFilePath, 0777, true);
      }

      $nama = $tendik['nama_tendik'] ?? $tendik['nama'];
      $nip = $tendik['nip'];

      $this->generate(
         nama: $nama,
         nomor: $nip,
         unique_code: $tendik['unique_code'],
      );

      $fileExt = $this->getFileExtension();
      $filename = url_title($nama, lowercase: true) . '_' . url_title($nip, lowercase: true) . '.' . $fileExt;
      $items = [];
      $items[] = [
         'nama' => $nama,
         'nomor' => $nip,
         'nomor_label' => 'NIP',
         'kelas' => '',
         'qr_url' => base_url('uploads/qr-tendik/' . $filename),
      ];

      $data = [
         'title' => 'Cetak QR - ' . $nama,
         'type' => 'tendik',
         'groupInfo' => $nama . ' (NIP: ' . $nip . ')',
         'items' => $items,
      ];

      return view('admin/generate-qr/print-qr', $data);
   }

   public function printQrTendik($id = null)
   {
      if ($id) {
         return $this->printQrTendikSingle($id);
      }

      $tendikList = (new TendikModel())->getAllTendik();

      $items = [];
      foreach ($tendikList as $tendik) {
         $this->qrCode->setForegroundColor($this->foregroundColor2);
         $this->label->setTextColor($this->foregroundColor2);
         $this->qrCodeFilePath = self::UPLOADS_PATH . 'qr-tendik/';
         if (!file_exists($this->qrCodeFilePath)) {
            mkdir($this->qrCodeFilePath, 0777, true);
         }

         $nama = $tendik['nama_tendik'] ?? $tendik['nama'];
         $nip = $tendik['nip'];

         $this->generate(
            nama: $nama,
            nomor: $nip,
            unique_code: $tendik['unique_code'],
         );

         $fileExt = $this->getFileExtension();
         $filename = url_title($nama, lowercase: true) . '_' . url_title($nip, lowercase: true) . '.' . $fileExt;
         $items[] = [
            'nama' => $nama,
            'nomor' => $nip,
            'nomor_label' => 'NIP',
            'kelas' => '',
            'qr_url' => base_url('uploads/qr-tendik/' . $filename),
         ];
      }

      $data = [
         'title' => 'Cetak QR Tendik',
         'type' => 'tendik',
         'groupInfo' => 'Semua Tendik - ' . count($items) . ' Tendik',
         'items' => $items,
      ];

      return view('admin/generate-qr/print-qr', $data);
   }

   public function printQrGuruSingle($id)
   {
      $guru = (new GuruModel())->find($id);
      if (!$guru) {
         session()->setFlashdata(['msg' => 'Data tidak ditemukan', 'error' => true]);
         return redirect()->back();
      }

      $this->qrCode->setForegroundColor($this->foregroundColor2);
      $this->label->setTextColor($this->foregroundColor2);
      $this->qrCodeFilePath = self::UPLOADS_PATH . 'qr-guru/';
      if (!file_exists($this->qrCodeFilePath)) {
         mkdir($this->qrCodeFilePath, 0777, true);
      }
      $this->generate(
         nama: $guru['nama_guru'],
         nomor: $guru['nuptk'],
         unique_code: $guru['unique_code'],
      );

      $fileExt = $this->getFileExtension();
      $filename = url_title($guru['nama_guru'], lowercase: true) . '_' . url_title($guru['nuptk'], lowercase: true) . '.' . $fileExt;
      $items = [];
      $items[] = [
         'nama' => $guru['nama_guru'],
         'nomor' => $guru['nuptk'],
         'nomor_label' => 'NUPTK',
         'kelas' => '',
         'qr_url' => base_url('uploads/qr-guru/' . $filename),
      ];

      $data = [
         'title' => 'Cetak QR - ' . $guru['nama_guru'],
         'type' => 'guru',
         'groupInfo' => $guru['nama_guru'] . ' (NUPTK: ' . $guru['nuptk'] . ')',
         'items' => $items,
      ];

      return view('admin/generate-qr/print-qr', $data);
   }

   public function printQrGuru()
   {
      $guruList = (new GuruModel())->getAllGuru();

      $items = [];
      foreach ($guruList as $guru) {
         $this->qrCode->setForegroundColor($this->foregroundColor2);
         $this->label->setTextColor($this->foregroundColor2);
         $this->qrCodeFilePath = self::UPLOADS_PATH . 'qr-guru/';
         if (!file_exists($this->qrCodeFilePath)) {
            mkdir($this->qrCodeFilePath, 0777, true);
         }
         $this->generate(
            nama: $guru['nama_guru'],
            nomor: $guru['nuptk'],
            unique_code: $guru['unique_code'],
         );

         $fileExt = $this->getFileExtension();
         $filename = url_title($guru['nama_guru'], lowercase: true) . '_' . url_title($guru['nuptk'], lowercase: true) . '.' . $fileExt;
         $items[] = [
            'nama' => $guru['nama_guru'],
            'nomor' => $guru['nuptk'],
            'nomor_label' => 'NUPTK',
            'kelas' => '',
            'qr_url' => base_url('uploads/qr-guru/' . $filename),
         ];
      }

      $data = [
         'title' => 'Cetak QR Guru',
         'type' => 'guru',
         'groupInfo' => 'Semua Guru - ' . count($items) . ' Guru',
         'items' => $items,
      ];

      return view('admin/generate-qr/print-qr', $data);
   }
}