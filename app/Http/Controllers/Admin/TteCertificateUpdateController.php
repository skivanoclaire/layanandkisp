<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Traits\ExportsTteData;
use App\Models\TteCertificateUpdateRequest;
use App\Exports\TteCertificateUpdateExport;
use Illuminate\Http\Request;

class TteCertificateUpdateController extends Controller
{
    use ExportsTteData;
    public function index(Request $request)
    {
        $query = TteCertificateUpdateRequest::with(['user', 'processedBy']);

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
            'total' => TteCertificateUpdateRequest::count(),
            'menunggu' => TteCertificateUpdateRequest::where('status', 'menunggu')->count(),
            'diproses' => TteCertificateUpdateRequest::where('status', 'diproses')->count(),
            'selesai' => TteCertificateUpdateRequest::where('status', 'selesai')->count(),
            'ditolak' => TteCertificateUpdateRequest::where('status', 'ditolak')->count(),
        ];

        return view('admin.tte.certificate-update.index', compact('requests', 'stats'));
    }

    public function show(TteCertificateUpdateRequest $tteCertificateUpdate)
    {
        $tteCertificateUpdate->load(['user', 'processedBy']);
        return view('admin.tte.certificate-update.show', compact('tteCertificateUpdate'));
    }

    public function updateStatus(Request $request, TteCertificateUpdateRequest $tteCertificateUpdate)
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
        if ($request->status === 'diproses' && $tteCertificateUpdate->status !== 'diproses') {
            $data['processing_at'] = now();
        }

        if ($request->status === 'selesai' && $tteCertificateUpdate->status !== 'selesai') {
            $data['completed_at'] = now();
        }

        if ($request->status === 'ditolak' && $tteCertificateUpdate->status !== 'ditolak') {
            $data['rejected_at'] = now();
        }

        $tteCertificateUpdate->update($data);

        return redirect()->route('admin.tte.certificate-update.show', $tteCertificateUpdate)
            ->with('success', 'Status permohonan berhasil diupdate.');
    }

    public function destroy(TteCertificateUpdateRequest $tteCertificateUpdate)
    {
        $ticketNo = $tteCertificateUpdate->ticket_no;
        $tteCertificateUpdate->delete();

        return redirect()->route('admin.tte.certificate-update.index')
            ->with('success', "Permohonan {$ticketNo} berhasil dihapus.");
    }

    public function exportExcel(Request $request)
    {
        return $this->exportToExcel(
            $request,
            TteCertificateUpdateExport::class,
            'pembaruan-sertifikat-tte'
        );
    }

    public function exportPdf(Request $request)
    {
        return $this->exportToPdf(
            $request,
            TteCertificateUpdateRequest::class,
            'admin.tte.certificate-update.pdf',
            'pembaruan-sertifikat-tte'
        );
    }
}
