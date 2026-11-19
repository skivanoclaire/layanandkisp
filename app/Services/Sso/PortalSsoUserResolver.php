<?php

namespace App\Services\Sso;

use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Kaltaraprov\SsoPortal\Contracts\UserResolver;
use Kaltaraprov\SsoPortal\SsoUser;

/**
 * Mencocokkan pengguna dari Portal SSO ke akun lokal E-Layanan.
 *
 * Urutan pencocokan sama dengan aturan lama (SsoController::resolveSsoUser):
 * NIP, lalu NIK (hash), lalu email. Email hanya dipakai untuk realm ASN karena
 * email akun masyarakat belum diverifikasi portal.
 */
class PortalSsoUserResolver implements UserResolver
{
    public function resolve(SsoUser $sso): ?Authenticatable
    {
        $nip = preg_replace('/\s+/', '', (string) $sso->nip);
        $nik = $sso->nik ? (string) $sso->nik : null;
        $email = $sso->email ? (string) $sso->email : null;
        $name = $sso->full_name ?: null;

        $user = null;
        $matchedBy = null;

        if ($nip !== '') {
            $user = User::where('nip', $nip)->orderBy('is_sso_user')->orderBy('id')->first();
            $matchedBy = 'nip';
        }

        if (! $user && $nik) {
            $nikHash = User::hashNik($nik);
            if ($nikHash) {
                $user = User::where('nik_hash', $nikHash)->orderBy('is_sso_user')->orderBy('id')->first();
                $matchedBy = 'nik';
            }
        }

        if (! $user && $email && $sso->isAsn()) {
            $user = User::where('email', $email)->first();
            $matchedBy = 'email';
        }

        if (! $user) {
            if ($nip === '' && ! $email) {
                return null;
            }

            $user = User::create([
                'nip' => $nip !== '' ? $nip : null,
                'nik' => $nik,
                'phone' => '-',
                'email' => $this->emailUnik($email, $nip),
                'name' => $name,
                'password' => bcrypt(Str::random(32)),
                'is_verified' => true,
                'is_sso_user' => true,
                'verified_at' => now(),
                'verified_by' => 'SSO System',
            ]);

            $this->attachRole($user, $sso, true);

            Log::info('New user created via SSO Portal', ['user_id' => $user->id, 'nip' => $nip]);

            return $user;
        }

        // Kolom yang sudah terisi tidak ditimpa; hanya melengkapi yang kosong.
        $update = [];
        if ($nip !== '' && $user->nip !== $nip && ! $this->nipDipakaiAkunLain($nip, $user->id)) {
            $update['nip'] = $nip;
        }
        if ($nik) {
            $update['nik'] = $nik;
        }
        if ($name && blank($user->name)) {
            $update['name'] = $name;
        }
        if ($update) {
            $user->update($update);
        }

        $this->attachRole($user, $sso, false);

        Log::info('User logged in via SSO Portal', [
            'user_id' => $user->id,
            'matched_by' => $matchedBy,
            'email_differs' => $email !== null && strcasecmp((string) $user->email, $email) !== 0,
        ]);

        return $user;
    }

    /**
     * Kolom email akun lokal unik dan wajib; untuk pengguna baru tanpa email, buat nilai pengganti.
     */
    protected function emailUnik(?string $email, string $nip): string
    {
        if ($email && ! User::where('email', $email)->exists()) {
            return $email;
        }

        return 'sso-'.($nip !== '' ? $nip : Str::random(8)).'@sso.invalid';
    }

    protected function nipDipakaiAkunLain(string $nip, int $userId): bool
    {
        return User::where('nip', $nip)->where('id', '!=', $userId)->exists();
    }

    /**
     * Role lokal: pengguna baru mendapat role "User"; role dari portal ditambahkan bila dikenali.
     */
    protected function attachRole(User $user, SsoUser $sso, bool $baru): void
    {
        if ($baru && ($default = Role::where('name', 'User')->first())) {
            $user->roles()->syncWithoutDetaching([$default->id]);
        }

        $kodeRole = $sso->roleCode();
        $roleLokal = $kodeRole !== null ? config('sso.role_map.'.$kodeRole) : null;
        if ($roleLokal && ($role = Role::where('name', $roleLokal)->first())) {
            $user->roles()->syncWithoutDetaching([$role->id]);
        }
    }
}
