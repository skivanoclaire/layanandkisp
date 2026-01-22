<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\CloudStorageRequest;
use App\Models\UnitKerja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CloudStorageRequestController extends Controller
{
    public function index()
    {
        $requests = CloudStorageRequest::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('user.datacenter.cloud-storage.index', compact('requests'));
    }

    public function create()
    {
        $unitKerjas = UnitKerja::forLayananDigital()->orderBy('nama')->get();
        return view('user.datacenter.cloud-storage.create', compact('unitKerjas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'unit_kerja_id' => 'required|exists:unit_kerjas,id',
            'kapasitas_gb' => 'required|integer|min:1',
            'tipe' => 'required|in:Internal Cloud (Synology),GoogleDrive',
            'keterangan' => 'nullable|string',
        ]);

        $validated['user_id'] = Auth::id();
        $validated['nip'] = Auth::user()->nip; // Auto-fill from authenticated user
        $validated['status'] = 'menunggu';

        CloudStorageRequest::create($validated);

        return redirect()->route('user.datacenter.cloud-storage.index')
            ->with('success', 'Permohonan cloud storage berhasil diajukan.');
    }

    public function show(CloudStorageRequest $cloudStorageRequest)
    {
        if ($cloudStorageRequest->user_id !== Auth::id()) {
            abort(403);
        }

        return view('user.datacenter.cloud-storage.show', compact('cloudStorageRequest'));
    }
}
