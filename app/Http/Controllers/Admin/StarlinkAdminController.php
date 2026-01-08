<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StarlinkRequest;
use App\Models\StarlinkServiceSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StarlinkAdminController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');

        $query = StarlinkRequest::with(['user', 'unitKerja'])
            ->orderByDesc('created_at');

        if ($status) {
            $query->where('status', $status);
        }

        $requests = $query->paginate(25);
        $serviceSetting = StarlinkServiceSetting::first();

        return view('admin.internet.starlink.index', compact('requests', 'status', 'serviceSetting'));
    }

    public function show($id)
    {
        $starlinkRequest = StarlinkRequest::with(['user', 'unitKerja', 'processedBy'])->findOrFail($id);
        return view('admin.internet.starlink.show', compact('starlinkRequest'));
    }

    public function setProcess(Request $request, $id)
    {
        $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $starlinkRequest = StarlinkRequest::findOrFail($id);

        $starlinkRequest->status = 'proses';
        $starlinkRequest->processing_at = now();
        $starlinkRequest->processed_by = auth()->id();

        if ($request->admin_notes) {
            $starlinkRequest->admin_notes = $request->admin_notes;
        }

        $starlinkRequest->save();

        Log::info('Starlink request set to process', [
            'ticket_no' => $starlinkRequest->ticket_no,
            'processed_by' => auth()->id(),
            'user_id' => $starlinkRequest->user_id,
        ]);

        return redirect()->route('admin.internet.starlink.show', $starlinkRequest->id)
            ->with('success', "Permohonan {$starlinkRequest->ticket_no} sedang diproses.");
    }

    public function complete(Request $request, $id)
    {
        $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $starlinkRequest = StarlinkRequest::findOrFail($id);

        $starlinkRequest->status = 'selesai';
        $starlinkRequest->completed_at = now();
        $starlinkRequest->processed_by = auth()->id();

        if ($request->admin_notes) {
            $starlinkRequest->admin_notes = $request->admin_notes;
        }

        $starlinkRequest->save();

        Log::info('Starlink request completed', [
            'ticket_no' => $starlinkRequest->ticket_no,
            'completed_by' => auth()->id(),
            'user_id' => $starlinkRequest->user_id,
        ]);

        return redirect()->route('admin.internet.starlink.show', $starlinkRequest->id)
            ->with('success', "Permohonan {$starlinkRequest->ticket_no} telah diselesaikan.");
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'admin_notes' => 'required|string|max:1000',
        ], [
            'admin_notes.required' => 'Alasan penolakan wajib diisi.',
        ]);

        $starlinkRequest = StarlinkRequest::findOrFail($id);

        $starlinkRequest->status = 'ditolak';
        $starlinkRequest->rejected_at = now();
        $starlinkRequest->processed_by = auth()->id();
        $starlinkRequest->admin_notes = $request->admin_notes;
        $starlinkRequest->save();

        Log::info('Starlink request rejected', [
            'ticket_no' => $starlinkRequest->ticket_no,
            'rejected_by' => auth()->id(),
            'user_id' => $starlinkRequest->user_id,
            'reason' => $request->admin_notes,
        ]);

        return redirect()->route('admin.internet.starlink.show', $starlinkRequest->id)
            ->with('success', "Permohonan {$starlinkRequest->ticket_no} telah ditolak.");
    }

    public function updateNotes(Request $request, $id)
    {
        $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $starlinkRequest = StarlinkRequest::findOrFail($id);

        // Only allow updating if status is proses or selesai
        if (!in_array($starlinkRequest->status, ['proses', 'selesai'])) {
            return back()->with('error', 'Catatan hanya dapat diperbarui jika status sedang diproses atau selesai.');
        }

        $starlinkRequest->admin_notes = $request->admin_notes;
        $starlinkRequest->save();

        return redirect()->route('admin.internet.starlink.show', $starlinkRequest->id)
            ->with('success', 'Catatan admin berhasil diperbarui.');
    }

    /**
     * Toggle Starlink service on/off
     */
    public function toggleService(Request $request)
    {
        $request->validate([
            'is_active' => 'required|boolean',
            'inactive_reason' => 'required_if:is_active,false|nullable|string|max:500',
        ], [
            'inactive_reason.required_if' => 'Alasan penonaktifan layanan wajib diisi.',
        ]);

        $setting = StarlinkServiceSetting::first();
        if (!$setting) {
            $setting = StarlinkServiceSetting::create([
                'is_active' => $request->is_active,
                'inactive_reason' => $request->inactive_reason,
                'updated_by' => auth()->id(),
            ]);
        } else {
            $setting->is_active = $request->is_active;
            $setting->inactive_reason = $request->inactive_reason;
            $setting->updated_by = auth()->id();
            $setting->save();
        }

        $statusText = $request->is_active ? 'diaktifkan' : 'dinonaktifkan';

        Log::info('Starlink service toggled', [
            'is_active' => $request->is_active,
            'inactive_reason' => $request->inactive_reason,
            'updated_by' => auth()->id(),
        ]);

        return redirect()->route('admin.internet.starlink.index')
            ->with('success', "Layanan Starlink Jelajah telah {$statusText}.");
    }
}
