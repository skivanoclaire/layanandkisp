<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\TataKelolaKeamanan\Aktivitas;
use App\Models\TataKelolaKeamanan\DesignFactor;
use App\Models\TataKelolaKeamanan\JawabanAktivitas;
use App\Models\TataKelolaKeamanan\Praktik;
use App\Models\TataKelolaKeamanan\Responden;
use App\Models\TataKelolaKeamanan\Evaluasi;
use App\Services\TataKelolaKeamanan\DesignFactorInput;
use App\Models\TataKelolaKeamanan\LampiranJawaban;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Halaman responden (akun portal) untuk mengisi kuesioner tata kelola keamanan
 * informasi: design factors (pimpinan) dan kuesioner aktivitas (informan RACI).
 * Hanya akun yang didaftarkan pengelola pada evaluasi yang dapat mengisi.
 */
class TataKelolaKeamananRespondenController extends Controller
{
    public function index(Request $request, DesignFactorInput $dfInput)
    {
        $respondens = Responden::where('user_id', $request->user()->id)
            ->with(['evaluasi', 'designFactors'])->withCount('jawabans')->get();
        $definisi = config('tata_kelola_keamanan.design_factors');

        $ringkasan = $respondens->mapWithKeys(function (Responden $r) use ($definisi, $dfInput) {
            $dfLengkap = $r->designFactors->filter(fn ($d) => $dfInput->lengkap($definisi[$d->df_kode] ?? ['tipe' => 'x', 'butir' => []], $d->nilai))->count();
            $tugas = $r->objektifTugas();
            $jumlahAktivitas = $tugas
                ? Aktivitas::whereIn('praktik_id', Praktik::where('evaluasi_id', $r->evaluasi_id)->whereIn('objektif_kode', $tugas)->pluck('id'))->count()
                : 0;

            return [$r->id => ['df' => $dfLengkap, 'objektif' => $tugas, 'aktivitas' => $jumlahAktivitas]];
        });

        return view('user.tata-kelola-keamanan.index', compact('respondens', 'ringkasan', 'definisi'));
    }

    public function designFactors(Request $request, Evaluasi $evaluasi)
    {
        $responden = $this->responden($request, $evaluasi);
        abort_unless($responden->isi_df, 403, 'Anda tidak ditugaskan mengisi kuesioner design factors.');

        $definisi = config('tata_kelola_keamanan.design_factors');
        $isian = $responden->designFactors()->get()->keyBy('df_kode');

        return view('user.tata-kelola-keamanan.design-factors', compact('evaluasi', 'responden', 'definisi', 'isian'));
    }

    public function simpanDesignFactors(Request $request, Evaluasi $evaluasi, DesignFactorInput $input)
    {
        $responden = $this->responden($request, $evaluasi);
        abort_unless($responden->isi_df, 403);
        $request->validate(['nilai' => ['array'], 'catatan' => ['array']]);

        $label = $responden->label();
        DB::transaction(function () use ($request, $evaluasi, $responden, $input, $label) {
            foreach ($input->bersihkanSemua((array) $request->input('nilai', [])) as $kode => $nilai) {
                DesignFactor::updateOrCreate(
                    ['evaluasi_id' => $evaluasi->id, 'df_kode' => $kode, 'responden_id' => $responden->id],
                    ['responden' => $label, 'nilai' => $nilai, 'catatan' => $request->input("catatan.$kode")]
                );
            }
        });

        return redirect()->route('tata-kelola-keamanan.responden.design-factors', $evaluasi)
            ->with('success', 'Jawaban tersimpan. Anda dapat kembali dan mengubahnya kapan saja selama evaluasi berjalan.');
    }

    public function aktivitas(Request $request, Evaluasi $evaluasi)
    {
        $responden = $this->responden($request, $evaluasi);
        $tugas = $responden->objektifTugas();
        abort_if(empty($tugas), 403, 'Anda belum ditugaskan mengisi kuesioner aktivitas.');

        $kode = $request->query('objektif', $tugas[0]);
        abort_unless(in_array($kode, $tugas, true), 404);

        $praktiks = Praktik::where('evaluasi_id', $evaluasi->id)->where('objektif_kode', $kode)
            ->with('aktivitas')->orderBy('urutan')->orderBy('kode')->get();
        $jawaban = JawabanAktivitas::where('responden_id', $responden->id)
            ->whereIn('aktivitas_id', $praktiks->flatMap->aktivitas->pluck('id'))
            ->with('lampirans')->get()->keyBy('aktivitas_id');

        return view('user.tata-kelola-keamanan.aktivitas', compact('evaluasi', 'responden', 'tugas', 'kode', 'praktiks', 'jawaban'));
    }

