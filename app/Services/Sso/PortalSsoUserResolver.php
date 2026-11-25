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
 * Urutan pencocokan: NIP, lalu NIK (hash), lalu email (khusus ASN). Hanya akun terpercaya
 * yang dicocokkan dan akun yang diblokir ditolak; detail aturannya ada di AkunSsoMatcher.
 */
class PortalSsoUserResolver implements UserResolver
{
    public function __construct(
        private AkunSsoMatcher $matcher,
        private VerifikasiPegawaiSimpeg $verifikasi,
    ) {
    }

    public function resolve(SsoUser $sso): ?Authenticatable
    {
        $nip = preg_replace('/\s+/', '', (string) $sso->nip);
        $nik = $sso->nik ? (string) $sso->nik : null;
        $email = $sso->email ? (string) $sso->email : null;
        $name = $sso->full_name ?: null;

        ['user' => $user, 'matched_by' => $matchedBy] = $this->matcher->cari($nip, $nik, $email, $sso->isAsn());

        if ($user) {
            if ($user->isBlocked()) {
                Log::warning('Login SSO ditolak: akun diblokir', ['user_id' => $user->id]);

                return null;
            }

            // Kolom yang sudah terisi tidak ditimpa; hanya melengkapi yang kosong.
            $update = [];
            if ($nip !== '' && $user->nip !== $nip && ! $this->dipakaiAkunLain('nip', $nip, $user->id)) {
                $update['nip'] = $nip;
            }
            if ($nik) {
                $nikHash = User::hashNik($nik);
                if ($nikHash && ! $this->dipakaiAkunLain('nik_hash', $nikHash, $user->id)) {
                    $update['nik'] = $nik;
                }
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

        if ($nip === '' && ! $email) {
            return null;
        }

        // Pendaftaran baru hanya untuk NIP yang terdaftar di SIMPEG (pegawai Pemprov Kaltara).
        $this->verifikasi->pastikanBolehDaftar($nip);

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

    private function dipakaiAkunLain(string $kolom, string $nilai, int $userId): bool
    {
        return User::where($kolom, $nilai)->where('id', '!=', $userId)->exists();
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
