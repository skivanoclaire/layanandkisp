<?php

namespace App\Services\TataKelolaKeamanan;

use App\Models\TataKelolaKeamanan\Pemetaan;
use App\Models\TataKelolaKeamanan\Penilaian;
use App\Models\TataKelolaKeamanan\Putaran;
use App\Models\TataKelolaKeamanan\Evaluasi;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Validasi pakar dengan prinsip Delphi: pakar menyatakan
 * setuju/tidak setuju per butir pemetaan; butir disepakati bila minimal 2/3 setuju;
 * butir yang belum disepakati direvisi dan dinilai ulang; paling banyak tiga putaran.
 */
class Delphi
{
    public function bukaPutaran(Evaluasi $evaluasi): Putaran
    {
        if ($evaluasi->putaranAktif()) {
            throw new RuntimeException('Masih ada putaran yang dibuka. Tutup putaran tersebut terlebih dahulu.');
        }

        $nomor = (int) $evaluasi->putarans()->max('nomor') + 1;
        if ($nomor > config('tata_kelola_keamanan.aturan.maksimal_putaran')) {
            throw new RuntimeException('Jumlah putaran sudah mencapai batas maksimal.');
        }
        if (! $evaluasi->pemetaans()->where('status', Pemetaan::STATUS_DRAF)->exists()) {
            throw new RuntimeException('Tidak ada butir pemetaan berstatus draf untuk dinilai.');
        }
        if (! $evaluasi->pakars()->where('aktif', true)->exists()) {
            throw new RuntimeException('Belum ada pakar aktif.');
        }

        return $evaluasi->putarans()->create([
            'nomor' => $nomor,
            'status' => Putaran::STATUS_DIBUKA,
            'dibuka_at' => now(),
        ]);
    }

    /** Butir yang dinilai pada putaran: pemetaan berstatus draf, atau yang sudah dinilai di putaran itu. */
    public function butir(Putaran $putaran): Collection
    {
        $dinilai = Penilaian::where('putaran_id', $putaran->id)->pluck('pemetaan_id')->unique();

        return Pemetaan::where('evaluasi_id', $putaran->evaluasi_id)
            ->where(function ($q) use ($putaran, $dinilai) {
                if ($putaran->status === Putaran::STATUS_DIBUKA) {
                    $q->where('status', Pemetaan::STATUS_DRAF);
                }
                $q->orWhereIn('id', $dinilai);
            })
            ->with('praktik')
            ->get()
            ->sortBy(fn ($p) => $p->praktik->kode.'-'.str_pad($p->id, 6, '0', STR_PAD_LEFT))
            ->values();
    }

    /** Rekap per butir: jumlah setuju, tidak setuju, keputusan, dan saran pakar. */
    public function rekap(Putaran $putaran): Collection
    {
        $penilaian = Penilaian::where('putaran_id', $putaran->id)->with('pakar.user')->get()->groupBy('pemetaan_id');

        return $this->butir($putaran)->map(function (Pemetaan $p) use ($penilaian) {
            $items = $penilaian[$p->id] ?? collect();
            $setuju = $items->where('setuju', true)->count();
            $jumlah = $items->count();

            return [
                'pemetaan' => $p,
                'setuju' => $setuju,
                'tidak_setuju' => $jumlah - $setuju,
                'jumlah_penilai' => $jumlah,
                'disepakati' => Aturan::disepakati($setuju, $jumlah),
                'saran' => $items->filter(fn ($i) => filled($i->saran))
                    ->map(fn ($i) => ['pakar' => $i->pakar?->nama, 'setuju' => $i->setuju, 'saran' => $i->saran])
                    ->values(),
            ];
        });
    }

    /**
     * Menutup putaran dan menerapkan keputusan: butir yang disepakati berstatus disepakati;
     * pada putaran terakhir, butir yang tetap belum disepakati dikeluarkan dari model.
     */
    public function tutupPutaran(Putaran $putaran): array
    {
        if ($putaran->status !== Putaran::STATUS_DIBUKA) {
            throw new RuntimeException('Putaran sudah ditutup.');
        }

        $rekap = $this->rekap($putaran);
        $terakhir = $putaran->nomor >= config('tata_kelola_keamanan.aturan.maksimal_putaran');
        $hasil = ['disepakati' => 0, 'revisi' => 0, 'dikeluarkan' => 0];

        DB::transaction(function () use ($putaran, $rekap, $terakhir, &$hasil) {
            foreach ($rekap as $r) {
                /** @var Pemetaan $p */
                $p = $r['pemetaan'];
                if ($p->status !== Pemetaan::STATUS_DRAF) {
                    continue;
                }
                if ($r['disepakati']) {
                    $p->update(['status' => Pemetaan::STATUS_DISEPAKATI, 'putaran_disepakati' => $putaran->nomor]);
                    $hasil['disepakati']++;
                } elseif ($terakhir) {
                    $p->update(['status' => Pemetaan::STATUS_DIKELUARKAN]);
                    $hasil['dikeluarkan']++;
                } else {
                    $hasil['revisi']++;
                }
            }

            $putaran->update(['status' => Putaran::STATUS_DITUTUP, 'ditutup_at' => now()]);
        });

        return $hasil;
    }
}
