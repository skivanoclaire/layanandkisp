<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Menjaga isolasi database test.
 *
 * Test dengan RefreshDatabase menjalankan migrate:fresh, sehingga database yang
 * ditunjuk akan dikosongkan. Bila konfigurasi terlanjur mengarah ke database
 * development `laravel` (environment variable dari docker-compose.yml menimpa
 * phpunit.xml), seluruh isinya akan terhapus — ini pernah terjadi.
 *
 * Test ini sengaja tidak memuat aplikasi Laravel: ia memeriksa environment mentah
 * yang disiapkan tests/bootstrap.php.
 */
class TestEnvironmentTest extends TestCase
{
    public function test_database_test_terpisah_dari_database_development(): void
    {
        $this->assertSame('laravel_test', getenv('DB_DATABASE'));
        $this->assertNotSame('laravel', getenv('DB_DATABASE'));

        // Laravel membaca $_ENV/$_SERVER, bukan hanya getenv().
        $this->assertSame('laravel_test', $_ENV['DB_DATABASE'] ?? null);
        $this->assertSame('laravel_test', $_SERVER['DB_DATABASE'] ?? null);
    }

    public function test_app_env_adalah_testing(): void
    {
        $this->assertSame('testing', getenv('APP_ENV'));
    }
}
