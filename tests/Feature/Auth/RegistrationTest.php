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
            'nik' => '1234567890123456',
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
