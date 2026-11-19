<?php

return [

    /*
    | Alamat Portal SSO dan identitas aplikasi ini (menu Aplikasi → Dokumentasi di portal).
    */
    'portal_url' => env('SSO_PORTAL_URL', 'https://portal.kaltaraprov.go.id'),

    'application_id' => env('SSO_PORTAL_APPLICATION_ID'),

    'api_key' => env('SSO_PORTAL_API_KEY'),

    // Batas waktu panggilan API ke portal (detik)
    'timeout' => (int) env('SSO_PORTAL_TIMEOUT', 10),

    /*
    | Rute bawaan paket:
    |   GET  /sso/login     → arahkan ke portal
    |   POST /sso/callback  → isi sebagai Callback URL di portal
    |   GET  /sso/complete  → langkah kedua callback (internal)
    |   POST /sso/logout    → akhiri sesi aplikasi
    */
    'routes' => [
        'enabled' => true,
        'prefix' => 'sso',
        'middleware' => ['web'],
    ],

    'redirect_after_login' => env('SSO_PORTAL_REDIRECT', '/dashboard'),

    // null = kembali ke portal
    'redirect_after_logout' => env('SSO_PORTAL_LOGOUT_REDIRECT'),

    /*
    | Cara membuat sesi login di aplikasi:
    |   session  → data pengguna SSO disimpan di sesi saja (tanpa tabel users).
    |              Pakai middleware 'sso.auth' dan facade SsoPortal::user().
    |   eloquent → pengguna SSO dicocokkan ke model User aplikasi lalu Auth::login(),
    |              sehingga auth()->user(), middleware 'auth', policy, dll. tetap jalan.
    */
    'login' => [
        'mode' => env('SSO_PORTAL_LOGIN_MODE', 'eloquent'),

        'guard' => null, // null = guard default

        // Pencocokan NIP/NIK/email dan role lokal E-Layanan (lihat App\Services\Sso\PortalSsoUserResolver)
        'resolver' => \App\Services\Sso\PortalSsoUserResolver::class,

        'model' => 'App\\Models\\User',

        // kolom tabel => field SSO; dicoba berurutan, yang pertama cocok dipakai.
        // Field SSO: id, username, email, full_name, nip, nik, realm, role_code
        'match' => [
            'email' => 'email',
        ],

        // Realm yang boleh dicocokkan ke pengguna yang sudah ada lewat 'match'. Email akun
        // masyarakat (public-kaltara) tidak diverifikasi portal: bila realm itu dicocokkan
        // lewat email, siapa pun bisa mendaftar dengan email admin aplikasi lalu masuk
        // sebagai admin. Pengguna realm lain hanya bisa masuk lewat auto_create.
        'match_realms' => ['asn-kaltara'],

        // Buat pengguna baru bila tidak ada yang cocok. Bila false, pengguna yang belum
        // terdaftar di aplikasi ditolak dengan pesan yang jelas.
        'auto_create' => false,

        // kolom tabel => field SSO, untuk pengguna baru
        'create_attributes' => [
            'name' => 'full_name',
            'email' => 'email',
        ],

        // Isi kolom 'password' dengan nilai acak untuk pengguna baru (login hanya lewat SSO)
        'random_password' => true,

        // kolom tabel => field SSO, diperbarui setiap login
        'sync_attributes' => [],
    ],

    /*
    | Pembatasan akses tambahan di sisi aplikasi.
    */
    // Realm yang boleh masuk: 'asn-kaltara', 'public-kaltara'. Kosong = semua.
    'allowed_realms' => ['asn-kaltara', 'public-kaltara'],

    // Tolak pengguna tanpa role aplikasi (aplikasi publik mengirim role = null)
    'require_role' => false,
];
