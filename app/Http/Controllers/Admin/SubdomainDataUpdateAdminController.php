<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Database;
use App\Models\Framework;
use App\Models\ProgrammingLanguage;
use App\Models\ServerLocation;
use App\Models\SubdomainDataUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SubdomainDataUpdateAdminController extends Controller
{
    /**
     * Daftar seluruh permohonan pembaruan data.
     */
    public function index(Request $request)
    {
        $query = SubdomainDataUpdateRequest::with(['user', 'webMonitor', 'processedBy']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                  ->orWhereHas('webMonitor', function ($wm) use ($search) {
                      $wm->where('subdomain', 'like', "%{$search}%");
                  })
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $requests = $query->latest()->paginate(15)->appends($request->except('page'));

        $stats = [
            'pending' => SubdomainDataUpdateRequest::where('status', 'pending')->count(),
            'revisi' => SubdomainDataUpdateRequest::where('status', 'revisi')->count(),
            'disetujui' => SubdomainDataUpdateRequest::where('status', 'disetujui')->count(),
            'ditolak' => SubdomainDataUpdateRequest::where('status', 'ditolak')->count(),
        ];

        return view('admin.subdomain.data-update.index', compact('requests', 'stats'));
    }

    /**
     * Detail permohonan dengan diff data saat ini vs usulan.
     */
    public function show($id)
    {
        $request = SubdomainDataUpdateRequest::with(['user', 'webMonitor', 'processedBy'])
            ->findOrFail($id);

        return view('admin.subdomain.data-update.show', array_merge(
            compact('request'),
            $this->lookups()
        ));
    }

    /**
     * Setujui — terapkan usulan ke WebMonitor.
     */
    public function approve(Request $request, $id)
    {
        $validated = $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $dataRequest = SubdomainDataUpdateRequest::with('webMonitor')->findOrFail($id);

        if ($dataRequest->status !== 'pending') {
            return back()->with('error', 'Hanya permohonan berstatus menunggu yang dapat disetujui.');
        }

        $webMonitor = $dataRequest->webMonitor;
        if (!$webMonitor) {
            return back()->with('error', 'Data subdomain (web monitor) tidak ditemukan.');
        }

        DB::beginTransaction();
        try {
            // Terapkan hanya field yang di-whitelist.
            $changes = collect($dataRequest->proposed_data ?? [])
                ->only(SubdomainDataUpdateRequest::EDITABLE_FIELDS)
                ->all();
            $webMonitor->fill($changes);

            // Usulan status pensiun/non-aktif (tidak menghapus data apa pun).
            if ($dataRequest->proposed_decommission !== null) {
                $decommission = (int) $dataRequest->proposed_decommission === 1;
                $webMonitor->is_decommissioned = $decommission;
                $webMonitor->decommissioned_at = $decommission ? now() : null;
            }

            $webMonitor->save();

            $dataRequest->update([
                'status' => 'disetujui',
                'processed_by' => Auth::id(),
                'processed_at' => now(),
                'applied_at' => now(),
                'admin_notes' => $validated['admin_notes'] ?? $dataRequest->admin_notes,
            ]);

            DB::commit();

            Log::info('Subdomain data update approved & applied', [
                'data_update_request_id' => $dataRequest->id,
                'web_monitor_id' => $webMonitor->id,
                'admin_id' => Auth::id(),
            ]);

            return back()->with('success', 'Permohonan disetujui dan data subdomain berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to apply subdomain data update', [
                'data_update_request_id' => $dataRequest->id,
                'web_monitor_id' => $webMonitor->id,
                'error' => $e->getMessage(),
                'admin_id' => Auth::id(),
            ]);

            return back()->with('error', 'Gagal menerapkan pembaruan data: ' . $e->getMessage());
        }
    }

    /**
     * Revisi — kembalikan ke pengguna untuk diperbaiki.
     */
    public function revisi(Request $request, $id)
    {
        $validated = $request->validate([
            'admin_notes' => 'required|string|max:1000',
        ], [
            'admin_notes.required' => 'Catatan revisi harus diisi.',
        ]);

        $dataRequest = SubdomainDataUpdateRequest::findOrFail($id);

        if ($dataRequest->status !== 'pending') {
            return back()->with('error', 'Hanya permohonan berstatus menunggu yang dapat diminta revisi.');
        }

        $dataRequest->update([
            'status' => 'revisi',
            'processed_by' => Auth::id(),
            'processed_at' => now(),
            'admin_notes' => $validated['admin_notes'],
        ]);

        return back()->with('success', 'Permohonan dikembalikan ke pengguna untuk direvisi.');
    }

    /**
     * Tolak permohonan.
     */
    public function reject(Request $request, $id)
    {
        $validated = $request->validate([
            'admin_notes' => 'required|string|max:1000',
        ], [
            'admin_notes.required' => 'Alasan penolakan harus diisi.',
        ]);

        $dataRequest = SubdomainDataUpdateRequest::findOrFail($id);

        if ($dataRequest->status !== 'pending') {
            return back()->with('error', 'Hanya permohonan berstatus menunggu yang dapat ditolak.');
        }

        $dataRequest->update([
            'status' => 'ditolak',
            'processed_by' => Auth::id(),
            'processed_at' => now(),
            'admin_notes' => $validated['admin_notes'],
        ]);

        return redirect()->route('admin.subdomain.data-update.index')
            ->with('success', 'Permohonan pembaruan data berhasil ditolak.');
    }

    /**
     * Lookup untuk menerjemahkan id → nama di tampilan diff.
     */
    private function lookups(): array
    {
        return [
            'programmingLanguages' => ProgrammingLanguage::orderBy('name')->get(),
            'frameworks' => Framework::orderBy('name')->get()->unique('name')->values(),
            'databases' => Database::orderBy('name')->get(),
            'serverLocations' => ServerLocation::orderBy('name')->get(),
        ];
    }
}
