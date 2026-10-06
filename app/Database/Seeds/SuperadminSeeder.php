<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\Shield\Entities\User;
use RuntimeException;

class SuperadminSeeder extends Seeder
{
    public function run()
    {
        // Never create a production admin with predictable credentials.
        $email = trim((string) env('SUPERADMIN_SEED_EMAIL', ''));
        $username = trim((string) env('SUPERADMIN_SEED_USERNAME', ''));
        $password = (string) env('SUPERADMIN_SEED_PASSWORD', '');

        if (! filter_var($email, FILTER_VALIDATE_EMAIL) || $username === '' || strlen($password) < 12) {
            throw new RuntimeException(
                'Set SUPERADMIN_SEED_EMAIL, SUPERADMIN_SEED_USERNAME, and a SUPERADMIN_SEED_PASSWORD of at least 12 characters in .env.'
            );
        }

        $userProvider = auth()->getProvider();

        // Check if superadmin already exists by email or username
        $existingSuperadmin = $userProvider->findByCredentials(['email' => $email]);

        if (!$existingSuperadmin) {
            // Also check by username
            $existingSuperadmin = $userProvider->where('username', $username)->first();
        }

        if (!$existingSuperadmin) {
            // Create user entity
            $user = new User([
                'username' => $username,
                'email'    => $email,
                'password' => $password,
            ]);

            $user->active = 1;

            // Save user
            $userProvider->save($user);

            // Get the user again to add to groups
            $user = $userProvider->findById($userProvider->getInsertID());

            // Superadmin gets: superadmin (primary), admin (convenience)
            $user->addGroup('superadmin', 'admin');

            log_message('info', 'Initial superadmin account created for {email}.', ['email' => $email]);
        } else {
            log_message('info', 'Superadmin seeder skipped because the account already exists.');
        }
    }
}
