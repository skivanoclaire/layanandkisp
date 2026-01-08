<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Traits\ExportsTteData;
use App\Models\TteRegistrationRequest;
use App\Exports\TteRegistrationExport;
use Illuminate\Http\Request;

class TteRegistrationController extends Controller
{
    use ExportsTteData;
    public function index(Request $request)
    {
        $query = TteRegistrationRequest::with(['user', 'processedBy']);

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
            'total' => TteRegistrationRequest::count(),
            'menunggu' => TteRegistrationRequest::where('status', 'menunggu')->count(),
            'proses' => TteRegistrationRequest::where('status', 'proses')->count(),
            'selesai' => TteRegistrationRequest::where('status', 'selesai')->count(),
            'ditolak' => TteRegistrationRequest::where('status', 'ditolak')->count(),
        ];

        return view('admin.tte.registration.index', compact('requests', 'stats'));
    }

    public function show(TteRegistrationRequest $tteRegistration)
    {
        $tteRegistration->load(['user', 'processedBy']);
        return view('admin.tte.registration.show', compact('tteRegistration'));
    }

    public function updateStatus(Request $request, TteRegistrationRequest $tteRegistration)
    {
        $request->validate([
            'status' => 'required|in:menunggu,proses,selesai,ditolak',
            'keterangan_admin' => 'nullable|string',
        ]);

        $data = [
            'status' => $request->status,
            'keterangan_admin' => $request->keterangan_admin,
            'processed_by' => auth()->id(),
        ];

        $tteRegistration->update($data);

        return redirect()->route('admin.tte.registration.show', $tteRegistration)
            ->with('success', 'Status permohonan berhasil diupdate.');
    }

    public function destroy(TteRegistrationRequest $tteRegistration)
    {
        $ticketNo = $tteRegistration->ticket_no;
        $tteRegistration->delete();

        return redirect()->route('admin.tte.registration.index')
            ->with('success', "Permohonan {$ticketNo} berhasil dihapus.");
    }

    public function exportExcel(Request $request)
    {
        return $this->exportToExcel(
            $request,
            TteRegistrationExport::class,
            'pendaftaran-tte'
        );
    }

    public function exportPdf(Request $request)
    {
        return $this->exportToPdf(
            $request,
            TteRegistrationRequest::class,
            'admin.tte.registration.pdf',
            'pendaftaran-tte'
        );
    }
}
