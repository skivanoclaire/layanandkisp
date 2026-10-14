<?php

namespace App\Http\Controllers\Admin\TataKelolaKeamanan;

use App\Http\Controllers\Controller;
use App\Models\TataKelolaKeamanan\Aktivitas;
use App\Models\TataKelolaKeamanan\Responden;
use App\Models\TataKelolaKeamanan\Evaluasi;
use App\Models\User;
use App\Services\TataKelolaKeamanan\DesignFactorInput;
use App\Services\TataKelolaKeamanan\RoleModul;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Pendaftaran akun portal sebagai responden evaluasi (pimpinan untuk design factors,
 * informan RACI untuk kuesioner aktivitas) dan pemantauan kemajuan pengisiannya.
 */
class RespondenController extends Controller
{
    public function index(Request $request, Evaluasi $evaluasi, DesignFactorInput $dfInput)
    {
        $respondens = $evaluasi->respondens()->with(['user', 'designFactors'])->withCount('jawabans')->orderBy('jabatan')->get();
        $objektifs = $evaluasi->objektifDipilih()->get();
        $definisi = config('tata_kelola_keamanan.design_factors');

        // Jumlah aktivitas yang menjadi tugas setiap responden
        $aktivitasPerObjektif = Aktivitas::query()
            ->join('tata_kelola_keamanan_praktiks as p', 'p.id', '=', 'tata_kelola_keamanan_aktivitas.praktik_id')
            ->where('p.evaluasi_id', $evaluasi->id)
            ->selectRaw('p.objektif_kode, count(*) as n')->groupBy('p.objektif_kode')
            ->pluck('n', 'objektif_kode');

        $progres = $respondens->mapWithKeys(function (Responden $r) use ($definisi, $dfInput, $aktivitasPerObjektif) {
            $dfLengkap = $r->designFactors->filter(fn ($d) => $dfInput->lengkap($definisi[$d->df_kode] ?? ['tipe' => 'x', 'butir' => []], $d->nilai))->count();
            $tugasAktivitas = collect($r->objektif ?? [])->sum(fn ($k) => $aktivitasPerObjektif[$k] ?? 0);

            return [$r->id => ['df' => $dfLengkap, 'aktivitas_tugas' => $tugasAktivitas]];
        });

        $hasilCari = collect();
        $q = trim((string) $request->query('q'));
        if (mb_strlen($q) >= 3) {
            $sudah = $respondens->pluck('user_id');
            $hasilCari = User::where('is_verified', true)
                ->where(fn ($w) => $w->where('name', 'like', "%$q%")->orWhere('email', 'like', "%$q%")->orWhere('nip', 'like', "%$q%"))
                ->whereNotIn('id', $sudah)
                ->with('unitKerja')
                ->orderBy('name')->limit(20)->get();
        }

        return view('admin.tata-kelola-keamanan.responden', compact('evaluasi', 'respondens', 'objektifs', 'progres', 'hasilCari', 'q', 'definisi'));
    }

    public function store(Request $request, Evaluasi $evaluasi, RoleModul $roleModul)
    {
        $data = $this->validasi($request, $evaluasi, true);
        $responden = $evaluasi->respondens()->create($data);
        $roleModul->pasang($responden->user, RoleModul::RESPONDEN);

        return redirect()->route('admin.tata-kelola-keamanan.responden', $evaluasi)->with('success', 'Responden ditambahkan dan role Responden Tata Kelola Keamanan dipasang pada akunnya. Responden mengisi melalui menu Layanan Digital → Kuesioner Tata Kelola Keamanan.');
    }

    public function update(Request $request, Evaluasi $evaluasi, Responden $responden)
    {
        abort_unless($responden->evaluasi_id === $evaluasi->id, 404);
        $responden->update($this->validasi($request, $evaluasi, false));

        return back()->with('success', 'Data responden diperbarui.');
    }

    public function destroy(Evaluasi $evaluasi, Responden $responden, RoleModul $roleModul)
    {
        abort_unless($responden->evaluasi_id === $evaluasi->id, 404);
        if ($responden->jawabans()->exists() || $responden->designFactors()->exists()) {
            return back()->with('error', 'Responden yang sudah mengisi tidak dapat dihapus agar jejak jawabannya tetap ada. Kosongkan penugasannya saja.');
        }
        $user = $responden->user;
        $responden->delete();
        $roleModul->lepasBilaTidakDipakai($user, RoleModul::RESPONDEN, Responden::where('user_id', $user->id)->exists());

        return back()->with('success', 'Responden dihapus.');
    }

    private function validasi(Request $request, Evaluasi $evaluasi, bool $baru): array
    {
        $rules = [
            'jabatan' => ['required', 'string', 'max:150'],
            'peran_raci' => ['nullable', 'string', 'max:150'],
            'isi_df' => ['boolean'],
            'objektif' => ['nullable', 'array'],
            'objektif.*' => [Rule::in($evaluasi->objektifDipilih()->pluck('kode'))],
        ];
        if ($baru) {
            $rules['user_id'] = [
                'required',
                Rule::exists('users', 'id')->where('is_verified', true),
                Rule::unique('tata_kelola_keamanan_respondens', 'user_id')->where('evaluasi_id', $evaluasi->id),
            ];
        }
        $data = $request->validate($rules, ['user_id.unique' => 'Akun ini sudah terdaftar sebagai responden evaluasi ini.']);
        $data['isi_df'] = $request->boolean('isi_df');
        $data['objektif'] = array_values($data['objektif'] ?? []);

        return $data;
    }
}
