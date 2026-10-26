<?php

namespace App\Http\Controllers\Admin\TataKelolaKeamanan;

use App\Http\Controllers\Controller;
use App\Models\TataKelolaKeamanan\Evaluasi;
use App\Models\TataKelolaKeamanan\HasilFgd;
use App\Models\TataKelolaKeamanan\Pemetaan;
use App\Models\TataKelolaKeamanan\SesiFgd;
use App\Services\TataKelolaKeamanan\ValidasiModel;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use RuntimeException;

/**
 * Tahap 4: validasi model, yaitu verifikasi silang butir pemetaan terhadap dokumen acuan,
 * lalu FGD bersama praktisi DKISP yang hasilnya dicatat pengelola per sesi.
 */
class ValidasiController extends Controller
{
    public function index(Request $request, Evaluasi $evaluasi, ValidasiModel $validasi)
    {
        $pemetaans = $evaluasi->pemetaans()->with('praktik')->get()
            ->sortBy(fn ($p) => $p->praktik->kode.'-'.str_pad($p->id, 6, '0', STR_PAD_LEFT))->values();
        $sesiList = $evaluasi->sesiFgds()->orderBy('nomor')->get();
        $sesi = $request->filled('sesi')
            ? $sesiList->firstWhere('nomor', (int) $request->query('sesi'))
            : $sesiList->last();
        $rekap = $sesi ? $validasi->rekap($sesi) : collect();
        $respondens = $evaluasi->respondens()->with('user')->get()->sortBy('jabatan')->values();
        $ringkasan = $validasi->ringkasanVerifikasi($evaluasi);
        $sesiAktif = $sesiList->firstWhere('status', SesiFgd::STATUS_DIBUKA);

        return view('admin.tata-kelola-keamanan.validasi', compact(
            'evaluasi', 'pemetaans', 'sesiList', 'sesi', 'rekap', 'respondens', 'ringkasan', 'sesiAktif'
        ));
    }

