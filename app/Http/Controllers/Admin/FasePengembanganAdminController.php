<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FasePengembanganDokumen;
use App\Models\RekomendasiAplikasiForm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FasePengembanganAdminController extends Controller
{
    /**
     * Display a listing of proposals in development phase.
     */
    public function index(Request $request)
    {
        $query = RekomendasiAplikasiForm::where('fase_saat_ini', 'pengembangan')
            ->with(['user', 'unitKerja', 'statusKementerian']);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama_aplikasi', 'like', "%{$search}%")
                  ->orWhere('ticket_number', 'like', "%{$search}%");
            });
        }

        // Status kementerian filter
        if ($request->filled('status_kementerian')) {
            $query->whereHas('statusKementerian', function($q) use ($request) {
                $q->where('status', $request->status_kementerian);
            });
        }

        $proposals = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.fase-pengembangan.index', compact('proposals'));
    }

    /**
     * Display details and documents for a specific proposal.
     */
    public function show($id)
    {
        $proposal = RekomendasiAplikasiForm::with([
            'user',
            'unitKerja',
            'statusKementerian',
            'fasePengembanganDokumen.uploader'
        ])->findOrFail($id);

        // Verify fase
        if ($proposal->fase_saat_ini !== 'pengembangan') {
            return redirect()->route('admin.fase-pengembangan.index')
                ->with('error', 'Usulan ini belum memasuki fase pengembangan.');
        }

        // Group documents by fase
        $dokumenByFase = $proposal->fasePengembanganDokumen->groupBy('fase');

        return view('admin.fase-pengembangan.show', compact('proposal', 'dokumenByFase'));
    }

    /**
     * Download a document.
     */
    public function downloadDokumen($id, $dokumenId)
    {
        $dokumen = FasePengembanganDokumen::findOrFail($dokumenId);

        // Check if file exists
        if (!Storage::disk('public')->exists($dokumen->file_path)) {
            return redirect()->back()->with('error', 'File tidak ditemukan.');
        }

        return Storage::disk('public')->download($dokumen->file_path, $dokumen->nama_file);
    }

    /**
     * Delete a document (admin intervention).
     */
    public function deleteDokumen(Request $request, $id, $dokumenId)
    {
        $dokumen = FasePengembanganDokumen::findOrFail($dokumenId);
        $proposal = $dokumen->proposal;

        // Require reason for deletion
        $request->validate([
            'alasan' => 'required|string|min:10',
        ], [
            'alasan.required' => 'Alasan penghapusan harus diisi.',
            'alasan.min' => 'Alasan penghapusan minimal 10 karakter.',
        ]);

        // Delete file from storage
        if (Storage::disk('public')->exists($dokumen->file_path)) {
            Storage::disk('public')->delete($dokumen->file_path);
        }

        // Log activity with reason
        $faseLabel = $dokumen->fase_display;
        $proposal->logActivity(
            'admin_delete_dokumen_fase',
            "Admin menghapus dokumen fase {$faseLabel}: {$dokumen->nama_file}. Alasan: {$request->alasan}",
            auth()->id()
        );

        // Delete database record
        $dokumen->delete();

        return redirect()->back()->with('success', 'Dokumen berhasil dihapus.');
    }

    /**
     * Add note/comment to proposal.
     */
    public function addNote(Request $request, $id)
    {
        $request->validate([
            'catatan' => 'required|string|min:10',
        ], [
            'catatan.required' => 'Catatan harus diisi.',
            'catatan.min' => 'Catatan minimal 10 karakter.',
        ]);

        $proposal = RekomendasiAplikasiForm::findOrFail($id);

        // Log activity
        $proposal->logActivity(
            'admin_add_note_fase',
            "Admin menambahkan catatan: {$request->catatan}",
            auth()->id()
        );

        return redirect()->back()->with('success', 'Catatan berhasil ditambahkan.');
    }
}
