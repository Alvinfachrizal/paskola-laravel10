<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Generate VAPID key pair untuk Web Push tanpa external package.
 * Menggunakan OpenSSL yang sudah ada di PHP.
 *
 * Usage: php artisan webpush:generatekeys
 */
class GenerateVapidKeys extends Command
{
    protected $signature   = 'webpush:generatekeys';
    protected $description = 'Generate VAPID key pair untuk Web Push Notification';

    public function handle(): int
    {
        if (!function_exists('openssl_pkey_new')) {
            $this->error('OpenSSL extension tidak tersedia. Aktifkan ext-openssl di php.ini.');
            return self::FAILURE;
        }

        // Generate EC P-256 key pair
        $key = openssl_pkey_new([
            'curve_name'       => 'prime256v1',
            'private_key_type' => OPENSSL_KEYTYPE_EC,
        ]);

        if (!$key) {
            $this->error('Gagal generate key: ' . openssl_error_string());
            return self::FAILURE;
        }

        $details = openssl_pkey_get_details($key);

        // Public key dalam format uncompressed (04 + x + y) → base64url
        $publicKey = $this->base64url(
            "\x04" . str_pad($details['ec']['x'], 32, "\x00", STR_PAD_LEFT)
                   . str_pad($details['ec']['y'], 32, "\x00", STR_PAD_LEFT)
        );

        // Private key (d value) → base64url
        $privateKey = $this->base64url(
            str_pad($details['ec']['d'], 32, "\x00", STR_PAD_LEFT)
        );

        $this->info('✅ VAPID Keys berhasil digenerate!');
        $this->newLine();
        $this->line('Tambahkan ke file <comment>.env</comment>:');
        $this->newLine();
        $this->line("VAPID_PUBLIC_KEY={$publicKey}");
        $this->line("VAPID_PRIVATE_KEY={$privateKey}");
        $this->newLine();

        // Update .env otomatis
        $envPath = base_path('.env');
        $env     = file_get_contents($envPath);

        $env = preg_replace('/^VAPID_PUBLIC_KEY=.*$/m',  "VAPID_PUBLIC_KEY={$publicKey}", $env);
        $env = preg_replace('/^VAPID_PRIVATE_KEY=.*$/m', "VAPID_PRIVATE_KEY={$privateKey}", $env);

        file_put_contents($envPath, $env);

        $this->info('.env berhasil diperbarui!');

        return self::SUCCESS;
    }

    private function base64url(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}

