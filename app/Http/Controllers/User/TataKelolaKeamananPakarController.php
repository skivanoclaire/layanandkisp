<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\TataKelolaKeamanan\Evaluasi;
use App\Models\TataKelolaKeamanan\Pakar;
use App\Models\TataKelolaKeamanan\Penilaian;
use App\Models\TataKelolaKeamanan\Putaran;
use App\Services\TataKelolaKeamanan\Delphi;
use Illuminate\Http\Request;

/**
 * Halaman penilaian pakar (akun portal). Pakar hanya dapat menilai evaluasi tempat ia
 * didaftarkan dan hanya selama putaran penilaian dibuka.
 */
class TataKelolaKeamananPakarController extends Controller
{
    public function index(Request $request)
    {
        $pakars = Pakar::where('user_id', $request->user()->id)->where('aktif', true)->with('evaluasi')->get();
        $putaranAktif = $pakars->mapWithKeys(fn (Pakar $p) => [$p->id => $p->evaluasi->putaranAktif()]);

        return view('user.tata-kelola-keamanan.pakar-index', compact('pakars', 'putaranAktif'));
    }

    public function show(Request $request, Evaluasi $evaluasi, Delphi $delphi)
    {
        $pakar = $this->pakar($request, $evaluasi);
        $putaran = $evaluasi->putaranAktif();
        $butir = $putaran ? $delphi->butir($putaran) : collect();
        $jawaban = $putaran
            ? Penilaian::where('putaran_id', $putaran->id)->where('pakar_id', $pakar->id)->get()->keyBy('pemetaan_id')
            : collect();
        $riwayat = $this->riwayatSaran($evaluasi, $putaran);

        return view('user.tata-kelola-keamanan.pakar', compact('evaluasi', 'pakar', 'putaran', 'butir', 'jawaban', 'riwayat'));
    }

    public function store(Request $request, Evaluasi $evaluasi, Delphi $delphi)
    {
        $pakar = $this->pakar($request, $evaluasi);
        $putaran = $evaluasi->putaranAktif();
        abort_unless($putaran, 403, 'Tidak ada putaran penilaian yang sedang dibuka.');

        $butir = $delphi->butir($putaran)->pluck('id')->all();
        $request->validate([
            'setuju' => ['required', 'array'],
            'setuju.*' => ['required', 'in:1,0'],
            'saran' => ['array'],
            'saran.*' => ['nullable', 'string', 'max:2000'],
        ], ['setuju.required' => 'Berikan penilaian setuju atau tidak setuju.']);

        $disimpan = 0;
        foreach ((array) $request->input('setuju') as $pemetaanId => $nilai) {
            if (! in_array((int) $pemetaanId, $butir, true)) {
                continue;
            }
            Penilaian::updateOrCreate(
                ['putaran_id' => $putaran->id, 'pakar_id' => $pakar->id, 'pemetaan_id' => (int) $pemetaanId],
                ['setuju' => $nilai === '1', 'saran' => $request->input("saran.$pemetaanId")]
            );
            $disimpan++;
        }

        $sisa = count($butir) - Penilaian::where('putaran_id', $putaran->id)->where('pakar_id', $pakar->id)->count();
        $pesan = "Terima kasih. $disimpan penilaian tersimpan.";
        if ($sisa > 0) {
            $pesan .= " Masih ada $sisa butir yang belum dinilai.";
        }

        return redirect()->route('tata-kelola-keamanan.pakar.show', $evaluasi)->with('success', $pesan);
    }

    private function pakar(Request $request, Evaluasi $evaluasi): Pakar
    {
        $pakar = Pakar::where('evaluasi_id', $evaluasi->id)->where('user_id', $request->user()->id)->where('aktif', true)->first();
        abort_unless($pakar, 403, 'Akun Anda tidak terdaftar sebagai pakar pada evaluasi ini.');

        return $pakar;
    }

    /** Ringkasan pendapat panel pada putaran sebelumnya (umpan balik Delphi), tanpa nama pakar. */
    private function riwayatSaran(Evaluasi $evaluasi, ?Putaran $putaran): array
    {
        if (! $putaran || $putaran->nomor <= 1) {
            return [];
        }
        $sebelumnya = Putaran::where('evaluasi_id', $evaluasi->id)->where('nomor', $putaran->nomor - 1)->first();
        if (! $sebelumnya) {
            return [];
        }

        return Penilaian::where('putaran_id', $sebelumnya->id)->get()->groupBy('pemetaan_id')
            ->map(fn ($items) => [
                'setuju' => $items->where('setuju', true)->count(),
                'jumlah' => $items->count(),
                'saran' => $items->pluck('saran')->filter()->values()->all(),
            ])->all();
    }
}
