<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LaporanGangguan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LaporanGangguanAdminController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');

        $query = LaporanGangguan::with(['user', 'unitKerja'])
            ->orderByDesc('created_at');

        if ($status) {
            $query->where('status', $status);
        }

        $laporans = $query->paginate(25);

        return view('admin.internet.laporan-gangguan.index', compact('laporans', 'status'));
    }

    public function show($id)
    {
        $laporan = LaporanGangguan::with(['user', 'unitKerja', 'processedBy'])->findOrFail($id);
        return view('admin.internet.laporan-gangguan.show', compact('laporan'));
    }

    public function setProcess(Request $request, $id)
    {
        $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $laporan = LaporanGangguan::findOrFail($id);

        $laporan->status = 'proses';
        $laporan->processing_at = now();
        $laporan->processed_by = auth()->id();

        if ($request->admin_notes) {
            $laporan->admin_notes = $request->admin_notes;
        }

        $laporan->save();

        Log::info('Laporan gangguan set to process', [
            'ticket_no' => $laporan->ticket_no,
            'processed_by' => auth()->id(),
            'user_id' => $laporan->user_id,
        ]);

        return redirect()->route('admin.internet.laporan-gangguan.show', $laporan->id)
            ->with('success', "Laporan {$laporan->ticket_no} sedang diproses.");
    }

    public function complete(Request $request, $id)
    {
        $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $laporan = LaporanGangguan::findOrFail($id);

        $laporan->status = 'selesai';
        $laporan->completed_at = now();
        $laporan->processed_by = auth()->id();

        if ($request->admin_notes) {
            $laporan->admin_notes = $request->admin_notes;
        }

        $laporan->save();

        Log::info('Laporan gangguan completed', [
            'ticket_no' => $laporan->ticket_no,
            'completed_by' => auth()->id(),
            'user_id' => $laporan->user_id,
        ]);

        return redirect()->route('admin.internet.laporan-gangguan.show', $laporan->id)
            ->with('success', "Laporan {$laporan->ticket_no} telah diselesaikan.");
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'admin_notes' => 'required|string|max:1000',
        ], [
            'admin_notes.required' => 'Alasan penolakan wajib diisi.',
        ]);

        $laporan = LaporanGangguan::findOrFail($id);

        $laporan->status = 'ditolak';
        $laporan->rejected_at = now();
        $laporan->processed_by = auth()->id();
        $laporan->admin_notes = $request->admin_notes;
        $laporan->save();

        Log::info('Laporan gangguan rejected', [
            'ticket_no' => $laporan->ticket_no,
            'rejected_by' => auth()->id(),
            'user_id' => $laporan->user_id,
            'reason' => $request->admin_notes,
        ]);

        return redirect()->route('admin.internet.laporan-gangguan.show', $laporan->id)
            ->with('success', "Laporan {$laporan->ticket_no} telah ditolak.");
    }

    public function updateNotes(Request $request, $id)
    {
        $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $laporan = LaporanGangguan::findOrFail($id);

        // Only allow updating if status is proses or selesai
        if (!in_array($laporan->status, ['proses', 'selesai'])) {
            return back()->with('error', 'Catatan hanya dapat diperbarui jika status sedang diproses atau selesai.');
        }

        $laporan->admin_notes = $request->admin_notes;
        $laporan->save();

        return redirect()->route('admin.internet.laporan-gangguan.show', $laporan->id)
            ->with('success', 'Catatan admin berhasil diperbarui.');
    }
}
