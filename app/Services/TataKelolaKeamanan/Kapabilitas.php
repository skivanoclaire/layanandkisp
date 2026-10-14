<?php

namespace App\Services\TataKelolaKeamanan;

use App\Models\TataKelolaKeamanan\Objektif;
use App\Models\TataKelolaKeamanan\Praktik;
use App\Models\TataKelolaKeamanan\Evaluasi;
use Illuminate\Support\Collection;

/**
 * Penentuan tingkat kapabilitas:
 * - setiap aktivitas dinyatakan terpenuhi atau belum berdasarkan bukti;
 * - pencapaian tiap tingkat = bagian aktivitas yang terpenuhi, lalu diberi rating N/P/L/F;
 * - penilaian dimulai dari tingkat 2; objektif mencapai suatu tingkat bila ratingnya F,
 *   bila tidak penilaian berhenti dan objektif berada pada tingkat di bawahnya.
 */
class Kapabilitas
{
    /**
     * @param  Collection<int, \App\Models\TataKelolaKeamanan\Aktivitas>  $aktivitas  aktivitas seluruh praktik objektif
     */
    public function hitung(Collection $aktivitas): array
    {
        $perLevel = [];
        foreach (config('tata_kelola_keamanan.aturan.level_dinilai') as $level) {
            $items = $aktivitas->where('level', $level);
            $total = $items->count();
            $terpenuhi = $items->where('terpenuhi', true)->count();
            $dinilai = $items->whereNotNull('terpenuhi')->count();
            $persen = $total > 0 ? round($terpenuhi / $total * 100, 1) : null;

            $perLevel[$level] = [
                'total' => $total,
                'terpenuhi' => $terpenuhi,
                'dinilai' => $dinilai,
                'lengkap' => $total > 0 && $dinilai === $total,
                'persen' => $persen,
                'rating' => $persen === null ? null : Aturan::rating($persen),
            ];
        }

        $levelSaatIni = null;
        $lengkap = true;
        if ($aktivitas->isNotEmpty()) {
            $levelSaatIni = 1;
            foreach ($perLevel as $level => $data) {
                if ($data['total'] === 0) {
                    break;
                }
                if (! $data['lengkap']) {
                    $lengkap = false;
                }
                if ($data['rating'] !== 'F') {
                    break;
                }
                $levelSaatIni = $level;
            }
        }

        return [
            'per_level' => $perLevel,
            'level' => $levelSaatIni,
            'lengkap' => $aktivitas->isNotEmpty() && $lengkap,
            'jumlah_aktivitas' => $aktivitas->count(),
        ];
    }

    /** Ringkasan kapabilitas dan kesenjangan setiap objektif terpilih dalam evaluasi. */
    public function ringkasanEvaluasi(Evaluasi $evaluasi): Collection
    {
        $objektifs = $evaluasi->objektifDipilih()->get();
        $praktiks = Praktik::where('evaluasi_id', $evaluasi->id)
            ->whereIn('objektif_kode', $objektifs->pluck('kode'))
            ->with('aktivitas')
            ->get()
            ->groupBy('objektif_kode');

        return $objektifs->map(function (Objektif $o) use ($praktiks) {
            $aktivitas = ($praktiks[$o->kode] ?? collect())->flatMap->aktivitas;
            $hasil = $this->hitung($aktivitas);
            $target = $o->target_level;

            return [
                'objektif' => $o,
                'hasil' => $hasil,
                'target' => $target,
                'gap' => ($target !== null && $hasil['level'] !== null) ? max(0, $target - $hasil['level']) : null,
            ];
        });
    }
}
