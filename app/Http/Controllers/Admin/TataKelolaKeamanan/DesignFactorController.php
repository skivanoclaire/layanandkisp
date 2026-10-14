<?php

namespace App\Http\Controllers\Admin\TataKelolaKeamanan;

use App\Http\Controllers\Controller;
use App\Models\TataKelolaKeamanan\DesignFactor;
use App\Models\TataKelolaKeamanan\Evaluasi;
use App\Services\TataKelolaKeamanan\DesignFactorInput;
use Illuminate\Http\Request;

/**
 * Tahap 1: jawaban kuesioner design factors. Responden dapat mengisi sendiri
 * melalui akunnya (ditandai "akun"), atau pengelola mengentri dari kuesioner kertas.
 * Nilai akhir hasil FGD disimpan tanpa nama responden dan disalin ke COBIT 2019 Design Toolkit.
 */
class DesignFactorController extends Controller
{
    public function index(Request $request, Evaluasi $evaluasi)
    {
        $definisi = config('tata_kelola_keamanan.design_factors');
        $responden = (string) $request->query('responden', DesignFactor::NILAI_AKHIR);

        $isian = $evaluasi->designFactors()->where('responden', $responden)->get()->keyBy('df_kode');
        $daftarResponden = $evaluasi->designFactors()->where('responden', '!=', DesignFactor::NILAI_AKHIR)
            ->selectRaw('responden, MAX(responden_id) as responden_id')
            ->groupBy('responden')->orderBy('responden')->get();
        $isianAkun = $isian->contains(fn ($d) => $d->responden_id !== null);

        // Rekap semua responden untuk dibandingkan dengan nilai akhir
        $semua = $evaluasi->designFactors()->get()->groupBy('df_kode');

        return view('admin.tata-kelola-keamanan.design-factors', compact('evaluasi', 'definisi', 'responden', 'isian', 'daftarResponden', 'semua', 'isianAkun'));
    }

    public function store(Request $request, Evaluasi $evaluasi, DesignFactorInput $input)
    {
        $request->validate([
            'responden' => ['nullable', 'string', 'max:100'],
            'nilai' => ['array'],
            'catatan' => ['array'],
        ]);
        $responden = trim((string) $request->input('responden', ''));

        if ($responden !== '' && $evaluasi->designFactors()->where('responden', $responden)->whereNotNull('responden_id')->exists()) {
            return back()->with('error', 'Isian ini diisi responden sendiri melalui akunnya dan tidak dapat diubah dari halaman admin.');
        }

        foreach ($input->bersihkanSemua((array) $request->input('nilai', [])) as $kode => $nilai) {
            DesignFactor::updateOrCreate(
                ['evaluasi_id' => $evaluasi->id, 'df_kode' => $kode, 'responden' => $responden],
                ['nilai' => $nilai, 'catatan' => $request->input("catatan.$kode")]
            );
        }

        return redirect()->route('admin.tata-kelola-keamanan.design-factors', [$evaluasi, 'responden' => $responden])
            ->with('success', $responden === '' ? 'Nilai akhir design factors disimpan.' : "Jawaban responden \"$responden\" disimpan.");
    }

    public function destroyResponden(Request $request, Evaluasi $evaluasi)
    {
        $responden = (string) $request->input('responden');
        abort_if($responden === '', 422);
        $evaluasi->designFactors()->where('responden', $responden)->whereNull('responden_id')->delete();

        return redirect()->route('admin.tata-kelola-keamanan.design-factors', $evaluasi)->with('success', "Jawaban \"$responden\" dihapus.");
    }
}
