<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Nama database yang boleh dipakai test. Apa pun selain ini ditolak — terutama
     * database development `laravel`, yang isinya pernah terhapus karena test.
     */
    private const ALLOWED_TEST_DATABASE = 'laravel_test';

    /**
     * Pengaman: test dengan RefreshDatabase menjalankan migrate:fresh, sehingga
     * database yang ditunjuk akan DIKOSONGKAN. Pemeriksaan wajib dilakukan SEBELUM
     * parent::setUp(), karena RefreshDatabase bermigrasi dari dalam parent::setUp() —
     * memeriksa setelahnya sudah terlambat.
     */
    protected function setUp(): void
    {
        $database = env('DB_DATABASE');

        if ($database !== self::ALLOWED_TEST_DATABASE) {
            $this->fail(
                "Test dihentikan demi keamanan: DB_DATABASE bernilai '{$database}', "
                ."bukan '".self::ALLOWED_TEST_DATABASE."'. Menjalankan test pada database ini "
                .'akan menghapus isinya (migrate:fresh). Periksa tests/bootstrap.php dan '
                .'environment variable DB_* pada container.'
            );
        }

        parent::setUp();
    }
}
