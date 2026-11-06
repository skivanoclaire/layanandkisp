<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InternetInstallationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PasangBaruInternetAdminController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');

        $query = InternetInstallationRequest::with(['user', 'unitKerja'])
            ->orderByDesc('created_at');

        if ($status) {
            $query->where('status', $status);
        }

        $requests = $query->paginate(25);

        return view('admin.internet.pasang-baru.index', compact('requests', 'status'));
    }

    public function show($id)
    {
        $installation = InternetInstallationRequest::with(['user', 'unitKerja', 'processedBy'])->findOrFail($id);

        return view('admin.internet.pasang-baru.show', compact('installation'));
    }

    public function process(Request $request, $id)
    {
        $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $installation = InternetInstallationRequest::findOrFail($id);

        if ($installation->status !== 'menunggu') {
            return back()->with('error', 'Hanya permohonan berstatus menunggu yang dapat diproses.');
        }

        $installation->status = 'proses';
        $installation->processing_at = now();
        $installation->processed_by = auth()->id();
        $installation->admin_notes = $request->admin_notes ?: $installation->admin_notes;
        $installation->save();

        Log::info('Pasang baru internet set to process', [
            'ticket_no' => $installation->ticket_no,
            'processed_by' => auth()->id(),
            'user_id' => $installation->user_id,
        ]);

        return redirect()->route('admin.internet.pasang-baru.show', $installation->id)
            ->with('success', "Permohonan {$installation->ticket_no} sedang diproses.");
    }

    public function complete(Request $request, $id)
    {
        $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $installation = InternetInstallationRequest::findOrFail($id);

        if ($installation->status !== 'proses') {
            return back()->with('error', 'Hanya permohonan yang sedang diproses yang dapat diselesaikan.');
        }

        $installation->status = 'selesai';
        $installation->completed_at = now();
        $installation->processed_by = auth()->id();
        $installation->admin_notes = $request->admin_notes ?: $installation->admin_notes;
        $installation->save();

        Log::info('Pasang baru internet completed', [
            'ticket_no' => $installation->ticket_no,
            'completed_by' => auth()->id(),
            'user_id' => $installation->user_id,
        ]);

        return redirect()->route('admin.internet.pasang-baru.show', $installation->id)
            ->with('success', "Permohonan {$installation->ticket_no} telah diselesaikan.");
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'admin_notes' => 'required|string|max:1000',
        ], [
            'admin_notes.required' => 'Alasan penolakan wajib diisi.',
        ]);

        $installation = InternetInstallationRequest::findOrFail($id);

        if (!in_array($installation->status, ['menunggu', 'proses'])) {
            return back()->with('error', 'Permohonan ini tidak dapat ditolak pada statusnya saat ini.');
        }

        $installation->status = 'ditolak';
        $installation->rejected_at = now();
        $installation->processed_by = auth()->id();
        $installation->admin_notes = $request->admin_notes;
        $installation->save();

        Log::info('Pasang baru internet rejected', [
            'ticket_no' => $installation->ticket_no,
            'rejected_by' => auth()->id(),
            'user_id' => $installation->user_id,
            'reason' => $request->admin_notes,
        ]);

        return redirect()->route('admin.internet.pasang-baru.show', $installation->id)
            ->with('success', "Permohonan {$installation->ticket_no} telah ditolak.");
    }

    public function updateNotes(Request $request, $id)
    {
        $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $installation = InternetInstallationRequest::findOrFail($id);

        if (!in_array($installation->status, ['proses', 'selesai'])) {
            return back()->with('error', 'Catatan hanya dapat diperbarui jika status sedang diproses atau selesai.');
        }

        $installation->admin_notes = $request->admin_notes;
        $installation->save();

        return redirect()->route('admin.internet.pasang-baru.show', $installation->id)
            ->with('success', 'Catatan admin berhasil diperbarui.');
    }
}
