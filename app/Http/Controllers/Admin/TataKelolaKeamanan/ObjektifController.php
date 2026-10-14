<?php

namespace App\Http\Controllers\Admin\TataKelolaKeamanan;

use App\Http\Controllers\Controller;
use App\Models\TataKelolaKeamanan\Objektif;
use App\Models\TataKelolaKeamanan\Praktik;
use App\Models\TataKelolaKeamanan\Evaluasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Tahap 2: skor kepentingan 40 objektif dari COBIT 2019 Design Toolkit, penetapan target
 * tingkat kapabilitas (Tabel 3.2), dan pemilihan objektif yang dinilai.
 */
class ObjektifController extends Controller
{
    public function index(Evaluasi $evaluasi)
    {
        $this->siapkan($evaluasi);
        $objektifs = $evaluasi->objektifs()->orderBy('kode')->get();
        $saran = $this->saranPilihan($objektifs);

        return view('admin.tata-kelola-keamanan.objektif', compact('evaluasi', 'objektifs', 'saran'));
    }

    public function update(Request $request, Evaluasi $evaluasi)
    {
        $this->siapkan($evaluasi);
        $request->validate([
            'skor' => ['array'],
            'skor.*' => ['nullable', 'integer', 'between:-100,100'],
            'keamanan' => ['array'],
            'dipilih' => ['array'],
            'target' => ['array'],
            'target.*' => ['nullable', 'integer', 'between:1,5'],
            'alasan' => ['array'],
            'alasan.*' => ['nullable', 'string', 'max:1000'],
        ]);

        $dipilih = array_keys(array_filter((array) $request->input('dipilih', [])));
        $maks = config('tata_kelola_keamanan.aturan.maksimal_objektif');
        if (count($dipilih) > $maks) {
            return back()->withInput()->with('error', "Objektif yang dinilai paling banyak $maks (Batasan Masalah 3).");
        }

        DB::transaction(function () use ($request, $evaluasi, $dipilih) {
            foreach ($evaluasi->objektifs as $o) {
                $skor = $request->input("skor.{$o->kode}");
                $o->update([
                    'skor_kepentingan' => ($skor === null || $skor === '') ? null : (int) $skor,
                    'terkait_keamanan' => (bool) $request->input("keamanan.{$o->kode}"),
                    'dipilih' => in_array($o->kode, $dipilih, true),
                    'target_manual' => $request->input("target.{$o->kode}") ?: null,
                    'alasan' => $request->input("alasan.{$o->kode}"),
                ]);
            }
            $this->siapkanPraktik($evaluasi);
        });

        return back()->with('success', 'Skor dan pilihan objektif disimpan.');
    }

    /** Menerapkan saran: skor >= 50, terkait keamanan, maksimal 8 dengan skor tertinggi. */
    public function terapkanSaran(Evaluasi $evaluasi)
    {
        $this->siapkan($evaluasi);
        $saran = $this->saranPilihan($evaluasi->objektifs()->get());

        DB::transaction(function () use ($evaluasi, $saran) {
            $evaluasi->objektifs()->update(['dipilih' => false]);
            $evaluasi->objektifs()->whereIn('kode', $saran)->update(['dipilih' => true]);
            $this->siapkanPraktik($evaluasi);
        });

        return back()->with('success', 'Saran pemilihan diterapkan: '.(implode(', ', $saran) ?: 'tidak ada objektif yang memenuhi syarat').'.');
    }

    public function saranPilihan($objektifs): array
    {
        return $objektifs
            ->filter(fn (Objektif $o) => $o->terkait_keamanan && $o->skor_kepentingan !== null
                && $o->skor_kepentingan >= config('tata_kelola_keamanan.aturan.skor_minimal_dipilih'))
            ->sortBy([['skor_kepentingan', 'desc'], ['kode', 'asc']])
            ->take(config('tata_kelola_keamanan.aturan.maksimal_objektif'))
            ->pluck('kode')
            ->sort()
            ->values()
            ->all();
    }

    /** Membuat baris 40 objektif untuk evaluasi bila belum ada. */
    private function siapkan(Evaluasi $evaluasi): void
    {
        $ada = $evaluasi->objektifs()->pluck('kode')->all();
        foreach (config('tata_kelola_keamanan.objektif') as $kode => $o) {
            if (! in_array($kode, $ada, true)) {
                $evaluasi->objektifs()->create(['kode' => $kode, 'terkait_keamanan' => $o['keamanan']]);
            }
        }
        $evaluasi->load('objektifs');
    }

    /** Menyalin daftar praktik dari config untuk objektif terpilih yang belum punya praktik. */
    private function siapkanPraktik(Evaluasi $evaluasi): void
    {
        foreach ($evaluasi->objektifs()->where('dipilih', true)->pluck('kode') as $kode) {
            if (Praktik::where('evaluasi_id', $evaluasi->id)->where('objektif_kode', $kode)->exists()) {
                continue;
            }
            $urutan = 0;
            foreach (config("tata_kelola_keamanan.praktik.$kode", []) as $kodePraktik => $nama) {
                Praktik::create([
                    'evaluasi_id' => $evaluasi->id,
                    'objektif_kode' => $kode,
                    'kode' => $kodePraktik,
                    'nama' => $nama,
                    'urutan' => ++$urutan,
                ]);
            }
        }
    }
}
