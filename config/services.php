<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'ranap' => [
        // Base URL tanpa kode RS & tanpa trailing slash, contoh: https://ranap.example.com/api.
        // Tiap RS punya identifier sendiri di rumah_sakit.ranap_kode_api (contoh: "rsa"), yang
        // disambung oleh RanapApiClient jadi {base_url}/{kode}/bed. RS yang kolom ranap_kode_api
        // -nya masih kosong otomatis fallback ke fixture lokal (mock_path).
        'base_url' => env('RANAP_API_BASE_URL'),
        'mock_path' => 'app/mock/ranap-ketersediaan.json',
    ],

    'antrian' => [
        // Basic Auth — kredensial global, sama untuk semua RS (base URL-nya sendiri per RS,
        // dari kolom rumah_sakit.link_antrian — lihat AntrianApiClient).
        'username' => env('ANTRIAN_API_USERNAME'),
        'password' => env('ANTRIAN_API_PASSWORD'),
    ],

];
