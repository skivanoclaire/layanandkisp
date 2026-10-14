<?php

namespace App\Http\Controllers\Admin\TataKelolaKeamanan;

use App\Http\Controllers\Controller;
use App\Models\TataKelolaKeamanan\Rekomendasi;
use App\Models\TataKelolaKeamanan\Evaluasi;
use App\Services\TataKelolaKeamanan\Kapabilitas;
use App\Services\TataKelolaKeamanan\Laporan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Tahap 6: analisis kesenjangan dan peta jalan rekomendasi.
 */
class RekomendasiController extends Controller
{
    public function index(Evaluasi $evaluasi, Kapabilitas $kapabilitas, Laporan $laporan)
    {
        $kesenjangan = $laporan->urutkanKesenjangan($kapabilitas->ringkasanEvaluasi($evaluasi));
        $kontrolBelum = $laporan->kontrolBelumDiterapkan($evaluasi);
        $rekomendasis = $evaluasi->rekomendasis()->with('praktik')->orderByRaw("FIELD(horizon, 'pendek', 'menengah', 'panjang')")->orderBy('prioritas')->get();
        $praktiks = $evaluasi->praktiks()->whereIn('objektif_kode', $evaluasi->objektifDipilih()->pluck('kode'))->orderBy('kode')->get();

        return view('admin.tata-kelola-keamanan.rekomendasi', compact('evaluasi', 'kesenjangan', 'kontrolBelum', 'rekomendasis', 'praktiks'));
    }

    public function store(Request $request, Evaluasi $evaluasi)
    {
        $evaluasi->rekomendasis()->create($this->validasi($request, $evaluasi));

        return back()->with('success', 'Rekomendasi ditambahkan.');
    }

    public function update(Request $request, Evaluasi $evaluasi, Rekomendasi $rekomendasi)
    {
        abort_unless($rekomendasi->evaluasi_id === $evaluasi->id, 404);
        $rekomendasi->update($this->validasi($request, $evaluasi));

        return back()->with('success', 'Rekomendasi diperbarui.');
    }

    public function destroy(Evaluasi $evaluasi, Rekomendasi $rekomendasi)
    {
        abort_unless($rekomendasi->evaluasi_id === $evaluasi->id, 404);
        $rekomendasi->delete();

        return back()->with('success', 'Rekomendasi dihapus.');
    }

    private function validasi(Request $request, Evaluasi $evaluasi): array
    {
        $data = $request->validate([
            'praktik_id' => ['nullable', Rule::exists('tata_kelola_keamanan_praktiks', 'id')->where('evaluasi_id', $evaluasi->id)],
            'objektif_kode' => ['nullable', Rule::in(array_keys(config('tata_kelola_keamanan.objektif')))],
            'iso_kode' => ['nullable', Rule::in(array_keys(config('tata_kelola_keamanan.iso_kontrol')))],
            'uraian' => ['required', 'string', 'max:2000'],
            'horizon' => ['required', Rule::in(array_keys(config('tata_kelola_keamanan.horizon')))],
            'prioritas' => ['required', 'integer', 'between:1,999'],
            'penanggung_jawab' => ['nullable', 'string', 'max:255'],
        ]);

        if (! empty($data['praktik_id'])) {
            $data['objektif_kode'] = $evaluasi->praktiks()->whereKey($data['praktik_id'])->value('objektif_kode');
        }

        return $data;
    }
}
