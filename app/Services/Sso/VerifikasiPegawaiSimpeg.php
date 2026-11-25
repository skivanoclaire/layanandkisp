<?php

namespace App\Services\Sso;

use App\Exceptions\SimpegException;
use App\Services\SimpegClient;
use Illuminate\Support\Facades\Log;
use Kaltaraprov\SsoPortal\SsoPortalException;

/**
 * Menahan pembuatan akun baru dari SSO BKN bila NIP tidak terdaftar di SIMPEG.
 *
 * Hanya dipanggil saat akun lokal BELUM ada (pendaftaran baru). Akun yang sudah
 * ada tetap bisa login walaupun SIMPEG sedang tidak tersedia.
 * Fail-closed: bila SIMPEG error, pendaftaran ditolak.
 */
class VerifikasiPegawaiSimpeg
{
    public function __construct(private SimpegClient $simpeg)
    {
    }

    /**
     * @throws SsoPortalException bila pendaftaran tidak boleh dilanjutkan
     */
    public function pastikanBolehDaftar(?string $nip): void
    {
        $nip = preg_replace('/\s+/', '', (string) $nip);

        if ($nip === '') {
            throw new SsoPortalException(
                'Pendaftaran SSO ditolak: NIP kosong.',
                'Akun SSO Anda tidak memiliki NIP. Pendaftaran akun di E-Layanan hanya untuk ASN Pemprov Kaltara.'
            );
        }

        try {
            $hasil = $this->simpeg->byNip($nip);
        } catch (SimpegException $e) {
            Log::error('SSO: verifikasi NIP ke SIMPEG gagal, pendaftaran ditolak', [
                'nip' => SimpegClient::samarkan($nip),
                'status' => $e->status,
                'msg' => $e->getMessage(),
            ]);

            throw new SsoPortalException(
                'Pendaftaran SSO ditolak: SIMPEG tidak tersedia.',
                'Verifikasi NIP ke SIMPEG sedang tidak tersedia. Silakan coba beberapa saat lagi.'
            );
        }

        if (empty($hasil['valid'])) {
            Log::warning('SSO: NIP tidak terdaftar di SIMPEG, pendaftaran ditolak', [
                'nip' => SimpegClient::samarkan($nip),
            ]);

            throw new SsoPortalException(
                'Pendaftaran SSO ditolak: NIP tidak ada di SIMPEG.',
                'NIP Anda tidak terdaftar sebagai pegawai di SIMPEG. Pendaftaran akun E-Layanan hanya untuk ASN Pemprov Kaltara.'
            );
        }
    }
}
