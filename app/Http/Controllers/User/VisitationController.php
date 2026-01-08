<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Visitation;
use App\Models\UnitKerja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VisitationController extends Controller
{
    public function index()
    {
        $requests = Visitation::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('user.datacenter.visitation.index', compact('requests'));
    }

    public function create()
    {
        $unitKerjas = UnitKerja::orderBy('nama')->get();
        return view('user.datacenter.visitation.create', compact('unitKerjas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'unit_kerja_id' => 'required|exists:unit_kerjas,id',
            'tujuan_kunjungan' => 'required|in:Kunjungan & Inspeksi Formal,Penempatan Aset,Pengambilan Aset',
            'nama_aset' => 'nullable|string|max:255',
            'nomor_aset' => 'nullable|string|max:255',
            'catatan_aset' => 'nullable|string',
            'tanggal_kunjungan' => 'required|date',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required',
            'keterangan' => 'nullable|string',
        ]);

        $validated['user_id'] = Auth::id();
        $validated['nip'] = Auth::user()->nip; // Auto-fill from authenticated user
        $validated['status'] = 'menunggu';

        Visitation::create($validated);

        return redirect()->route('user.datacenter.visitation.index')
            ->with('success', 'Permohonan kunjungan berhasil diajukan.');
    }

    public function show(Visitation $visitation)
    {
        if ($visitation->user_id !== Auth::id()) {
            abort(403);
        }

        return view('user.datacenter.visitation.show', compact('visitation'));
    }
}
