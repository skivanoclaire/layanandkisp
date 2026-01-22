<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\FasePengembanganDokumen;
use App\Models\RekomendasiAplikasiForm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FasePengembanganController extends Controller
{
    /**
     * Display dashboard with proposals in development phase.
     */
    public function index()
    {
        $proposals = RekomendasiAplikasiForm::where('user_id', auth()->id())
            ->where('fase_saat_ini', 'pengembangan')
            ->with('statusKementerian')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('user.fase-pengembangan.index', compact('proposals'));
    }

    /**
     * Show phase management page for a specific proposal.
     */
    public function show($id)
    {
        $proposal = RekomendasiAplikasiForm::with([
            'statusKementerian',
            'fasePengembanganDokumen.uploader',
            'historiAktivitas.user'
        ])->findOrFail($id);

        // Verify ownership
        if ($proposal->user_id !== auth()->id()) {
            abort(403, 'Anda tidak memiliki akses ke usulan ini.');
        }

        // Verify fase
        if ($proposal->fase_saat_ini !== 'pengembangan') {
            return redirect()->route('fase-pengembangan.index')
                ->with('error', 'Usulan ini belum memasuki fase pengembangan.');
        }

        // Group documents by fase
        $dokumenByFase = $proposal->fasePengembanganDokumen->groupBy('fase');

        return view('user.fase-pengembangan.show', compact('proposal', 'dokumenByFase'));
    }

    /**
     * Upload a document for a specific phase.
     */
    public function uploadDokumen(Request $request, $id)
    {
        $request->validate([
            'fase' => 'required|in:rancang_bangun,implementasi,uji_kelaikan,pemeliharaan,evaluasi',
            'dokumen' => 'required|file|mimes:zip,rar,7z|max:51200', // 50MB
        ], [
            'dokumen.required' => 'Dokumen harus diupload.',
            'dokumen.file' => 'File yang diupload tidak valid.',
            'dokumen.mimes' => 'Dokumen harus berformat ZIP, RAR, atau 7Z.',
            'dokumen.max' => 'Ukuran dokumen maksimal 50MB.',
        ]);

        $proposal = RekomendasiAplikasiForm::findOrFail($id);

        // Verify ownership
        if ($proposal->user_id !== auth()->id()) {
            abort(403, 'Anda tidak memiliki akses ke usulan ini.');
        }

        // Verify fase
        if ($proposal->fase_saat_ini !== 'pengembangan') {
            return redirect()->route('fase-pengembangan.index')
                ->with('error', 'Usulan ini belum memasuki fase pengembangan.');
        }

        $file = $request->file('dokumen');
        $fase = $request->fase;

        // Generate unique filename with format: {fase}_{original_name}_{timestamp}.{extension}
        $timestamp = now()->format('Ymd_His');
        $extension = $file->getClientOriginalExtension();
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        // Sanitize filename: remove special characters, keep only alphanumeric, dash, underscore
        $sanitizedName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $originalName);
        $filename = "{$fase}_{$sanitizedName}_{$timestamp}.{$extension}";

        // Store file in fase-specific directory
        $faseFolder = str_replace('_', '-', $fase); // rancang_bangun -> rancang-bangun
        $directory = "fase-pengembangan/{$proposal->ticket_number}/{$faseFolder}";
        $filePath = $file->storeAs($directory, $filename, 'public');

        // Create database record
        FasePengembanganDokumen::create([
            'rekomendasi_aplikasi_form_id' => $proposal->id,
            'fase' => $fase,
            'nama_file' => $filename, // Store the generated filename instead of original
            'file_path' => $filePath,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'uploaded_by' => auth()->id(),
        ]);

        // Log activity
        $faseLabel = FasePengembanganDokumen::FASE_LABELS[$fase] ?? $fase;
        $proposal->logActivity(
            'upload_dokumen_fase',
            "Upload dokumen fase {$faseLabel}: {$file->getClientOriginalName()}",
            auth()->id()
        );

        return redirect()->back()->with('success', 'Dokumen berhasil diupload.');
    }

    /**
     * Download a document.
     */
    public function downloadDokumen($id, $dokumenId)
    {
        $dokumen = FasePengembanganDokumen::findOrFail($dokumenId);
        $proposal = $dokumen->proposal;

        // Verify ownership
        if ($proposal->user_id !== auth()->id()) {
            abort(403, 'Anda tidak memiliki akses ke dokumen ini.');
        }

        // Check if file exists
        if (!Storage::disk('public')->exists($dokumen->file_path)) {
            return redirect()->back()->with('error', 'File tidak ditemukan.');
        }

        return Storage::disk('public')->download($dokumen->file_path, $dokumen->nama_file);
    }

    /**
     * Delete a document.
     */
    public function deleteDokumen($id, $dokumenId)
    {
        $dokumen = FasePengembanganDokumen::findOrFail($dokumenId);
        $proposal = $dokumen->proposal;

        // Verify ownership
        if ($proposal->user_id !== auth()->id()) {
            abort(403, 'Anda tidak memiliki akses ke dokumen ini.');
        }

        // Delete file from storage
        if (Storage::disk('public')->exists($dokumen->file_path)) {
            Storage::disk('public')->delete($dokumen->file_path);
        }

        // Log activity before deletion
        $faseLabel = $dokumen->fase_display;
        $proposal->logActivity(
            'delete_dokumen_fase',
            "Hapus dokumen fase {$faseLabel}: {$dokumen->nama_file}",
            auth()->id()
        );

        // Delete database record
        $dokumen->delete();

        return redirect()->back()->with('success', 'Dokumen berhasil dihapus.');
    }
}
