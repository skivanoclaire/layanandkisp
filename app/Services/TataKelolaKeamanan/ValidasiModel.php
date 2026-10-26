<?php

namespace App\Services\TataKelolaKeamanan;

use App\Models\TataKelolaKeamanan\Evaluasi;
use App\Models\TataKelolaKeamanan\HasilFgd;
use App\Models\TataKelolaKeamanan\Pemetaan;
use App\Models\TataKelolaKeamanan\SesiFgd;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Validasi model dua tahap (proposal subbab 3.3.3):
 * 1) verifikasi silang setiap butir pemetaan terhadap dokumen acuan: didukung bila keterkaitannya
 *    tercantum pada sekurang-kurangnya satu dokumen acuan; butir yang tidak didukung hanya
 *    dipertahankan bila keterkaitannya dapat dijelaskan (dicatat sebagai rancangan peneliti);
 * 2) FGD bersama praktisi DKISP: butir disepakati bila forum mencapai mufakat setuju; butir yang belum
 *    disepakati diperbaiki dan dibahas ulang pada sesi lanjutan, atau dikeluarkan beserta alasannya.
 */
class ValidasiModel
{
    /**
     * @param  array<string, ?string>  $dokumen  kode dokumen acuan => rujukan (halaman/pasal/tabel)
     */
    public function verifikasi(Pemetaan $pemetaan, array $dokumen, ?string $catatan, bool $pertahankan): string
    {
        $this->pastikanBolehUbah($pemetaan);

        $dokumen = array_intersect_key($dokumen, config('tata_kelola_keamanan.dokumen_acuan'));
        $catatan = filled($catatan) ? trim($catatan) : null;

        if ($dokumen) {
            $hasil = Pemetaan::VERIFIKASI_DIDUKUNG;
        } elseif ($pertahankan) {
            if (! $catatan) {
                throw new RuntimeException('Butir yang tidak didukung dokumen acuan hanya dapat dipertahankan bila keterkaitannya dijelaskan pada catatan.');
            }
            $hasil = Pemetaan::VERIFIKASI_RANCANGAN_PENELITI;
        } else {
            $hasil = Pemetaan::VERIFIKASI_TIDAK_DIDUKUNG;
        }

        $pemetaan->update([
            'verifikasi' => $hasil,
            'dokumen_acuan' => $dokumen ?: null,
            'catatan_verifikasi' => $catatan,
        ]);

        return $hasil;
    }

    public function bukaSesi(Evaluasi $evaluasi, array $data): SesiFgd
    {
        if ($evaluasi->sesiAktif()) {
            throw new RuntimeException('Masih ada sesi FGD yang dibuka. Tutup sesi tersebut terlebih dahulu.');
        }
        if (! $evaluasi->pemetaans()->get()->contains(fn (Pemetaan $p) => $p->siapFgd())) {
            throw new RuntimeException('Belum ada butir draf yang lolos verifikasi dokumen untuk dibahas dalam FGD.');
        }

        return $evaluasi->sesiFgds()->create($data + [
            'nomor' => (int) $evaluasi->sesiFgds()->max('nomor') + 1,
            'status' => SesiFgd::STATUS_DIBUKA,
        ]);
    }

    /** Butir yang dibahas pada sesi: butir siap FGD selama sesi dibuka, ditambah yang sudah diputuskan di sesi itu. */
    public function butir(SesiFgd $sesi): Collection
    {
        $diputuskan = HasilFgd::where('sesi_fgd_id', $sesi->id)->pluck('pemetaan_id')->all();

        return Pemetaan::where('evaluasi_id', $sesi->evaluasi_id)
            ->with('praktik')
            ->get()
            ->filter(fn (Pemetaan $p) => in_array($p->id, $diputuskan, true)
                || ($sesi->status === SesiFgd::STATUS_DIBUKA && $p->siapFgd()))
            ->sortBy(fn ($p) => $p->praktik->kode.'-'.str_pad($p->id, 6, '0', STR_PAD_LEFT))
            ->values();
    }

    /** Rekap per butir: keputusan forum (null = belum dibahas) dan saran. */
    public function rekap(SesiFgd $sesi): Collection
    {
        $hasil = HasilFgd::where('sesi_fgd_id', $sesi->id)->get()->keyBy('pemetaan_id');

        return $this->butir($sesi)->map(fn (Pemetaan $p) => [
            'pemetaan' => $p,
            'keputusan' => $hasil[$p->id]->keputusan ?? null,
            'saran' => $hasil[$p->id]->saran ?? null,
        ]);
    }