    public function verifikasi(Request $request, Evaluasi $evaluasi, Pemetaan $pemetaan, ValidasiModel $validasi)
    {
        abort_unless($pemetaan->evaluasi_id === $evaluasi->id, 404);
        $kode = array_keys(config('tata_kelola_keamanan.dokumen_acuan'));
        $data = $request->validate([
            'dokumen' => ['nullable', 'array'],
            'dokumen.*' => [Rule::in($kode)],
            'rujukan' => ['nullable', 'array'],
            'rujukan.*' => ['nullable', 'string', 'max:255'],
            'catatan_verifikasi' => ['nullable', 'string', 'max:2000'],
            'pertahankan' => ['boolean'],
        ]);

        $dokumen = [];
        foreach (array_unique($data['dokumen'] ?? []) as $k) {
            $dokumen[$k] = filled($data['rujukan'][$k] ?? null) ? trim($data['rujukan'][$k]) : null;
        }

        try {
            $hasil = $validasi->verifikasi($pemetaan, $dokumen, $data['catatan_verifikasi'] ?? null, (bool) ($data['pertahankan'] ?? false));
        } catch (RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return back()->with('success', "Verifikasi {$pemetaan->praktik->kode}: ".Pemetaan::labelVerifikasi($hasil).'.');
    }

    public function keluarkan(Request $request, Evaluasi $evaluasi, Pemetaan $pemetaan, ValidasiModel $validasi)
    {
        abort_unless($pemetaan->evaluasi_id === $evaluasi->id, 404);
        $data = $request->validate(['alasan_dikeluarkan' => ['required', 'string', 'max:2000']], [
            'alasan_dikeluarkan.required' => 'Tuliskan alasan butir dikeluarkan dari model.',
        ]);

        try {
            $validasi->keluarkan($pemetaan, $data['alasan_dikeluarkan']);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "Butir {$pemetaan->praktik->kode} dikeluarkan dari model.");
    }

    public function bukaSesi(Request $request, Evaluasi $evaluasi, ValidasiModel $validasi)
    {
        $data = $this->dataSesi($request, $evaluasi);

        try {
            $sesi = $validasi->bukaSesi($evaluasi, $data);
        } catch (RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.tata-kelola-keamanan.validasi', [$evaluasi, 'sesi' => $sesi->nomor])
            ->with('success', "Sesi FGD {$sesi->nomor} dibuka. Cetak lembar validasi model untuk peserta, lalu catat keputusan forum di bawah.");
    }

    public function simpanSesi(Request $request, Evaluasi $evaluasi, SesiFgd $sesi, ValidasiModel $validasi)
    {
        abort_unless($sesi->evaluasi_id === $evaluasi->id, 404);
        abort_unless($sesi->status === SesiFgd::STATUS_DIBUKA, 403, 'Sesi FGD sudah ditutup.');

        $data = $this->dataSesi($request, $evaluasi) + $request->validate([
            'catatan' => ['nullable', 'string', 'max:5000'],
            'keputusan' => ['nullable', 'array'],
            'keputusan.*' => ['nullable', Rule::in([HasilFgd::SETUJU, HasilFgd::TIDAK_SETUJU])],
            'saran' => ['nullable', 'array'],
            'saran.*' => ['nullable', 'string', 'max:2000'],
        ]);

        $sesi->update(collect($data)->only(['tanggal', 'tempat', 'peserta', 'peserta_lain', 'catatan'])->all());
        $jumlah = $validasi->simpanHasil($sesi, $data['keputusan'] ?? [], $data['saran'] ?? []);

        return back()->with('success', "Data sesi FGD {$sesi->nomor} disimpan; $jumlah keputusan butir tercatat.");
    }

    public function tutupSesi(Evaluasi $evaluasi, SesiFgd $sesi, ValidasiModel $validasi)
    {
        abort_unless($sesi->evaluasi_id === $evaluasi->id, 404);
        try {
            $hasil = $validasi->tutupSesi($sesi);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        $pesan = "Sesi FGD {$sesi->nomor} ditutup: {$hasil['disepakati']} butir disepakati, {$hasil['perbaikan']} perlu diperbaiki";
        if ($hasil['belum_dibahas']) {
            $pesan .= ", {$hasil['belum_dibahas']} belum dibahas";
        }

        return back()->with('success', $pesan.'.');
    }

    /** Lembar validasi model (Lampiran 3 proposal) untuk dicetak dan dibagikan kepada peserta FGD. */
    public function lembar(Request $request, Evaluasi $evaluasi, ValidasiModel $validasi)
    {
        $sesi = $request->filled('sesi')
            ? $evaluasi->sesiFgds()->where('nomor', (int) $request->query('sesi'))->firstOrFail()
            : $evaluasi->sesiAktif();
        $butir = $sesi
            ? $validasi->butir($sesi)
            : $evaluasi->pemetaans()->with('praktik')->get()->filter(fn (Pemetaan $p) => $p->siapFgd())
                ->sortBy(fn ($p) => $p->praktik->kode.'-'.str_pad($p->id, 6, '0', STR_PAD_LEFT))->values();

        return view('admin.tata-kelola-keamanan.lembar-validasi', compact('evaluasi', 'sesi', 'butir'));
    }

    private function dataSesi(Request $request, Evaluasi $evaluasi): array
    {
        $data = $request->validate([
            'tanggal' => ['required', 'date'],
            'tempat' => ['nullable', 'string', 'max:255'],
            'peserta' => ['nullable', 'array', 'required_without:peserta_lain'],
            'peserta.*' => [Rule::exists('tata_kelola_keamanan_respondens', 'id')->where('evaluasi_id', $evaluasi->id)],
            'peserta_lain' => ['nullable', 'string', 'max:2000'],
        ], [
            'tanggal.required' => 'Isi tanggal pelaksanaan FGD.',
            'peserta.required_without' => 'Pilih minimal satu informan yang hadir atau tuliskan peserta lain.',
        ]);
        $data['peserta'] = array_values(array_map('intval', array_unique($data['peserta'] ?? [])));

        return $data;
    }
}
