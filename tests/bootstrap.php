<?php

/**
 * Bootstrap khusus test.
 *
 * Test berjalan di MySQL (engine yang sama dengan produksi), tetapi pada database
 * TERPISAH: `laravel_test`. Ini disengaja — SQLite tidak dipakai karena sebagian
 * migration memakai operasi yang hanya didukung MySQL (mis. drop kolom yang punya
 * index di 2025_10_28_134849_update_unit_kerjas_kategori_to_tipe).
 *
 * docker-compose.yml menyetel DB_* sebagai environment variable container, dan nilai
 * itu lebih "kuat" daripada <env> di phpunit.xml. Tanpa penimpaan di sini, test pernah
 * berjalan di database development `laravel` dan RefreshDatabase (migrate:fresh)
 * menghapus seluruh isinya. Penimpaan dilakukan sebelum autoloader dimuat, sehingga
 * tidak ada kode yang sempat membaca konfigurasi database asli.
 */
const TEST_DATABASE = 'laravel_test';

$testEnvironment = [
    'APP_ENV' => 'testing',
    'DB_CONNECTION' => 'mysql',
    'DB_DATABASE' => TEST_DATABASE,
];

foreach ($testEnvironment as $key => $value) {
    putenv("{$key}={$value}");
    $_ENV[$key] = $value;
    $_SERVER[$key] = $value;
}

require __DIR__.'/../vendor/autoload.php';

// Buat database test bila belum ada, supaya `php artisan test` langsung jalan
// di environment baru tanpa langkah manual.
$host = getenv('DB_HOST') ?: '127.0.0.1';
$port = getenv('DB_PORT') ?: '3306';
$username = getenv('DB_USERNAME') ?: 'root';
$password = getenv('DB_PASSWORD') ?: '';

try {
    $pdo = new PDO("mysql:host={$host};port={$port}", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    $pdo->exec(
        'CREATE DATABASE IF NOT EXISTS `'.TEST_DATABASE.'` '
        .'DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'
    );
} catch (PDOException $e) {
    fwrite(STDERR, "Tidak dapat menyiapkan database test '".TEST_DATABASE."': ".$e->getMessage()."\n");
    fwrite(STDERR, "Buat manual: CREATE DATABASE ".TEST_DATABASE."; lalu beri hak akses ke user DB_USERNAME.\n");
    exit(1);
}