    /**
     * Mencatat keputusan forum. Keputusan kosong menghapus catatan sebelumnya (butir belum dibahas).
     *
     * @param  array<int, ?string>  $keputusan  pemetaan_id => setuju | tidak_setuju | null
     * @param  array<int, ?string>  $saran
     */
    public function simpanHasil(SesiFgd $sesi, array $keputusan, array $saran): int
    {
        if ($sesi->status !== SesiFgd::STATUS_DIBUKA) {
            throw new RuntimeException('Sesi FGD sudah ditutup.');
        }

        $butir = $this->butir($sesi)->pluck('id')->all();
        $disimpan = 0;

        DB::transaction(function () use ($sesi, $keputusan, $saran, $butir, &$disimpan) {
            foreach ($keputusan as $pemetaanId => $nilai) {
                if (! in_array((int) $pemetaanId, $butir, true)) {
                    continue;
                }
                if (! in_array($nilai, [HasilFgd::SETUJU, HasilFgd::TIDAK_SETUJU], true)) {
                    HasilFgd::where('sesi_fgd_id', $sesi->id)->where('pemetaan_id', $pemetaanId)->delete();
                    continue;
                }
                HasilFgd::updateOrCreate(
                    ['sesi_fgd_id' => $sesi->id, 'pemetaan_id' => (int) $pemetaanId],
                    ['keputusan' => $nilai, 'saran' => filled($saran[$pemetaanId] ?? null) ? trim($saran[$pemetaanId]) : null]
                );
                $disimpan++;
            }
        });

        return $disimpan;
    }

    /**
     * Menutup sesi dan menerapkan keputusan: butir yang disetujui forum berstatus disepakati;
     * butir yang tidak disetujui atau belum dibahas tetap draf untuk diperbaiki/dibahas pada sesi lanjutan.
     */
    public function tutupSesi(SesiFgd $sesi): array
    {
        if ($sesi->status !== SesiFgd::STATUS_DIBUKA) {
            throw new RuntimeException('Sesi FGD sudah ditutup.');
        }

        $rekap = $this->rekap($sesi);
        $hasil = ['disepakati' => 0, 'perbaikan' => 0, 'belum_dibahas' => 0];

        DB::transaction(function () use ($sesi, $rekap, &$hasil) {
            foreach ($rekap as $r) {
                if ($r['keputusan'] === HasilFgd::SETUJU) {
                    $r['pemetaan']->update(['status' => Pemetaan::STATUS_DISEPAKATI, 'sesi_disepakati' => $sesi->nomor]);
                    $hasil['disepakati']++;
                } elseif ($r['keputusan'] === HasilFgd::TIDAK_SETUJU) {
                    $hasil['perbaikan']++;
                } else {
                    $hasil['belum_dibahas']++;
                }
            }

            $sesi->update(['status' => SesiFgd::STATUS_DITUTUP, 'ditutup_at' => now()]);
        });

        return $hasil;
    }

    /** Mengeluarkan butir draf dari model; alasan wajib dicatat. */
    public function keluarkan(Pemetaan $pemetaan, string $alasan): void
    {
        $this->pastikanBolehUbah($pemetaan);
        if (blank($alasan)) {
            throw new RuntimeException('Tuliskan alasan butir dikeluarkan dari model.');
        }

        $pemetaan->update(['status' => Pemetaan::STATUS_DIKELUARKAN, 'alasan_dikeluarkan' => trim($alasan)]);
    }

    /** Ringkasan hasil verifikasi dokumen (butir yang dikeluarkan tidak dihitung). */
    public function ringkasanVerifikasi(Evaluasi $evaluasi): array
    {
        $butir = $evaluasi->pemetaans()->where('status', '!=', Pemetaan::STATUS_DIKELUARKAN)->pluck('verifikasi');
        $hasil = array_fill_keys(array_keys(config('tata_kelola_keamanan.verifikasi')), 0) + ['belum' => 0];
        foreach ($butir as $v) {
            $hasil[$v ?? 'belum']++;
        }

        return $hasil;
    }

    private function pastikanBolehUbah(Pemetaan $pemetaan): void
    {
        if ($pemetaan->status !== Pemetaan::STATUS_DRAF) {
            throw new RuntimeException('Hanya butir berstatus draf yang dapat diubah.');
        }
        if ($pemetaan->evaluasi->sesiAktif()) {
            throw new RuntimeException('Sesi FGD sedang dibuka. Tutup sesi sebelum mengubah butir pemetaan.');
        }
    }
}
