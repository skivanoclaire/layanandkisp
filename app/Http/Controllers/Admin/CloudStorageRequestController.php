<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CloudStorageRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CloudStorageRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = CloudStorageRequest::with(['user', 'unitKerja', 'processedBy']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ticket_no', 'like', "%{$search}%")
                  ->orWhere('nama', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%");
            });
        }

        $requests = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.datacenter.cloud-storage.index', compact('requests'));
    }

    public function show(CloudStorageRequest $cloudStorageRequest)
    {
        $cloudStorageRequest->load(['user', 'unitKerja', 'processedBy', 'kapasitasRevisis.revisedBy']);
        return view('admin.datacenter.cloud-storage.show', compact('cloudStorageRequest'));
    }

    public function process(Request $request, CloudStorageRequest $cloudStorageRequest)
    {
        if ($cloudStorageRequest->status !== 'menunggu') {
            return redirect()->back()->with('error', 'Permohonan tidak dapat diproses.');
        }

        $cloudStorageRequest->update([
            'status' => 'proses',
            'processed_by' => Auth::id(),
            'processing_at' => now(),
            'admin_notes' => $request->admin_notes,
        ]);

        return redirect()->route('admin.datacenter.cloud-storage.show', $cloudStorageRequest)
            ->with('success', 'Permohonan berhasil diproses.');
    }

    public function complete(Request $request, CloudStorageRequest $cloudStorageRequest)
    {
        if ($cloudStorageRequest->status !== 'proses') {
            return redirect()->back()->with('error', 'Permohonan harus dalam status proses.');
        }

        $validated = $request->validate([
            'kapasitas_disetujui_gb' => 'required|integer|min:1',
            'alasan_kapasitas' => [
                Rule::requiredIf(fn () => (int) $request->kapasitas_disetujui_gb !== (int) $cloudStorageRequest->kapasitas_gb),
                'nullable',
                'string',
            ],
            'akses_url' => 'required|string|max:255',
            'username' => 'required|string|max:255',
            'password' => 'required|string|max:255',
            'keterangan_admin' => 'nullable|string',
            'admin_notes' => 'nullable|string',
        ], [
            'alasan_kapasitas.required' => 'Alasan wajib diisi jika kapasitas yang disetujui berbeda dari yang diminta.',
        ]);

        DB::transaction(function () use ($cloudStorageRequest, $validated) {
            $cloudStorageRequest->setKapasitasDisetujui(
                (int) $validated['kapasitas_disetujui_gb'],
                $validated['alasan_kapasitas'] ?? null,
                Auth::id()
            );

            $cloudStorageRequest->update([
                'status' => 'selesai',
                'akses_url' => $validated['akses_url'],
                'username' => $validated['username'],
                'password' => $validated['password'],
                'keterangan_admin' => $validated['keterangan_admin'],
                'admin_notes' => $validated['admin_notes'],
                'completed_at' => now(),
            ]);
        });

        return redirect()->route('admin.datacenter.cloud-storage.show', $cloudStorageRequest)
            ->with('success', 'Permohonan telah diselesaikan dan akses cloud storage berhasil diberikan.');
    }

    public function reviseKapasitas(Request $request, CloudStorageRequest $cloudStorageRequest)
    {
        if ($cloudStorageRequest->status !== 'selesai') {
            return redirect()->back()->with('error', 'Kapasitas hanya dapat direvisi pada permohonan yang sudah selesai.');
        }

        $validated = $request->validate([
            'kapasitas_disetujui_gb' => [
                'required',
                'integer',
                'min:1',
                Rule::notIn([$cloudStorageRequest->kapasitas_berlaku_gb]),
            ],
            'alasan_kapasitas' => 'required|string',
        ], [
            'kapasitas_disetujui_gb.not_in' => 'Kapasitas baru sama dengan kapasitas yang berlaku saat ini.',
        ]);

        DB::transaction(fn () => $cloudStorageRequest->setKapasitasDisetujui(
            (int) $validated['kapasitas_disetujui_gb'],
            $validated['alasan_kapasitas'],
            Auth::id()
        ));

        return redirect()->route('admin.datacenter.cloud-storage.show', $cloudStorageRequest)
            ->with('success', 'Kapasitas yang disetujui berhasil direvisi.');
    }

    public function updateCredentials(Request $request, CloudStorageRequest $cloudStorageRequest)
    {
        if ($cloudStorageRequest->status !== 'selesai') {
            return redirect()->back()->with('error', 'Kredensial hanya dapat diubah pada permohonan yang sudah selesai.');
        }

        $validated = $request->validate([
            'akses_url' => 'required|string|max:255',
            'username' => 'required|string|max:255',
            'password' => 'nullable|string|max:255', // kosong = password lama tetap dipakai
        ]);

        $data = [
            'akses_url' => $validated['akses_url'],
            'username' => $validated['username'],
        ];
        if (filled($validated['password'] ?? null)) {
            $data['password'] = $validated['password'];
        }

        $cloudStorageRequest->update($data);

        return redirect()->route('admin.datacenter.cloud-storage.show', $cloudStorageRequest)
            ->with('success', 'Kredensial akses cloud storage berhasil diperbarui.');
    }

    public function reject(Request $request, CloudStorageRequest $cloudStorageRequest)
    {
        if (!in_array($cloudStorageRequest->status, ['menunggu', 'proses'])) {
            return redirect()->back()->with('error', 'Permohonan tidak dapat ditolak.');
        }

        $request->validate([
            'admin_notes' => 'required|string',
        ]);

        $cloudStorageRequest->update([
            'status' => 'ditolak',
            'processed_by' => Auth::id(),
            'admin_notes' => $request->admin_notes,
            'rejected_at' => now(),
        ]);

        return redirect()->route('admin.datacenter.cloud-storage.show', $cloudStorageRequest)
            ->with('success', 'Permohonan telah ditolak.');
    }

    public function updateNotes(Request $request, CloudStorageRequest $cloudStorageRequest)
    {
        $request->validate([
            'admin_notes' => 'nullable|string',
        ]);

        $cloudStorageRequest->update([
            'admin_notes' => $request->admin_notes,
        ]);

        return redirect()->route('admin.datacenter.cloud-storage.show', $cloudStorageRequest)
            ->with('success', 'Catatan admin berhasil diperbarui.');
    }
}
