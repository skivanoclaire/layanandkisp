<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\VpsRequest;
use App\Models\UnitKerja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VpsRequestController extends Controller
{
    public function index()
    {
        $requests = VpsRequest::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('user.datacenter.vps.index', compact('requests'));
    }

    public function create()
    {
        $unitKerjas = UnitKerja::forLayananDigital()->orderBy('nama')->get();
        return view('user.datacenter.vps.create', compact('unitKerjas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'unit_kerja_id' => 'required|exists:unit_kerjas,id',
            'vcpu' => 'required|integer|min:1',
            'jumlah_socket' => 'required|integer|min:1',
            'vcpu_per_socket' => 'required|integer|min:1',
            'ram_gb' => 'required|integer|min:1',
            'storage_gb' => 'required|integer|min:1',
            'keterangan' => 'nullable|string',
        ]);

        $validated['user_id'] = Auth::id();
        $validated['nip'] = Auth::user()->nip; // Auto-fill from authenticated user
        $validated['status'] = 'menunggu';

        VpsRequest::create($validated);

        return redirect()->route('user.datacenter.vps.index')
            ->with('success', 'Permohonan VPS berhasil diajukan.');
    }

    public function show(VpsRequest $vpsRequest)
    {
        if ($vpsRequest->user_id !== Auth::id()) {
            abort(403);
        }

        return view('user.datacenter.vps.show', compact('vpsRequest'));
    }
}
