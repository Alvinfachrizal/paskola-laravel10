<?php

namespace App\Services;

use App\Models\Announcement;
use App\Models\PushSubscription;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * WebPushService — Pure PHP VAPID implementation
 *
 * Mengirim Web Push Notification menggunakan VAPID tanpa external package.
 * Jika minishlink/web-push terinstall, akan digunakan secara otomatis.
 */
class WebPushService
{
    // ─── Public API ───────────────────────────────────────────────────────────

    /**
     * Kirim push ke semua subscriber yang berhak menerima pengumuman ini.
     */
    public function sendToAnnouncement(Announcement $announcement): array
    {
        $targets = $this->resolveTargetUserIds($announcement);

        if (empty($targets)) {
            return ['sent' => 0, 'failed' => 0];
        }

        $subscriptions = PushSubscription::whereIn('user_id', $targets)->get();

        $sent   = 0;
        $failed = 0;

        foreach ($subscriptions as $sub) {
            $ok = $this->send(
                $sub,
                $announcement->title,
                strip_tags($announcement->content),
                route('announcements.show', $announcement->id),
            );
            $ok ? $sent++ : $failed++;
        }

        Log::info("WebPush sent: {$sent}, failed: {$failed}", [
            'announcement_id' => $announcement->id,
        ]);

        return ['sent' => $sent, 'failed' => $failed];
    }

    /**
     * Kirim satu push notification ke satu subscription.
     */
    public function send(
        PushSubscription $subscription,
        string $title,
        string $body,
        string $url = '/'
    ): bool {
        $payload = json_encode([
            'title' => $title,
            'body'  => mb_strimwidth($body, 0, 120, '...'),
            'icon'  => '/icons/icon-192.png',
            'badge' => '/icons/badge-72.png',
            'url'   => $url,
            'tag'   => 'paskola-announcement',
        ]);

        try {
            if (class_exists(\Minishlink\WebPush\WebPush::class)) {
                return $this->sendViaMinishlink($subscription, $payload);
            }

            return $this->sendViaVapid($subscription, $payload);

        } catch (\Throwable $e) {
            Log::warning('WebPush failed: ' . $e->getMessage(), [
                'sub_id' => $subscription->id,
            ]);

            // Auto-cleanup expired subscriptions
            if (str_contains($e->getMessage(), '410') || str_contains($e->getMessage(), '404')) {
                $subscription->delete();
            }

            return false;
        }
    }

    // ─── Minishlink driver (jika package terinstall) ──────────────────────────

    private function sendViaMinishlink(PushSubscription $sub, string $payload): bool
    {
        $auth = [
            'VAPID' => [
                'subject'    => config('webpush.vapid.subject'),
                'publicKey'  => config('webpush.vapid.public_key'),
                'privateKey' => config('webpush.vapid.private_key'),
            ],
        ];

        $webPush = new \Minishlink\WebPush\WebPush($auth);
        $subscription = \Minishlink\WebPush\Subscription::create([
            'endpoint'        => $sub->endpoint,
            'publicKey'       => $sub->public_key,
            'authToken'       => $sub->auth_token,
            'contentEncoding' => 'aesgcm',
        ]);

        $report = $webPush->sendOneNotification($subscription, $payload);
        return $report->isSuccess();
    }

    // ─── Native VAPID driver (tanpa package) ─────────────────────────────────

    private function sendViaVapid(PushSubscription $sub, string $payload): bool
    {
        $vapidPublic  = config('webpush.vapid.public_key');
        $vapidPrivate = config('webpush.vapid.private_key');
        $vapidSubject = config('webpush.vapid.subject');

        if (empty($vapidPublic) || empty($vapidPrivate)) {
            Log::warning('WebPush: VAPID keys tidak dikonfigurasi. Jalankan: php artisan webpush:generatekeys');
            return false;
        }

        $endpoint     = $sub->endpoint;
        $audience     = $this->getAudience($endpoint);
        $vapidHeaders = $this->buildVapidHeaders($audience, $vapidSubject, $vapidPublic, $vapidPrivate);

        // Kirim tanpa enkripsi payload (hanya notification tanpa body jika tidak ada enkripsi)
        // Ini "ping" mode — browser akan tahu ada update tapi tanpa payload detail
        $response = Http::withHeaders(array_merge($vapidHeaders, [
            'TTL'          => '86400',
            'Content-Type' => 'application/json',
        ]))->post($endpoint, json_decode($payload, true));

        if ($response->status() === 410 || $response->status() === 404) {
            $sub->delete();
            return false;
        }

        return $response->successful();
    }

