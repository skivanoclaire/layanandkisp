<?php

namespace App\Http\Controllers\Admin\TataKelolaKeamanan;

use App\Http\Controllers\Controller;
use App\Models\TataKelolaKeamanan\Pemetaan;
use App\Models\TataKelolaKeamanan\Praktik;
use App\Models\TataKelolaKeamanan\Evaluasi;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Tahap 3: pemetaan praktik COBIT 2019 ke klausul/kontrol ISO/IEC 27001:2022 dan ketentuan
 * regulasi. Butir hanya dapat diubah selama berstatus draf dan tidak ada sesi FGD
 * validasi model yang sedang dibuka. Mengubah isi butir membatalkan hasil verifikasi dokumennya.
 */
class PemetaanController extends Controller
{
    public function index(Evaluasi $evaluasi)
    {
        $objektifs = $evaluasi->objektifDipilih()->get();
        $praktiks = $evaluasi->praktiks()
            ->whereIn('objektif_kode', $objektifs->pluck('kode'))
            ->with(['pemetaans'])
            ->orderBy('objektif_kode')->orderBy('urutan')->orderBy('kode')
            ->get()
            ->groupBy('objektif_kode');
        $sesiAktif = $evaluasi->sesiAktif();

        return view('admin.tata-kelola-keamanan.pemetaan', compact('evaluasi', 'objektifs', 'praktiks', 'sesiAktif'));
    }

    public function store(Request $request, Evaluasi $evaluasi)
    {
        $data = $this->validasi($request, $evaluasi);
        $this->pastikanBolehUbah($evaluasi);

        $evaluasi->pemetaans()->create($data + ['status' => Pemetaan::STATUS_DRAF]);

        return back()->with('success', 'Butir pemetaan ditambahkan.');
    }

    public function update(Request $request, Evaluasi $evaluasi, Pemetaan $pemetaan)
    {
        abort_unless($pemetaan->evaluasi_id === $evaluasi->id, 404);
        $this->pastikanBolehUbah($evaluasi, $pemetaan);
        $data = $this->validasi($request, $evaluasi);
        $data['catatan_revisi'] = $request->input('catatan_revisi');

        $pemetaan->fill($data);
        if ($pemetaan->isDirty(['praktik_id', 'iso_items', 'regulasi_items'])) {
            $pemetaan->fill(['verifikasi' => null, 'dokumen_acuan' => null, 'catatan_verifikasi' => null]);
        }
        $pemetaan->save();

        return back()->with('success', 'Butir pemetaan diperbarui.');
    }

    public function destroy(Evaluasi $evaluasi, Pemetaan $pemetaan)
    {
        abort_unless($pemetaan->evaluasi_id === $evaluasi->id, 404);
        $this->pastikanBolehUbah($evaluasi, $pemetaan);
        if ($pemetaan->hasilFgds()->exists()) {
            return back()->with('error', 'Butir yang sudah dibahas dalam FGD tidak dapat dihapus. Keluarkan dari model di tab 4 beserta alasannya.');
        }
        $pemetaan->delete();

        return back()->with('success', 'Butir pemetaan dihapus.');
    }

    public function praktikStore(Request $request, Evaluasi $evaluasi)
    {
        $data = $request->validate([
            'objektif_kode' => ['required', Rule::in($evaluasi->objektifDipilih()->pluck('kode'))],
            'kode' => ['required', 'string', 'max:10', Rule::unique('tata_kelola_keamanan_praktiks', 'kode')->where('evaluasi_id', $evaluasi->id)],
            'nama' => ['required', 'string', 'max:255'],
        ]);
        $data['urutan'] = (int) $evaluasi->praktiks()->where('objektif_kode', $data['objektif_kode'])->max('urutan') + 1;
        $evaluasi->praktiks()->create($data);

        return back()->with('success', 'Praktik ditambahkan.');
    }

    public function praktikDestroy(Evaluasi $evaluasi, Praktik $praktik)
    {
        abort_unless($praktik->evaluasi_id === $evaluasi->id, 404);
        if ($praktik->pemetaans()->exists() || $praktik->aktivitas()->exists()) {
            return back()->with('error', 'Praktik yang sudah memiliki pemetaan atau aktivitas tidak dapat dihapus.');
        }
        $praktik->delete();

        return back()->with('success', 'Praktik dihapus.');
    }

    private function validasi(Request $request, Evaluasi $evaluasi): array
    {
        $kodeIso = array_merge(array_keys(config('tata_kelola_keamanan.iso_klausul')), array_keys(config('tata_kelola_keamanan.iso_kontrol')));

        $data = $request->validate([
            'praktik_id' => ['required', Rule::exists('tata_kelola_keamanan_praktiks', 'id')->where('evaluasi_id', $evaluasi->id)],
            'iso_items' => ['required', 'array', 'min:1'],
            'iso_items.*' => [Rule::in($kodeIso)],
            'regulasi_items' => ['nullable', 'array'],
            'regulasi_items.*' => [Rule::in(array_keys(config('tata_kelola_keamanan.regulasi')))],
            'bukti_diharapkan' => ['nullable', 'string', 'max:2000'],
        ], [
            'iso_items.required' => 'Pilih minimal satu klausul atau kontrol ISO/IEC 27001:2022.',
        ]);
        $data['iso_items'] = array_values(array_unique($data['iso_items']));
        $data['regulasi_items'] = array_values(array_unique($data['regulasi_items'] ?? []));

        return $data;
    }

    private function pastikanBolehUbah(Evaluasi $evaluasi, ?Pemetaan $pemetaan = null): void
    {
        if ($evaluasi->sesiAktif()) {
            abort(back()->with('error', 'Sesi FGD validasi model sedang dibuka. Tutup sesi sebelum mengubah pemetaan.'));
        }
        if ($pemetaan && $pemetaan->status !== Pemetaan::STATUS_DRAF) {
            abort(back()->with('error', 'Hanya butir berstatus draf yang dapat diubah.'));
        }
    }
}
