<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\StarlinkRequest;
use App\Models\StarlinkServiceSetting;
use App\Models\UnitKerja;
use Illuminate\Http\Request;

class StarlinkController extends Controller
{
    public function index()
    {
        $requests = StarlinkRequest::with(['unitKerja'])
            ->where('user_id', auth()->id())
            ->orderByDesc('created_at')
            ->paginate(10);

        $serviceSetting = StarlinkServiceSetting::first();

        return view('user.internet.starlink.index', compact('requests', 'serviceSetting'));
    }

    public function create()
    {
        // Check if service is active
        $serviceSetting = StarlinkServiceSetting::first();
        if (!$serviceSetting || !$serviceSetting->is_active) {
            return redirect()->route('user.internet.starlink.index')
                ->with('error', 'Layanan Starlink saat ini tidak tersedia. ' . ($serviceSetting->inactive_reason ?? ''));
        }

        $unitKerjaList = UnitKerja::orderBy('nama')->get();
        return view('user.internet.starlink.create', compact('unitKerjaList'));
    }

    public function store(Request $request)
    {
        // Check if service is active
        $serviceSetting = StarlinkServiceSetting::first();
        if (!$serviceSetting || !$serviceSetting->is_active) {
            return redirect()->route('user.internet.starlink.index')
                ->with('error', 'Layanan Starlink saat ini tidak tersedia.');
        }

        $validated = $request->validate([
            'unit_kerja_id' => 'required|exists:unit_kerjas,id',
            'no_hp' => 'required|string|max:20',
            'uraian_kegiatan' => 'required|string',
            'tanggal_mulai' => 'required|date|after_or_equal:today',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required',
        ], [
            'unit_kerja_id.required' => 'Instansi wajib dipilih.',
            'no_hp.required' => 'No. HP/WhatsApp wajib diisi.',
            'uraian_kegiatan.required' => 'Uraian kegiatan wajib diisi.',
            'tanggal_mulai.required' => 'Tanggal mulai wajib diisi.',
            'tanggal_mulai.after_or_equal' => 'Tanggal mulai tidak boleh kurang dari hari ini.',
            'tanggal_selesai.required' => 'Tanggal selesai wajib diisi.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai tidak boleh kurang dari tanggal mulai.',
            'jam_mulai.required' => 'Jam mulai wajib diisi.',
            'jam_selesai.required' => 'Jam selesai wajib diisi.',
        ]);

        StarlinkRequest::create([
            'user_id' => auth()->id(),
            'nama' => auth()->user()->name,
            'nip' => auth()->user()->nip,
            'no_hp' => $validated['no_hp'],
            'unit_kerja_id' => $validated['unit_kerja_id'],
            'uraian_kegiatan' => $validated['uraian_kegiatan'],
            'tanggal_mulai' => $validated['tanggal_mulai'],
            'tanggal_selesai' => $validated['tanggal_selesai'],
            'jam_mulai' => $validated['jam_mulai'],
            'jam_selesai' => $validated['jam_selesai'],
            'status' => 'menunggu',
        ]);

        return redirect()->route('user.internet.starlink.index')
            ->with('success', 'Permohonan Starlink Jelajah berhasil dikirim. Tim kami akan segera menindaklanjuti.');
    }

    public function show(StarlinkRequest $starlinkRequest)
    {
        // Ensure user can only view their own requests
        if ($starlinkRequest->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $starlinkRequest->load(['unitKerja', 'processedBy']);
        return view('user.internet.starlink.show', compact('starlinkRequest'));
    }
}
