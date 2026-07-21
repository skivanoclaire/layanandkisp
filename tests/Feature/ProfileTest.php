<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Catatan: aplikasi ini menyimpang dari scaffolding Laravel Breeze —
 * `profile.update` memakai PUT (bukan PATCH), dan fitur hapus akun sendiri
 * tidak tersedia (tidak ada route `profile.destroy`). Test di sini mengikuti
 * perilaku aplikasi, bukan perilaku bawaan Breeze.
 */
class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/profile')
            ->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put('/profile', [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'phone' => '081234567890',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('profile.edit'));

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertSame('081234567890', $user->phone);
    }

    /**
     * Berbeda dari Breeze: aplikasi ini tidak mereset status verifikasi email
     * ketika alamat email diubah.
     */
    public function test_status_verifikasi_email_tidak_direset_saat_email_diubah(): void
    {
        $user = User::factory()->create();
        $this->assertNotNull($user->email_verified_at);

        $this->actingAs($user)
            ->put('/profile', [
                'name' => 'Test User',
                'email' => 'email-baru@example.com',
                'phone' => '081234567890',
            ])
            ->assertSessionHasNoErrors();

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_nama_dan_email_wajib_diisi(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from('/profile')
            ->put('/profile', ['name' => '', 'email' => 'bukan-email'])
            ->assertSessionHasErrors(['name', 'email']);
    }

    /**
     * Fitur hapus akun sendiri tidak tersedia: `ProfileController::destroy()` ada,
     * tetapi tidak ada route yang mengarah ke sana.
     */
    public function test_hapus_akun_sendiri_tidak_tersedia(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->delete('/profile', ['password' => 'password'])
            ->assertMethodNotAllowed();

        $this->assertNotNull($user->fresh());
    }
}
