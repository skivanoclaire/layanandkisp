<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\InternetInstallationRequest;
use Illuminate\Http\Request;

class PasangBaruInternetController extends Controller
{
    public function index()
    {
        $requests = InternetInstallationRequest::where('user_id', auth()->id())
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('user.internet.pasang-baru.index', compact('requests'));
    }

    public function create()
    {
        return view('user.internet.pasang-baru.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'no_hp' => 'required|string|max:20',
            'lokasi_pemasangan' => 'required|string|max:1000',
            'ruangan' => 'nullable|string|max:255',
            'pic_lokasi' => 'nullable|string|max:255',
            'jumlah_titik' => 'required|integer|min:1|max:20',
            'uraian_kebutuhan' => 'required|string|max:2000',
        ], [
            'no_hp.required' => 'No. HP/WhatsApp wajib diisi.',
            'lokasi_pemasangan.required' => 'Lokasi pemasangan wajib diisi.',
            'jumlah_titik.required' => 'Jumlah titik wajib diisi.',
            'jumlah_titik.min' => 'Jumlah titik minimal 1.',
            'jumlah_titik.max' => 'Jumlah titik maksimal 20. Untuk kebutuhan lebih besar, hubungi admin.',
            'uraian_kebutuhan.required' => 'Uraian kebutuhan wajib diisi.',
        ]);

        InternetInstallationRequest::create([
            'user_id' => auth()->id(),
            'unit_kerja_id' => auth()->user()->unit_kerja_id,
            'nama' => auth()->user()->name,
            'nip' => auth()->user()->nip,
            'no_hp' => $validated['no_hp'],
            'lokasi_pemasangan' => $validated['lokasi_pemasangan'],
            'ruangan' => $validated['ruangan'] ?? null,
            'pic_lokasi' => $validated['pic_lokasi'] ?? null,
            'jumlah_titik' => $validated['jumlah_titik'],
            'uraian_kebutuhan' => $validated['uraian_kebutuhan'],
            'status' => 'menunggu',
        ]);

        return redirect()->route('user.internet.pasang-baru.index')
            ->with('success', 'Permohonan pasang baru internet berhasil dikirim. Tim kami akan segera menindaklanjuti.');
    }

    public function show(InternetInstallationRequest $pasangBaru)
    {
        abort_unless($pasangBaru->user_id === auth()->id(), 403, 'Unauthorized action.');

        $pasangBaru->load(['unitKerja', 'processedBy']);

        return view('user.internet.pasang-baru.show', ['installation' => $pasangBaru]);
    }
}
