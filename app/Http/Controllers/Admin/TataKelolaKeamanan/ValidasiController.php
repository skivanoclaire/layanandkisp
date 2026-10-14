<?php

namespace App\Http\Controllers\Admin\TataKelolaKeamanan;

use App\Http\Controllers\Controller;
use App\Models\TataKelolaKeamanan\Evaluasi;
use App\Models\TataKelolaKeamanan\Pakar;
use App\Models\TataKelolaKeamanan\Putaran;
use App\Models\User;
use App\Services\TataKelolaKeamanan\Delphi;
use App\Services\TataKelolaKeamanan\RoleModul;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use RuntimeException;

/**
 * Tahap 4: panel pakar (akun portal), putaran penilaian (prinsip Delphi), dan rekap kesepakatan.
 */
class ValidasiController extends Controller
{
    public function index(Request $request, Evaluasi $evaluasi, Delphi $delphi)
    {
        $pakars = $evaluasi->pakars()->with('user')->withCount('penilaians')->get()->sortBy('nama')->values();
        $putarans = $evaluasi->putarans()->orderBy('nomor')->get();
        $putaran = $request->filled('putaran')
            ? $putarans->firstWhere('nomor', (int) $request->query('putaran'))
            : $putarans->last();
        $rekap = $putaran ? $delphi->rekap($putaran) : collect();
        $sudahMenilai = $putaran
            ? $putaran->penilaians()->select('pakar_id')->distinct()->pluck('pakar_id')->all()
            : [];

        $hasilCari = collect();
        $q = trim((string) $request->query('q'));
        if (mb_strlen($q) >= 3) {
            $hasilCari = User::where('is_verified', true)
                ->where(fn ($w) => $w->where('name', 'like', "%$q%")->orWhere('email', 'like', "%$q%")->orWhere('nip', 'like', "%$q%"))
                ->whereNotIn('id', $pakars->pluck('user_id'))
                ->orderBy('name')->limit(20)->get();
        }

        return view('admin.tata-kelola-keamanan.validasi', compact('evaluasi', 'pakars', 'putarans', 'putaran', 'rekap', 'sudahMenilai', 'hasilCari', 'q'));
    }

    public function pakarStore(Request $request, Evaluasi $evaluasi, RoleModul $roleModul)
    {
        $data = $request->validate([
            'user_id' => [
                'required',
                Rule::exists('users', 'id')->where('is_verified', true),
                Rule::unique('tata_kelola_keamanan_pakars', 'user_id')->where('evaluasi_id', $evaluasi->id),
            ],
            'instansi' => ['nullable', 'string', 'max:255'],
            'keahlian' => ['nullable', 'string', 'max:255'],
        ], ['user_id.unique' => 'Akun ini sudah terdaftar sebagai pakar evaluasi ini.']);

        $pakar = $evaluasi->pakars()->create($data);
        $roleModul->pasang($pakar->user, RoleModul::PAKAR);

        return redirect()->route('admin.tata-kelola-keamanan.validasi', $evaluasi)
            ->with('success', "{$pakar->nama} ditambahkan sebagai pakar dan role Pakar Tata Kelola Keamanan dipasang pada akunnya.");
    }

    public function pakarUpdate(Request $request, Evaluasi $evaluasi, Pakar $pakar)
    {
        abort_unless($pakar->evaluasi_id === $evaluasi->id, 404);
        $pakar->update($request->validate([
            'instansi' => ['nullable', 'string', 'max:255'],
            'keahlian' => ['nullable', 'string', 'max:255'],
            'aktif' => ['boolean'],
        ]));

        return back()->with('success', 'Data pakar diperbarui.');
    }

    public function pakarDestroy(Evaluasi $evaluasi, Pakar $pakar, RoleModul $roleModul)
    {
        abort_unless($pakar->evaluasi_id === $evaluasi->id, 404);
        if ($pakar->penilaians()->exists()) {
            return back()->with('error', 'Pakar yang sudah memberi penilaian tidak dapat dihapus; nonaktifkan saja.');
        }
        $user = $pakar->user;
        $pakar->delete();
        $roleModul->lepasBilaTidakDipakai($user, RoleModul::PAKAR, Pakar::where('user_id', $user->id)->exists());

        return back()->with('success', 'Pakar dihapus.');
    }

    public function bukaPutaran(Evaluasi $evaluasi, Delphi $delphi)
    {
        try {
            $putaran = $delphi->bukaPutaran($evaluasi);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.tata-kelola-keamanan.validasi', [$evaluasi, 'putaran' => $putaran->nomor])
            ->with('success', "Putaran {$putaran->nomor} dibuka. Pakar menilai melalui menu Validasi Pakar Tata Kelola Keamanan di akunnya.");
    }

    public function tutupPutaran(Evaluasi $evaluasi, Putaran $putaran, Delphi $delphi)
    {
        abort_unless($putaran->evaluasi_id === $evaluasi->id, 404);
        try {
            $hasil = $delphi->tutupPutaran($putaran);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        $pesan = "Putaran {$putaran->nomor} ditutup: {$hasil['disepakati']} butir disepakati, {$hasil['revisi']} perlu direvisi";
        if ($hasil['dikeluarkan']) {
            $pesan .= ", {$hasil['dikeluarkan']} dikeluarkan dari model";
        }

        return back()->with('success', $pesan.'.');
    }
}
