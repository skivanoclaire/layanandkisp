<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\LaporanGangguan;
use App\Models\UnitKerja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LaporanGangguanController extends Controller
{
    public function index()
    {
        $laporans = LaporanGangguan::with(['unitKerja'])
            ->where('user_id', auth()->id())
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('user.internet.laporan-gangguan.index', compact('laporans'));
    }

    public function create()
    {
        $unitKerjaList = UnitKerja::forLayananDigital()->orderBy('nama')->get();
        return view('user.internet.laporan-gangguan.create', compact('unitKerjaList'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'unit_kerja_id' => 'required|exists:unit_kerjas,id',
            'no_hp' => 'required|string|max:20',
            'uraian_permasalahan' => 'required|string',
            'lokasi_koordinat' => 'nullable|string|max:255',
            'lampiran_foto' => 'nullable|array|max:5',
            'lampiran_foto.*' => 'image|mimes:jpeg,png,jpg|max:2048',
        ], [
            'unit_kerja_id.required' => 'Instansi wajib dipilih.',
            'no_hp.required' => 'No. HP/WhatsApp wajib diisi.',
            'uraian_permasalahan.required' => 'Uraian permasalahan wajib diisi.',
            'lampiran_foto.max' => 'Maksimal 5 foto yang dapat diunggah.',
            'lampiran_foto.*.image' => 'File harus berupa gambar.',
            'lampiran_foto.*.mimes' => 'Format gambar harus: jpeg, png, atau jpg.',
            'lampiran_foto.*.max' => 'Ukuran maksimal gambar adalah 2MB.',
        ]);

        $fotoPaths = [];
        if ($request->hasFile('lampiran_foto')) {
            foreach ($request->file('lampiran_foto') as $file) {
                $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('laporan-gangguan', $fileName, 'public');
                $fotoPaths[] = $path;
            }
        }

        LaporanGangguan::create([
            'user_id' => auth()->id(),
            'nama' => auth()->user()->name,
            'nip' => auth()->user()->nip,
            'no_hp' => $validated['no_hp'],
            'unit_kerja_id' => $validated['unit_kerja_id'],
            'uraian_permasalahan' => $validated['uraian_permasalahan'],
            'lokasi_koordinat' => $validated['lokasi_koordinat'],
            'lampiran_foto' => $fotoPaths,
            'status' => 'menunggu',
        ]);

        return redirect()->route('user.internet.laporan-gangguan.index')
            ->with('success', 'Laporan gangguan berhasil dikirim. Tim kami akan segera menindaklanjuti.');
    }

    public function show(LaporanGangguan $laporanGangguan)
    {
        // Ensure user can only view their own reports
        if ($laporanGangguan->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $laporanGangguan->load(['unitKerja', 'processedBy']);
        return view('user.internet.laporan-gangguan.show', compact('laporanGangguan'));
    }
}
