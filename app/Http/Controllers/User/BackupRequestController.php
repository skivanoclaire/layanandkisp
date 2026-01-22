<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\BackupRequest;
use App\Models\UnitKerja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BackupRequestController extends Controller
{
    public function index()
    {
        $requests = BackupRequest::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('user.datacenter.backup.index', compact('requests'));
    }

    public function create()
    {
        $unitKerjas = UnitKerja::forLayananDigital()->orderBy('nama')->get();
        return view('user.datacenter.backup.create', compact('unitKerjas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'unit_kerja_id' => 'required|exists:unit_kerjas,id',
            'backup_virtual_machine' => 'nullable|boolean',
            'backup_aplikasi' => 'nullable|boolean',
            'backup_database' => 'nullable|boolean',
            'jadwal_backup' => 'required|string|max:255',
            'retensi_hari' => 'required|integer|min:1',
            'keterangan' => 'nullable|string',
        ]);

        // Convert checkboxes to boolean
        $validated['backup_virtual_machine'] = $request->has('backup_virtual_machine');
        $validated['backup_aplikasi'] = $request->has('backup_aplikasi');
        $validated['backup_database'] = $request->has('backup_database');

        $validated['user_id'] = Auth::id();
        $validated['nip'] = Auth::user()->nip; // Auto-fill from authenticated user
        $validated['status'] = 'menunggu';

        BackupRequest::create($validated);

        return redirect()->route('user.datacenter.backup.index')
            ->with('success', 'Permohonan backup berhasil diajukan.');
    }

    public function show(BackupRequest $backupRequest)
    {
        if ($backupRequest->user_id !== Auth::id()) {
            abort(403);
        }

        return view('user.datacenter.backup.show', compact('backupRequest'));
    }
}
