<?php

namespace App\Http\Controllers\Admin\TataKelolaKeamanan;

use App\Http\Controllers\Controller;
use App\Models\TataKelolaKeamanan\Aktivitas;
use App\Models\TataKelolaKeamanan\Kontrol;
use App\Models\TataKelolaKeamanan\LampiranJawaban;
use App\Models\TataKelolaKeamanan\Praktik;
use App\Models\TataKelolaKeamanan\Evaluasi;
use App\Services\TataKelolaKeamanan\Kapabilitas;
use App\Services\TataKelolaKeamanan\Laporan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/**
 * Tahap 5: asesmen tingkat kapabilitas (aktivitas per tingkat, terpenuhi/belum berdasarkan
 * bukti) dan status penerapan kontrol ISO/IEC 27001:2022 yang terpetakan.
 */
class AsesmenController extends Controller
{
    public function index(Request $request, Evaluasi $evaluasi, Kapabilitas $kapabilitas)
    {
        $objektifs = $evaluasi->objektifDipilih()->get();
        $kode = $request->query('objektif', $objektifs->first()?->kode);
        $objektif = $objektifs->firstWhere('kode', $kode);

        $praktiks = $objektif
            ? $evaluasi->praktiks()->where('objektif_kode', $objektif->kode)->with(['aktivitas.jawabans.responden.user', 'aktivitas.jawabans.lampirans'])->orderBy('urutan')->orderBy('kode')->get()
            : collect();
        $hasil = $kapabilitas->hitung($praktiks->flatMap->aktivitas);
        $ringkasan = $kapabilitas->ringkasanEvaluasi($evaluasi)->keyBy(fn ($r) => $r['objektif']->kode);

        return view('admin.tata-kelola-keamanan.asesmen', compact('evaluasi', 'objektifs', 'objektif', 'praktiks', 'hasil', 'ringkasan'));
    }

    public function aktivitasStore(Request $request, Evaluasi $evaluasi)
    {
        $data = $request->validate([
            'praktik_id' => ['required', Rule::exists('tata_kelola_keamanan_praktiks', 'id')->where('evaluasi_id', $evaluasi->id)],
            'level' => ['required', 'integer', Rule::in(config('tata_kelola_keamanan.aturan.level_dinilai'))],
            'uraian' => ['required', 'string', 'max:2000'],
            'bukti_diharapkan' => ['nullable', 'string', 'max:1000'],
        ]);
        $data['urutan'] = (int) Aktivitas::where('praktik_id', $data['praktik_id'])->max('urutan') + 1;
        Aktivitas::create($data);

        return back()->with('success', 'Aktivitas ditambahkan.');
    }

    /**
     * Impor banyak aktivitas sekaligus. Satu baris per aktivitas:
     * KODE_PRAKTIK | LEVEL | uraian aktivitas | bukti yang diharapkan (opsional)
     */
    public function aktivitasImpor(Request $request, Evaluasi $evaluasi)
    {
        $request->validate(['teks' => ['required', 'string', 'max:100000']]);
        $praktiks = $evaluasi->praktiks()->pluck('id', 'kode');
        $levels = config('tata_kelola_keamanan.aturan.level_dinilai');
        $berhasil = 0;
        $gagal = [];

        DB::transaction(function () use ($request, $praktiks, $levels, &$berhasil, &$gagal) {
            foreach (preg_split('/\r\n|\r|\n/', $request->input('teks')) as $no => $baris) {
                if (trim($baris) === '') {
                    continue;
                }
                $kolom = array_map('trim', explode('|', $baris));
                $praktikId = $praktiks[$kolom[0] ?? ''] ?? null;
                $level = (int) ($kolom[1] ?? 0);
                $uraian = $kolom[2] ?? '';
                if (! $praktikId || ! in_array($level, $levels, true) || $uraian === '') {
                    $gagal[] = $no + 1;

                    continue;
                }
                Aktivitas::create([
                    'praktik_id' => $praktikId,
                    'level' => $level,
                    'uraian' => mb_substr($uraian, 0, 2000),
                    'bukti_diharapkan' => isset($kolom[3]) ? mb_substr($kolom[3], 0, 1000) : null,
                    'urutan' => (int) Aktivitas::where('praktik_id', $praktikId)->max('urutan') + 1,
                ]);
                $berhasil++;
            }
        });

        $pesan = "$berhasil aktivitas diimpor.";
        if ($gagal) {
            return back()->with('warning', $pesan.' Baris tidak valid (kode praktik/level/uraian): '.implode(', ', $gagal).'.');
        }

        return back()->with('success', $pesan);
    }

