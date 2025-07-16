<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    */

    // Endpoint mana saja yang diizinkan menerima request lintas origin (CORS)
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    // Method HTTP apa saja yang diizinkan dari React (GET, POST, PUT, DELETE, dll)
    'allowed_methods' => ['*'],

    // Domain mana saja yang diizinkan mengakses API Laravel
    'allowed_origins' => ['https://pusdatren.kanzussholawat.org/', 'https://kanzussholawat.org/', 'http://localhost:5173', 'http://localhost:5174'],

    // Untuk pola regex yang cocok dengan asal permintaan (biasanya kosong kalau pakai allowed_origins saja)
    'allowed_origins_patterns' => [],

    // Header apa saja yang boleh dikirim dari front-end (biasanya dikasih '*')
    'allowed_headers' => ['*'],

    // Header apa saja yang boleh dibaca dari response oleh front-end (bisa dikosongkan kalau tidak perlu expose)
    'exposed_headers' => [],

    // Berapa lama preflight request (OPTIONS) boleh di-cache dalam detik
    'max_age' => 0,

    // Mengizinkan pengiriman cookie dan credentials (harus true untuk Sanctum)
    'supports_credentials' => true,

];
