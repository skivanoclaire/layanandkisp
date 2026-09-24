<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Rule 'recaptcha' memanggil Google lewat HTTP (lihat AppServiceProvider),
        // dan view register memanggil widget-nya. Keduanya diganti implementasi palsu
        // agar test tidak bergantung pada jaringan.
        $this->app->bind('nocaptcha', fn () => new class
        {
            public function verifyResponse($response, $clientIp = null): bool
            {
                return $response === 'recaptcha-valid';
            }

            /** Widget hanya perlu merender sesuatu; isinya tidak diuji. */
            public function display($attributes = [], $options = []): string
            {
                return '<div class="g-recaptcha"></div>';
            }

            /** Metode lain dari paket no-captcha (mis. displaySubmit, renderJs). */
            public function __call($name, $arguments): string
            {
                return '';
            }
        });
    }

    /**
     * @return array<string, string>
     */
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Test User',
            'nik' => '6503015201900001',
            'phone' => '081234567890',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'g-recaptcha-response' => 'recaptcha-valid',
        ], $overrides);
    }

    public function test_registration_screen_can_be_rendered(): void
    {
        $this->get('/register')->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', $this->validPayload());

        $this->assertAuthenticated();
        $response->assertRedirect(route('user.dashboard', absolute: false));
        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
    }

    public function test_nik_harus_16_digit(): void
    {
        $this->post('/register', $this->validPayload(['nik' => '123']))
            ->assertSessionHasErrors('nik');

        $this->assertGuest();
    }

    /**
     * NIP PNS 18 digit dulu diterima karena maxlength="16" memotongnya diam-diam
     * dan validasi hanya menghitung digit. Keduanya harus ditolak sekarang.
     *
     * @dataProvider nipProvider
     */
    public function test_nik_tidak_boleh_diisi_nip(string $nip): void
    {
        $this->post('/register', $this->validPayload(['nik' => $nip]))
            ->assertSessionHasErrors('nik');

        $this->assertGuest();
    }

    /** @return array<string, array<string>> */
    public static function nipProvider(): array
    {
        return [
            'NIP 18 digit'                => ['198501012010011001'],
            'NIP terpotong jadi 16 digit' => ['1985010120100110'],
            'NIP generasi 2000-an'        => ['200001012024011001'],
        ];
    }

    /**
     * @dataProvider nikTidakValidProvider
     */
    public function test_nik_harus_sesuai_struktur(string $nik): void
    {
        $this->post('/register', $this->validPayload(['nik' => $nik]))
            ->assertSessionHasErrors('nik');

        $this->assertGuest();
    }

    /** @return array<string, array<string>> */
    public static function nikTidakValidProvider(): array
    {
        return [
            'kode provinsi tidak dikenal' => ['9903015201900001'],
            'bulan lahir 90'              => ['1234567890123456'],
            'tanggal lahir 99'            => ['6503019901900001'],
            'nomor urut 0000'             => ['6503015201900000'],
            'semua digit sama'            => ['1111111111111111'],
            'mengandung huruf'            => ['650301520190000A'],
        ];
    }

    public function test_nik_valid_diterima(): void
    {
        $this->post('/register', $this->validPayload(['nik' => '3201014503950002']))
            ->assertSessionHasNoErrors();

        $this->assertAuthenticated();
    }

    public function test_nik_tidak_boleh_ganda(): void
    {
        $this->post('/register', $this->validPayload())->assertRedirect();
        $this->post('/logout');

        // NIK sama, email & phone berbeda — duplikat dideteksi lewat nik_hash.
        $this->post('/register', $this->validPayload([
            'email' => 'lain@example.com',
            'phone' => '081299998888',
        ]))->assertSessionHasErrors('nik');
    }

    public function test_registrasi_ditolak_bila_recaptcha_tidak_valid(): void
    {
        $this->post('/register', $this->validPayload(['g-recaptcha-response' => 'salah']))
            ->assertSessionHasErrors('g-recaptcha-response');

        $this->assertGuest();
    }
}
