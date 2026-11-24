<?php

namespace App\Services\Sso;

use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Pencocokan identitas hasil SSO ke akun lokal.
 *
 * Aturan keamanan (lihat audit A-05 dan A-09):
 *  - Hanya akun terpercaya (sudah diverifikasi admin, atau berasal dari SSO) yang boleh dicocokkan.
 *    Pendaftar mandiri yang belum diverifikasi tidak bisa "mengambil alih" NIP/NIK ASN.
 *  - NIP/NIK yang sama di akun tidak terpercaya dilepas, karena SSO sudah membuktikan identitasnya.
 *    Dengan begitu NIP/NIK tetap unik dan akun penyerang tidak lagi memegang identitas tersebut.
 */
class AkunSsoMatcher
{
    /**
     * @return array{user: ?User, matched_by: ?string}
     */
    public function cari(?string $nip, ?string $nik, ?string $email, bool $isAsn): array
    {
        $nip = preg_replace('/\s+/', '', (string) $nip);
        $nikHash = $nik ? User::hashNik($nik) : null;

        $user = null;
        $matchedBy = null;

        if ($nip !== '') {
            $user = User::terpercaya()->where('nip', $nip)->orderBy('is_sso_user')->orderBy('id')->first();
            $matchedBy = 'nip';
        }

        if (! $user && $nikHash) {
            $user = User::terpercaya()->where('nik_hash', $nikHash)->orderBy('is_sso_user')->orderBy('id')->first();
            $matchedBy = 'nik';
        }

        if (! $user && $email && $isAsn) {
            $user = User::terpercaya()->where('email', $email)->first();
            $matchedBy = 'email';
        }

        $this->lepasDariAkunTidakTerpercaya($nip, $nikHash, $user?->id);

        return ['user' => $user, 'matched_by' => $matchedBy];
    }

    private function lepasDariAkunTidakTerpercaya(string $nip, ?string $nikHash, ?int $pemilikId): void
    {
        if ($nip === '' && ! $nikHash) {
            return;
        }

        $pemakai = User::query()
            ->where('is_verified', false)
            ->where('is_sso_user', false)
            ->when($pemilikId, fn ($q) => $q->where('id', '!=', $pemilikId))
            ->where(function ($q) use ($nip, $nikHash) {
                if ($nip !== '') {
                    $q->orWhere('nip', $nip);
                }
                if ($nikHash) {
                    $q->orWhere('nik_hash', $nikHash);
                }
            })
            ->get();

        foreach ($pemakai as $akun) {
            $dilepas = [];
            if ($nip !== '' && $akun->nip === $nip) {
                $akun->nip = null;
                $dilepas[] = 'nip';
            }
            if ($nikHash && $akun->nik_hash === $nikHash) {
                $akun->nik = null;
                $akun->nik_hash = null;
                $dilepas[] = 'nik';
            }

            $akun->save();

            Log::warning('Identitas SSO dilepas dari akun belum terverifikasi', [
                'user_id' => $akun->id,
                'field' => $dilepas,
            ]);
        }
    }
}