    public function simpanAktivitas(Request $request, Evaluasi $evaluasi)
    {
        $responden = $this->responden($request, $evaluasi);
        $tugas = $responden->objektifTugas();
        abort_if(empty($tugas), 403);

        $request->validate([
            'objektif' => ['required', Rule::in($tugas)],
            'jawaban' => ['array'],
            'jawaban.*' => ['nullable', Rule::in(array_keys(JawabanAktivitas::JAWABAN))],
            'keterangan' => ['array'],
            'keterangan.*' => ['nullable', 'string', 'max:2000'],
            'lampiran' => ['array'],
            'lampiran.*' => ['array', 'max:'.LampiranJawaban::MAKS_PER_AKTIVITAS],
            'lampiran.*.*' => ['file', 'max:'.LampiranJawaban::MAKS_KB, 'mimes:'.LampiranJawaban::TIPE],
        ], [
            'lampiran.*.max' => 'Paling banyak '.LampiranJawaban::MAKS_PER_AKTIVITAS.' berkas per aktivitas.',
            'lampiran.*.*.max' => 'Ukuran berkas paling besar 10 MB.',
            'lampiran.*.*.mimes' => 'Tipe berkas harus PDF, JPG, PNG, Word, atau Excel.',
        ]);

        $izin = Aktivitas::whereIn('praktik_id', Praktik::where('evaluasi_id', $evaluasi->id)
            ->where('objektif_kode', $request->input('objektif'))->pluck('id'))->pluck('id')->all();
        $jawabanInput = (array) $request->input('jawaban', []);
        $berkasInput = (array) $request->file('lampiran', []);

        // Berkas hanya diterima untuk aktivitas tugas yang dijawab, dan total per aktivitas tidak melebihi batas
        foreach ($berkasInput as $aktivitasId => $berkas) {
            if (! in_array((int) $aktivitasId, $izin, true) || empty($jawabanInput[$aktivitasId])) {
                throw ValidationException::withMessages(['lampiran' => 'Pilih jawaban terlebih dahulu sebelum mengunggah berkas untuk aktivitas tersebut.']);
            }
            $sudahAda = LampiranJawaban::whereHas('jawaban', fn ($q) => $q->where('aktivitas_id', (int) $aktivitasId)->where('responden_id', $responden->id))->count();
            if ($sudahAda + count($berkas) > LampiranJawaban::MAKS_PER_AKTIVITAS) {
                throw ValidationException::withMessages(['lampiran' => 'Paling banyak '.LampiranJawaban::MAKS_PER_AKTIVITAS.' berkas per aktivitas. Hapus berkas lama bila ingin mengganti.']);
            }
        }

        $disimpan = 0;
        $folder = Evaluasi::folderBerkas($evaluasi->id)."/jawaban/{$responden->id}";

        DB::transaction(function () use ($request, $responden, $izin, $jawabanInput, $berkasInput, $folder, &$disimpan) {
            foreach ($jawabanInput as $aktivitasId => $jawaban) {
                if (! $jawaban || ! in_array((int) $aktivitasId, $izin, true)) {
                    continue;
                }
                $record = JawabanAktivitas::updateOrCreate(
                    ['aktivitas_id' => (int) $aktivitasId, 'responden_id' => $responden->id],
                    ['jawaban' => $jawaban, 'keterangan' => $request->input("keterangan.$aktivitasId")]
                );
                foreach ($berkasInput[$aktivitasId] ?? [] as $file) {
                    $record->lampirans()->create([
                        'path' => $file->store($folder, 'local'),
                        'nama_asli' => mb_substr($file->getClientOriginalName(), 0, 255),
                        'mime' => $file->getClientMimeType(),
                        'ukuran' => $file->getSize(),
                    ]);
                }
                $disimpan++;
            }
        });

        return redirect()->route('tata-kelola-keamanan.responden.aktivitas', [$evaluasi, 'objektif' => $request->input('objektif')])
            ->with('success', "$disimpan jawaban tersimpan. Terima kasih.");
    }

    public function unduhLampiran(Request $request, Evaluasi $evaluasi, LampiranJawaban $lampiran)
    {
        $this->pastikanPemilik($request, $evaluasi, $lampiran);
        abort_unless(Storage::disk('local')->exists($lampiran->path), 404);

        return Storage::disk('local')->download($lampiran->path, $lampiran->nama_asli);
    }

    public function hapusLampiran(Request $request, Evaluasi $evaluasi, LampiranJawaban $lampiran)
    {
        $this->pastikanPemilik($request, $evaluasi, $lampiran);
        $objektif = $lampiran->jawaban->aktivitas->praktik->objektif_kode ?? null;
        $lampiran->delete();

        return redirect()->route('tata-kelola-keamanan.responden.aktivitas', [$evaluasi, 'objektif' => $objektif])
            ->with('success', 'Berkas dihapus.');
    }

    private function pastikanPemilik(Request $request, Evaluasi $evaluasi, LampiranJawaban $lampiran): void
    {
        $responden = $this->responden($request, $evaluasi);
        abort_unless($lampiran->jawaban && $lampiran->jawaban->responden_id === $responden->id, 404);
    }

    private function responden(Request $request, Evaluasi $evaluasi): Responden
    {
        $responden = Responden::where('evaluasi_id', $evaluasi->id)->where('user_id', $request->user()->id)->with(['evaluasi', 'user'])->first();
        abort_unless($responden, 403, 'Akun Anda tidak terdaftar sebagai responden evaluasi ini.');

        return $responden;
    }
}