    public function aktivitasNilai(Request $request, Evaluasi $evaluasi, Aktivitas $aktivitas)
    {
        $this->pastikanMilik($evaluasi, $aktivitas);
        $data = $request->validate([
            'terpenuhi' => ['nullable', Rule::in(['1', '0', ''])],
            'bukti_ditemukan' => ['nullable', 'string', 'max:2000'],
            'sumber_bukti' => ['nullable', 'array'],
            'sumber_bukti.*' => [Rule::in(array_keys(config('tata_kelola_keamanan.sumber_bukti')))],
            'informan' => ['nullable', 'string', 'max:255'],
            'lampiran' => ['nullable', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx'],
        ]);

        $update = [
            'terpenuhi' => ($data['terpenuhi'] ?? '') === '' ? null : $data['terpenuhi'] === '1',
            'bukti_ditemukan' => $data['bukti_ditemukan'] ?? null,
            'sumber_bukti' => $data['sumber_bukti'] ?? [],
            'informan' => $data['informan'] ?? null,
            'dinilai_at' => ($data['terpenuhi'] ?? '') === '' ? null : now(),
        ];
        if ($request->hasFile('lampiran')) {
            if ($aktivitas->lampiran_path) {
                Storage::disk('local')->delete($aktivitas->lampiran_path);
            }
            $update['lampiran_path'] = $request->file('lampiran')->store("tata-kelola-keamanan/{$evaluasi->id}/bukti", 'local');
        }
        $aktivitas->update($update);

        return back()->with('success', 'Penilaian aktivitas disimpan.');
    }

    public function aktivitasDestroy(Evaluasi $evaluasi, Aktivitas $aktivitas)
    {
        $this->pastikanMilik($evaluasi, $aktivitas);
        if ($aktivitas->jawabans()->exists()) {
            return back()->with('error', 'Aktivitas yang sudah dijawab informan tidak dapat dihapus.');
        }
        if ($aktivitas->lampiran_path) {
            Storage::disk('local')->delete($aktivitas->lampiran_path);
        }
        $aktivitas->delete();

        return back()->with('success', 'Aktivitas dihapus.');
    }

    public function lampiran(Evaluasi $evaluasi, Aktivitas $aktivitas)
    {
        $this->pastikanMilik($evaluasi, $aktivitas);
        abort_unless($aktivitas->lampiran_path && Storage::disk('local')->exists($aktivitas->lampiran_path), 404);

        return Storage::disk('local')->download($aktivitas->lampiran_path);
    }

    /** Unduh berkas bukti yang diunggah informan. */
    public function lampiranJawaban(Evaluasi $evaluasi, LampiranJawaban $lampiran)
    {
        abort_unless($lampiran->evaluasiId() === $evaluasi->id, 404);
        abort_unless(Storage::disk('local')->exists($lampiran->path), 404);

        return Storage::disk('local')->download($lampiran->path, $lampiran->nama_asli);
    }

    public function kontrol(Evaluasi $evaluasi, Laporan $laporan)
    {
        $cakupan = $laporan->cakupanKontrol($evaluasi);
        $status = $evaluasi->kontrols()->get()->keyBy('iso_kode');

        return view('admin.tata-kelola-keamanan.kontrol', compact('evaluasi', 'cakupan', 'status'));
    }

    public function kontrolSimpan(Request $request, Evaluasi $evaluasi)
    {
        $request->validate([
            'status' => ['array'],
            'status.*' => ['nullable', Rule::in(array_keys(config('tata_kelola_keamanan.status_kontrol')))],
            'bukti' => ['array'],
            'bukti.*' => ['nullable', 'string', 'max:2000'],
        ]);
        $kodeValid = array_keys(config('tata_kelola_keamanan.iso_kontrol'));

        foreach ((array) $request->input('status', []) as $kode => $status) {
            if (! in_array($kode, $kodeValid, true)) {
                continue;
            }
            Kontrol::updateOrCreate(
                ['evaluasi_id' => $evaluasi->id, 'iso_kode' => $kode],
                ['status' => $status ?: null, 'bukti' => $request->input("bukti.$kode")]
            );
        }

        return back()->with('success', 'Status kontrol disimpan.');
    }

    private function pastikanMilik(Evaluasi $evaluasi, Aktivitas $aktivitas): void
    {
        abort_unless(Praktik::whereKey($aktivitas->praktik_id)->where('evaluasi_id', $evaluasi->id)->exists(), 404);
    }
}
