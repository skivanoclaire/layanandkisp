<?php

namespace App\Http\Controllers\Admin\TataKelolaKeamanan;

use App\Exports\TataKelolaKeamananExport;
use App\Http\Controllers\Controller;
use App\Models\TataKelolaKeamanan\Pemetaan;
use App\Models\TataKelolaKeamanan\Evaluasi;
use App\Services\TataKelolaKeamanan\Laporan;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Daftar evaluasi, ringkasan kemajuan tahapan, laporan, dan ekspor.
 */
class EvaluasiController extends Controller
{
    public function index()
    {
        $evaluasis = Evaluasi::withCount(['objektifs as jumlah_dipilih' => fn ($q) => $q->where('dipilih', true), 'pemetaans', 'pakars'])
            ->latest('id')->get();

        return view('admin.tata-kelola-keamanan.index', compact('evaluasis'));
    }

    public function panduan()
    {
        return view('admin.tata-kelola-keamanan.panduan');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'instansi' => ['nullable', 'string', 'max:255'],
            'keterangan' => ['nullable', 'string', 'max:2000'],
        ]);

        $evaluasi = Evaluasi::create($data);

        return redirect()->route('admin.tata-kelola-keamanan.show', $evaluasi)->with('success', 'Evaluasi dibuat.');
    }

    public function show(Evaluasi $evaluasi, Laporan $laporan)
    {
        $tahapan = $laporan->kemajuan($evaluasi);

        return view('admin.tata-kelola-keamanan.show', compact('evaluasi', 'tahapan'));
    }

    public function update(Request $request, Evaluasi $evaluasi)
    {
        $evaluasi->update($request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'instansi' => ['nullable', 'string', 'max:255'],
            'keterangan' => ['nullable', 'string', 'max:2000'],
        ]));

        return back()->with('success', 'Data evaluasi diperbarui.');
    }

    public function destroy(Request $request, Evaluasi $evaluasi)
    {
        $request->validate(['konfirmasi' => ['required', 'in:'.$evaluasi->nama]], [
            'konfirmasi.in' => 'Ketik nama evaluasi persis untuk menghapus.',
        ]);
        $evaluasi->delete();

        return redirect()->route('admin.tata-kelola-keamanan.index')->with('success', 'Evaluasi dihapus.');
    }

    public function laporan(Evaluasi $evaluasi, Laporan $laporan)
    {
        $data = $laporan->lengkap($evaluasi);

        return view('admin.tata-kelola-keamanan.laporan', array_merge(['evaluasi' => $evaluasi], $data));
    }

    public function export(Evaluasi $evaluasi)
    {
        $nama = 'tata-kelola-keamanan-'.$evaluasi->id.'-'.now()->format('Ymd-His').'.xlsx';

        return Excel::download(new TataKelolaKeamananExport($evaluasi), $nama);
    }

    public static function labelStatusPemetaan(string $status): string
    {
        return [
            Pemetaan::STATUS_DRAF => 'Draf',
            Pemetaan::STATUS_DISEPAKATI => 'Disepakati',
            Pemetaan::STATUS_DIKELUARKAN => 'Dikeluarkan',
        ][$status] ?? $status;
    }
}
