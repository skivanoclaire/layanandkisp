<?php

namespace App\Services;

use App\Models\RekomendasiDokumenUsulan;
use App\Models\RekomendasiAplikasiForm;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RekomendasiDocumentService
{
    /**
     * Upload a document for rekomendasi usulan.
     */
    public function uploadDocument(
        UploadedFile $file,
        string $jenisDokumen,
        int $rekomendasiId
    ): RekomendasiDokumenUsulan {
        // Validate document type
        $this->validateDocument($file, $jenisDokumen);

        // Generate filename
        $rekomendasi = RekomendasiAplikasiForm::findOrFail($rekomendasiId);
        $ticketNumber = $rekomendasi->ticket_number;
        $extension = $file->getClientOriginalExtension();
        $timestamp = now()->format('YmdHis');
        $filename = "{$ticketNumber}_{$jenisDokumen}_{$timestamp}.{$extension}";

        // Store file
        $path = $file->storeAs(
            "rekomendasi/documents/{$rekomendasiId}",
            $filename,
            'public'
        );

        // Check for existing document of same type
        $existingDoc = RekomendasiDokumenUsulan::where('rekomendasi_aplikasi_form_id', $rekomendasiId)
            ->where('jenis_dokumen', $jenisDokumen)
            ->latest()
            ->first();

        $versi = $existingDoc ? $existingDoc->versi + 1 : 1;

        // Create database record
        return RekomendasiDokumenUsulan::create([
            'rekomendasi_aplikasi_form_id' => $rekomendasiId,
            'jenis_dokumen' => $jenisDokumen,
            'nama_file' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'versi' => $versi,
            'uploaded_by' => auth()->id(),
        ]);
    }

    /**
     * Validate document file.
     */
    public function validateDocument(UploadedFile $file, string $jenisDokumen): bool
    {
        // Check file size (max 10MB)
        $maxSize = 10 * 1024 * 1024; // 10MB in bytes
        if ($file->getSize() > $maxSize) {
            throw new \Exception('Ukuran file maksimal 10MB');
        }

        // Check file type (only PDF)
        $allowedMimeTypes = ['application/pdf'];
        if (!in_array($file->getMimeType(), $allowedMimeTypes)) {
            throw new \Exception('File harus berformat PDF');
        }

        // Validate jenis_dokumen
        $validTypes = ['analisis_kebutuhan', 'perencanaan', 'manajemen_risiko'];
        if (!in_array($jenisDokumen, $validTypes)) {
            throw new \Exception('Jenis dokumen tidak valid');
        }

        return true;
    }

    /**
     * Delete a document.
     */
    public function deleteDocument(int $documentId): bool
    {
        $document = RekomendasiDokumenUsulan::findOrFail($documentId);

        // Check authorization
        if ($document->uploaded_by !== auth()->id() && !auth()->user()->hasRole('Admin')) {
            throw new \Exception('Anda tidak memiliki akses untuk menghapus dokumen ini');
        }

        // Delete file from storage
        if (Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        // Delete database record
        return $document->delete();
    }

    /**
     * Get document file path.
     */
    public function getDocumentPath(int $documentId): string
    {
        $document = RekomendasiDokumenUsulan::findOrFail($documentId);
        return storage_path('app/public/' . $document->file_path);
    }

    /**
     * Download a document.
     */
    public function downloadDocument(int $documentId): StreamedResponse
    {
        $document = RekomendasiDokumenUsulan::findOrFail($documentId);

        // Check authorization
        $rekomendasi = $document->rekomendasiAplikasiForm;
        if ($rekomendasi->user_id !== auth()->id() && !auth()->user()->hasRole('Admin')) {
            throw new \Exception('Anda tidak memiliki akses untuk mengunduh dokumen ini');
        }

        // Check if file exists
        if (!Storage::disk('public')->exists($document->file_path)) {
            throw new \Exception('File tidak ditemukan');
        }

        return Storage::disk('public')->download(
            $document->file_path,
            $document->nama_file
        );
    }

    /**
     * Get all documents for a rekomendasi.
     */
    public function getDocuments(int $rekomendasiId): array
    {
        $documents = RekomendasiDokumenUsulan::where('rekomendasi_aplikasi_form_id', $rekomendasiId)
            ->with('uploader')
            ->orderBy('jenis_dokumen')
            ->orderBy('versi', 'desc')
            ->get()
            ->groupBy('jenis_dokumen');

        return [
            'analisis_kebutuhan' => $documents->get('analisis_kebutuhan', collect())->first(),
            'perencanaan' => $documents->get('perencanaan', collect())->first(),
            'manajemen_risiko' => $documents->get('manajemen_risiko', collect())->first(),
        ];
    }

    /**
     * Check if all required documents are uploaded.
     * V2 Note: Document upload is no longer required as all data is now filled digitally.
     * This function now returns true to allow submission without PDF documents.
     */
    public function hasAllRequiredDocuments(int $rekomendasiId): bool
    {
        // V2: All information is now filled through digital forms
        // No PDF documents are required for submission
        return true;
    }

    /**
     * Get document version history.
     */
    public function getDocumentHistory(int $rekomendasiId, string $jenisDokumen): \Illuminate\Support\Collection
    {
        return RekomendasiDokumenUsulan::where('rekomendasi_aplikasi_form_id', $rekomendasiId)
            ->where('jenis_dokumen', $jenisDokumen)
            ->with('uploader')
            ->orderBy('versi', 'desc')
            ->get();
    }
}
