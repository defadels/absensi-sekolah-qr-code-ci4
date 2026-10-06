<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\Autoloader\FileLocator;
use CodeIgniter\Autoloader\FileLocatorCached;
use CodeIgniter\Publisher\Publisher;
use RuntimeException;
use Throwable;

class Maintenance extends BaseController
{
    public function index()
    {
        if (! $this->isSuperadmin()) {
            return $this->response->setStatusCode(403)->setBody('Akses ditolak.');
        }

        return view('admin/maintenance/index');
    }

    public function run()
    {
        if (! $this->isSuperadmin()) {
            return $this->response->setStatusCode(403)->setBody('Akses ditolak.');
        }

        $action = (string) $this->request->getPost('action');
        $confirm = (string) $this->request->getPost('confirm');

        if (! in_array($action, ['migrate', 'cache-clear', 'optimize'], true)) {
            return redirect()->to(base_url('admin/maintenance'))
                ->with('error', 'Aksi maintenance tidak dikenal.');
        }

        if (in_array($action, ['migrate', 'optimize'], true) && $confirm !== 'YA') {
            return redirect()->to(base_url('admin/maintenance'))
                ->with('error', 'Ketik YA pada kolom konfirmasi untuk menjalankan aksi ini.');
        }

        try {
            switch ($action) {
                case 'migrate':
                    $message = $this->runMigrations();
                    break;
                case 'cache-clear':
                    $message = $this->clearCache();
                    break;
                case 'optimize':
                    $message = $this->optimizeApplication();
                    break;
                default:
                    throw new RuntimeException('Aksi maintenance tidak dikenal.');
            }

            return redirect()->to(base_url('admin/maintenance'))->with('success', $message);
        } catch (Throwable $exception) {
            log_message('error', 'Maintenance action failed: {message}', [
                'message' => $exception->getMessage(),
            ]);

            return redirect()->to(base_url('admin/maintenance'))
                ->with('error', 'Aksi gagal: ' . $exception->getMessage());
        }
    }

    private function runMigrations(): string
    {
        $migrations = service('migrations');
        $migrations->setNamespace(null);
        $migrations->clearCliMessages();

        if (! $migrations->latest()) {
            throw new RuntimeException('CodeIgniter melaporkan migrasi gagal.');
        }

        return 'Migrasi database selesai. Tabel yang belum ada sudah dibuat jika tersedia di file migration.';
    }

    private function clearCache(): string
    {
        if (! service('cache')->clean()) {
            throw new RuntimeException('Cache tidak dapat dibersihkan.');
        }

        return 'Cache aplikasi berhasil dibersihkan.';
    }

    private function optimizeApplication(): string
    {
        $publisher = new Publisher(APPPATH, APPPATH);
        $optimizeConfig = APPPATH . 'Config/Optimize.php';

        if (! $publisher->replace($optimizeConfig, [
            'public bool $configCacheEnabled = false;'  => 'public bool $configCacheEnabled = true;',
            'public bool $locatorCacheEnabled = false;' => 'public bool $locatorCacheEnabled = true;',
        ])) {
            throw new RuntimeException('Config/Optimize.php tidak dapat diperbarui.');
        }

        $locator = new FileLocatorCached(new FileLocator(service('autoloader')));
        $locator->deleteCache();

        $factoryCache = WRITEPATH . 'cache/FactoriesCache_config';
        if (is_file($factoryCache) && ! unlink($factoryCache)) {
            throw new RuntimeException('Cache konfigurasi tidak dapat dibersihkan.');
        }

        return 'Optimasi cache konfigurasi dan file locator CI4 diaktifkan. Paket Composer tidak diubah.';
    }

    private function isSuperadmin(): bool
    {
        $user = auth()->user();

        return $user !== null && $user->inGroup('superadmin');
    }
}
