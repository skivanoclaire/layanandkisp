<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Traits\ExportsTteData;
use App\Models\TtePassphraseResetRequest;
use App\Exports\TtePassphraseResetExport;
use Illuminate\Http\Request;

class TtePassphraseResetController extends Controller
{
    use ExportsTteData;
    public function index(Request $request)
    {
        $query = TtePassphraseResetRequest::with(['user', 'processedBy']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search by ticket_no, nama, NIP, or email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('ticket_no', 'like', "%{$search}%")
                  ->orWhere('nama', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%")
                  ->orWhere('email_resmi', 'like', "%{$search}%");
            });
        }

        // Filter by date range
        if ($request->filled('dari_tanggal')) {
            $query->whereDate('created_at', '>=', $request->dari_tanggal);
        }
        if ($request->filled('sampai_tanggal')) {
            $query->whereDate('created_at', '<=', $request->sampai_tanggal);
        }

        $requests = $query->orderBy('created_at', 'desc')->paginate(20);

        $stats = [
            'total' => TtePassphraseResetRequest::count(),
            'menunggu' => TtePassphraseResetRequest::where('status', 'menunggu')->count(),
            'diproses' => TtePassphraseResetRequest::where('status', 'diproses')->count(),
            'selesai' => TtePassphraseResetRequest::where('status', 'selesai')->count(),
            'ditolak' => TtePassphraseResetRequest::where('status', 'ditolak')->count(),
        ];

        return view('admin.tte.passphrase-reset.index', compact('requests', 'stats'));
    }

    public function show(TtePassphraseResetRequest $ttePassphraseReset)
    {
        $ttePassphraseReset->load(['user', 'processedBy']);
        return view('admin.tte.passphrase-reset.show', compact('ttePassphraseReset'));
    }

    public function updateStatus(Request $request, TtePassphraseResetRequest $ttePassphraseReset)
    {
        $request->validate([
            'status' => 'required|in:menunggu,diproses,selesai,ditolak',
            'keterangan_admin' => 'nullable|string',
            'instansi' => 'nullable|string|max:255',
            'jabatan' => 'nullable|string|max:255',
        ]);

        $data = [
            'status' => $request->status,
            'keterangan_admin' => $request->keterangan_admin,
            'instansi' => $request->instansi,
            'jabatan' => $request->jabatan,
            'processed_by' => auth()->id(),
        ];

        // Set timestamps based on status
        if ($request->status === 'diproses' && $ttePassphraseReset->status !== 'diproses') {
            $data['processing_at'] = now();
        }

        if ($request->status === 'selesai' && $ttePassphraseReset->status !== 'selesai') {
            $data['completed_at'] = now();
        }

        if ($request->status === 'ditolak' && $ttePassphraseReset->status !== 'ditolak') {
            $data['rejected_at'] = now();
        }

        $ttePassphraseReset->update($data);

        return redirect()->route('admin.tte.passphrase-reset.show', $ttePassphraseReset)
            ->with('success', 'Status permohonan berhasil diupdate.');
    }

    public function destroy(TtePassphraseResetRequest $ttePassphraseReset)
    {
        $ticketNo = $ttePassphraseReset->ticket_no;
        $ttePassphraseReset->delete();

        return redirect()->route('admin.tte.passphrase-reset.index')
            ->with('success', "Permohonan {$ticketNo} berhasil dihapus.");
    }

    public function exportExcel(Request $request)
    {
        return $this->exportToExcel(
            $request,
            TtePassphraseResetExport::class,
            'reset-passphrase-tte'
        );
    }

    public function exportPdf(Request $request)
    {
        return $this->exportToPdf(
            $request,
            TtePassphraseResetRequest::class,
            'admin.tte.passphrase-reset.pdf',
            'reset-passphrase-tte'
        );
    }
}
