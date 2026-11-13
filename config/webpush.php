<?php

return [
    /*
    |--------------------------------------------------------------------------
    | VAPID Keys (Voluntary Application Server Identification)
    |--------------------------------------------------------------------------
    | Generate via: php artisan webpush:generatekeys
    | Lalu copy ke .env:
    |   VAPID_PUBLIC_KEY=...
    |   VAPID_PRIVATE_KEY=...
    |   VAPID_SUBJECT=mailto:admin@sekolah.sch.id
    */
    'vapid' => [
        'subject'     => env('VAPID_SUBJECT', 'mailto:admin@paskola.sch.id'),
        'public_key'  => env('VAPID_PUBLIC_KEY', ''),
        'private_key' => env('VAPID_PRIVATE_KEY', ''),
    ],
];

