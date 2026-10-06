<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\Database\Seeder;
use Config\Database;
use RuntimeException;
use Throwable;

/**
 * Temporary, key-protected deployment endpoints for database setup.
 * This intentionally bypasses the app BaseController, which loads school
 * settings during initialization and can fail while tables are missing.
 */
class DeployMigrate extends Controller
{
    public function index()
    {
        if ($response = $this->preflight()) {
            return $response;
        }

        if (strtolower($this->request->getMethod()) === 'get') {
            return $this->renderForm(
                'Jalankan Migrasi Database',
                'Menjalankan migration CI4 yang belum diterapkan. Backup database sebelum melanjutkan.',
                'YA'
            );
        }

        if ($response = $this->authorizePost('YA')) {
            return $response;
        }

        try {
            $migrations = service('migrations');
            $migrations->setNamespace(null);
            $migrations->clearCliMessages();

            if (! $migrations->latest()) {
                throw new RuntimeException('CodeIgniter melaporkan migration gagal.');
            }

            // Repair a missing Settings table if the migrations history says
            // it already ran (for example, after an incomplete DB import).
            $settingsRestored = $this->restoreMissingSettingsTable();
            $message = 'Berhasil. Semua migration yang tersedia sudah dijalankan.';
            if ($settingsRestored) {
                $message .= ' Tabel settings yang hilang juga dibuat kembali.';
            }
            $message .= ' Hapus endpoint deployment setelah selesai.';

            return $this->response->setContentType('text/plain')->setBody($message);
        } catch (Throwable $exception) {
            return $this->failureResponse('Deployment migration failed', $exception);
        }
    }

    public function seedSuperadmin()
    {
        if ($response = $this->preflight()) {
            return $response;
        }

        if (strtolower($this->request->getMethod()) === 'get') {
            return $this->renderForm(
                'Buat Akun Superadmin',
                'Hanya menjalankan SuperadminSeeder. Isi kredensial akun awal di .env sebelum melanjutkan.',
                'SEED SUPERADMIN'
            );
        }

        if ($response = $this->authorizePost('SEED SUPERADMIN')) {
            return $response;
        }

        try {
            $seeder = new Seeder(new Database());
            $seeder->call('SuperadminSeeder');

            return $this->response->setContentType('text/plain')->setBody(
                'SuperadminSeeder selesai. Jika email atau username sudah terdaftar, seeder akan melewatinya. Hapus endpoint deployment setelah selesai.'
            );
        } catch (Throwable $exception) {
            return $this->failureResponse('Superadmin seeding failed', $exception);
        }
    }

    private function preflight()
    {
        $key = trim((string) env('DEPLOY_ACTION_KEY', ''));
        if (strlen($key) < 32) {
            return $this->response->setStatusCode(503)
                ->setContentType('text/plain')
                ->setBody('Isi DEPLOY_ACTION_KEY di .env dengan kunci acak minimal 32 karakter.');
        }

        if (! $this->request->isSecure()) {
            return $this->response->setStatusCode(400)
                ->setContentType('text/plain')
                ->setBody('Buka endpoint ini melalui HTTPS setelah SSL domain aktif.');
        }

        return null;
    }

    private function renderForm(string $title, string $description, string $confirmation): \CodeIgniter\HTTP\ResponseInterface
    {
        helper('form');
        $safeTitle = esc($title);
        $safeDescription = esc($description);
        $safeConfirmation = esc($confirmation);

        $html = '<!doctype html><html lang="id"><head><meta charset="utf-8">'
            . '<meta name="viewport" content="width=device-width, initial-scale=1">'
            . '<title>' . $safeTitle . '</title></head>'
            . '<body style="font:16px sans-serif;max-width:560px;margin:48px auto;padding:0 16px">'
            . '<h1>' . $safeTitle . '</h1><p>' . $safeDescription . '</p>'
            . '<form method="post">' . csrf_field()
            . '<p><label>Kunci deployment dari .env<br><input type="password" name="key" required autocomplete="off" style="width:100%;padding:10px"></label></p>'
            . '<p><label>Ketik ' . $safeConfirmation . ' untuk konfirmasi<br><input type="text" name="confirm" required autocomplete="off" style="width:100%;padding:10px"></label></p>'
            . '<button type="submit" style="padding:10px 16px">Jalankan</button>'
            . '</form></body></html>';

        return $this->response->setContentType('text/html')->setBody($html);
    }

    private function authorizePost(string $confirmation)
    {
        $expectedKey = trim((string) env('DEPLOY_ACTION_KEY', ''));
        $providedKey = (string) $this->request->getPost('key');

        if ($providedKey === '' || ! hash_equals($expectedKey, $providedKey)) {
            return $this->response->setStatusCode(403)
                ->setContentType('text/plain')
                ->setBody('Kunci deployment tidak valid.');
        }

        if ((string) $this->request->getPost('confirm') !== $confirmation) {
            return $this->response->setStatusCode(400)
                ->setContentType('text/plain')
                ->setBody('Konfirmasi tidak valid.');
        }

        return null;
    }

    private function restoreMissingSettingsTable(): bool
    {
        $settings = config('Settings');
        $group = $settings->database['group'] ?? null;
        $table = $settings->database['table'] ?? 'settings';
        $db = db_connect($group);

        if ($db->tableExists($table)) {
            return false;
        }

        $forge = Database::forge($group);
        $forge->addField('id');
        $forge->addField([
            'class' => ['type' => 'varchar', 'constraint' => 255],
            'key' => ['type' => 'varchar', 'constraint' => 255],
            'value' => ['type' => 'text', 'null' => true],
            'type' => ['type' => 'varchar', 'constraint' => 31, 'default' => 'string'],
            'context' => ['type' => 'varchar', 'constraint' => 255, 'null' => true],
            'created_at' => ['type' => 'datetime', 'null' => false],
            'updated_at' => ['type' => 'datetime', 'null' => false],
        ]);
        $forge->createTable($table, true);

        if (! $db->tableExists($table)) {
            throw new RuntimeException('Tabel settings tidak berhasil dibuat.');
        }

        return true;
    }

    private function failureResponse(string $logPrefix, Throwable $exception): \CodeIgniter\HTTP\ResponseInterface
    {
        log_message('error', $logPrefix . ': {message}', [
            'message' => $exception->getMessage(),
        ]);

        return $this->response->setStatusCode(500)
            ->setContentType('text/plain')
            ->setBody('Aksi gagal: ' . $exception->getMessage());
    }
}