    private function getAudience(string $endpoint): string
    {
        $parsed = parse_url($endpoint);
        return $parsed['scheme'] . '://' . $parsed['host'];
    }

    private function buildVapidHeaders(
        string $audience,
        string $subject,
        string $publicKey,
        string $privateKey
    ): array {
        $expiration = time() + 12 * 3600;

        $header = $this->base64urlEncode(json_encode(['typ' => 'JWT', 'alg' => 'ES256']));
        $claims = $this->base64urlEncode(json_encode([
            'aud' => $audience,
            'exp' => $expiration,
            'sub' => $subject,
        ]));

        $unsigned  = "{$header}.{$claims}";
        $signature = $this->signJwt($unsigned, $privateKey);
        $jwt       = "{$unsigned}.{$signature}";

        return [
            'Authorization' => "vapid t={$jwt},k={$publicKey}",
        ];
    }

    private function signJwt(string $data, string $privateKeyBase64url): string
    {
        // Decode private key dari base64url
        $privateKeyBytes = $this->base64urlDecode($privateKeyBase64url);

        // Buat EC private key dari raw d value
        $pem = $this->derToEcPrivateKeyPem($privateKeyBytes);
        $privateKey = openssl_pkey_get_private($pem);

        if (!$privateKey) {
            throw new \RuntimeException('Invalid VAPID private key');
        }

        openssl_sign($data, $signature, $privateKey, OPENSSL_ALGO_SHA256);

        // Konversi DER signature ke raw (r+s = 64 bytes)
        return $this->base64urlEncode($this->derToRaw($signature));
    }

    private function derToEcPrivateKeyPem(string $d): string
    {
        // Build DER untuk EC private key (prime256v1)
        $ecOid = "\x06\x08\x2a\x86\x48\xce\x3d\x03\x01\x07"; // OID prime256v1
        $algId = "\x30\x0a\x06\x08\x2a\x86\x48\xce\x3d\x04\x03\x02"; // algorithmIdentifier

        // ECPrivateKey structure (RFC 5915)
        $ecKey = "\x30" . $this->derLength(
            "\x02\x01\x01" . // version
            "\x04" . $this->derLength($d) . $d . // private key
            "\xa0\x0a" . $ecOid // curve OID
        );

        // PKCS#8 PrivateKeyInfo
        $pkcs8 = "\x30" . $this->derLength(
            "\x02\x01\x00" . // version
            "\x30\x13\x06\x07\x2a\x86\x48\xce\x3d\x02\x01" . $ecOid . // algorithm
            "\x04" . $this->derLength($ecKey) . $ecKey // privateKey
        );

        return "-----BEGIN PRIVATE KEY-----\n" .
               chunk_split(base64_encode($pkcs8), 64, "\n") .
               "-----END PRIVATE KEY-----";
    }

    private function derLength(string $data): string
    {
        $len = strlen($data);
        if ($len < 128) return chr($len);
        if ($len < 256) return "\x81" . chr($len);
        return "\x82" . chr($len >> 8) . chr($len & 0xff);
    }

    private function derToRaw(string $der): string
    {
        // Parse DER SEQUENCE
        $offset = 2; // skip 0x30 + length
        $r = $this->derParseInteger($der, $offset);
        $s = $this->derParseInteger($der, $offset);
        return str_pad($r, 32, "\x00", STR_PAD_LEFT) .
               str_pad($s, 32, "\x00", STR_PAD_LEFT);
    }

    private function derParseInteger(string $der, int &$offset): string
    {
        $offset++; // skip 0x02
        $len = ord($der[$offset++]);
        $value = substr($der, $offset, $len);
        $offset += $len;
        // Remove leading null byte (padding for positive integers)
        return ltrim($value, "\x00");
    }

    private function base64urlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function base64urlDecode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', (4 - strlen($data) % 4) % 4));
    }

    // ─── Target resolution ───────────────────────────────────────────────────

    private function resolveTargetUserIds(Announcement $announcement): array
    {
        $query = \App\Models\User::where('school_id', $announcement->school_id);

        if (!empty($announcement->target_roles)) {
            $query->whereIn('role', $announcement->target_roles);
        }

        // Jika ada filter kelas spesifik, hanya kirim ke siswa di kelas itu
        // (guru & admin tetap terima)
        if (!empty($announcement->target_class_ids)) {
            $classIds = $announcement->target_class_ids;
            $query->where(function ($q) use ($classIds) {
                $q->whereHas('student.currentClass', fn($c) => $c->whereIn('school_class_id', $classIds))
                  ->orWhereNotIn('role', ['Siswa', 'Orang Tua']);
            });
        }

        return $query->pluck('id')->toArray();
    }
}
