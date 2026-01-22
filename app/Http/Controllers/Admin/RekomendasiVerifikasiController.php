<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RekomendasiAplikasiForm;
use App\Models\RekomendasiVerifikasi;
use App\Models\RekomendasiStatusKementerian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Dompdf\Dompdf;
use Dompdf\Options;

class RekomendasiVerifikasiController extends Controller
{
    /**
     * Display a listing of proposals awaiting verification.
     */
    public function index(Request $request)
    {
        $query = RekomendasiAplikasiForm::query()
            ->with(['user.unitKerja', 'pemilikProsesBisnis', 'verifikasi.verifikator', 'dokumenUsulan'])
            ->latest();

        // Filter by proposal status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            // Default: show proposals that are submitted, need revision, being processed, or approved
            $query->whereIn('status', ['diajukan', 'perlu_revisi', 'diproses', 'disetujui']);
        }

        // Filter by verification status
        if ($request->filled('verification_status')) {
            $query->whereHas('verifikasi', function ($q) use ($request) {
                $q->where('status', $request->verification_status);
            });
        }

        // Search by application name or ticket number
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama_aplikasi', 'like', '%' . $request->search . '%')
                  ->orWhere('ticket_number', 'like', '%' . $request->search . '%');
            });
        }

        $proposals = $query->paginate(15);

        return view('admin.rekomendasi.verifikasi.index', compact('proposals'));
    }

    /**
     * Display the specified proposal for verification.
     */
    public function show($id)
    {
        $proposal = RekomendasiAplikasiForm::with([
            'user.unitKerja',
            'pemilikProsesBisnis',
            'dokumenUsulan.uploader',
            'verifikasi.verifikator',
            'historiAktivitas.user',
            'statusKementerian'
        ])->findOrFail($id);

        return view('admin.rekomendasi.verifikasi.show', compact('proposal'));
    }

    /**
     * Start verification process.
     */
    public function startVerification($id)
    {
        try {
            $proposal = RekomendasiAplikasiForm::findOrFail($id);

            if ($proposal->status !== 'diajukan') {
                return redirect()
                    ->back()
                    ->with('error', 'Usulan ini tidak dapat diverifikasi pada status saat ini.');
            }

            $verifikasi = $proposal->verifikasi;
            if (!$verifikasi) {
                // Fallback: Create verifikasi record if it doesn't exist
                $verifikasi = RekomendasiVerifikasi::create([
                    'rekomendasi_aplikasi_form_id' => $proposal->id,
                    'verifikator_id' => auth()->id(),
                    'status' => 'sedang_diverifikasi',
                    'tanggal_mulai_verifikasi' => now(),
                ]);
            } else {
                $verifikasi->update([
                    'verifikator_id' => auth()->id(),
                    'status' => 'sedang_diverifikasi',
                    'tanggal_mulai_verifikasi' => now(),
                ]);
            }

            $proposal->update(['status' => 'diproses']);

            // Log activity
            $proposal->logActivity(
                'Verifikasi Dimulai',
                'Verifikasi dimulai oleh ' . auth()->user()->name
            );

            return redirect()
                ->route('admin.rekomendasi.verifikasi.verify', $proposal->id)
                ->with('success', 'Verifikasi dimulai. Silakan lengkapi checklist verifikasi.');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Show verification form with checklist.
     */
    public function verify($id)
    {
        $proposal = RekomendasiAplikasiForm::with([
            'user.unitKerja',
            'pemilikProsesBisnis',
            'dokumenUsulan.uploader',
            'verifikasi.verifikator',
        ])->findOrFail($id);

        $verifikasi = $proposal->verifikasi;

        if (!$verifikasi || $verifikasi->status === 'menunggu') {
            return redirect()
                ->route('admin.rekomendasi.verifikasi.startVerification', $id)
                ->with('info', 'Silakan mulai verifikasi terlebih dahulu.');
        }

        return view('admin.rekomendasi.verifikasi.verify', compact('proposal', 'verifikasi'));
    }

    /**
     * Update verification checklist.
     */
    public function updateChecklist(Request $request, $id)
    {
        $request->validate([
            'checklist_analisis_kebutuhan' => 'nullable|boolean',
            'checklist_perencanaan' => 'nullable|boolean',
            'checklist_manajemen_risiko' => 'nullable|boolean',
            'checklist_kelengkapan_data' => 'nullable|boolean',
            'checklist_kesesuaian_peraturan' => 'nullable|boolean',
            'catatan_internal' => 'nullable|string',
        ]);

        try {
            $proposal = RekomendasiAplikasiForm::findOrFail($id);
            $verifikasi = $proposal->verifikasi;

            if (!$verifikasi) {
                return redirect()
                    ->back()
                    ->with('error', 'Data verifikasi tidak ditemukan.');
            }

            $verifikasi->update([
                'checklist_analisis_kebutuhan' => $request->has('checklist_analisis_kebutuhan'),
                'checklist_perencanaan' => $request->has('checklist_perencanaan'),
                'checklist_manajemen_risiko' => $request->has('checklist_manajemen_risiko'),
                'checklist_kelengkapan_data' => $request->has('checklist_kelengkapan_data'),
                'checklist_kesesuaian_peraturan' => $request->has('checklist_kesesuaian_peraturan'),
                'catatan_internal' => $request->catatan_internal,
            ]);

            // Log activity
            $proposal->logActivity(
                'Checklist Verifikasi Diperbarui',
                'Checklist verifikasi diperbarui oleh ' . auth()->user()->name
            );

            return redirect()
                ->back()
                ->with('success', 'Checklist verifikasi berhasil diperbarui.');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Approve verification.
     */
    public function approve(Request $request, $id)
    {
        $request->validate([
            'catatan_verifikasi' => 'nullable|string|max:1000',
        ]);

        try {
            \DB::beginTransaction();

            $proposal = RekomendasiAplikasiForm::findOrFail($id);
            $verifikasi = $proposal->verifikasi;

            if (!$verifikasi) {
                return redirect()
                    ->back()
                    ->with('error', 'Data verifikasi tidak ditemukan.');
            }

            // Check if all checklists are completed
            if (!$verifikasi->isAllChecklistCompleted()) {
                return redirect()
                    ->back()
                    ->with('error', 'Harap lengkapi semua checklist sebelum menyetujui usulan.');
            }

            $verifikasi->update([
                'status' => 'disetujui',
                'tanggal_verifikasi' => now(),
                'catatan_verifikasi' => $request->catatan_verifikasi,
            ]);

            $proposal->update([
                'status' => 'disetujui',
                'fase_saat_ini' => 'menunggu_kementerian',
            ]);

            // Log activity
            $proposal->logActivity(
                'Verifikasi Disetujui',
                'Usulan disetujui oleh verifikator: ' . auth()->user()->name
            );

            \DB::commit();

            // TODO: Send notification to user

            return redirect()
                ->route('admin.rekomendasi.verifikasi.index')
                ->with('success', 'Usulan berhasil disetujui. Proses selanjutnya: Menunggu persetujuan Kementerian Komdigi.');

        } catch (\Exception $e) {
            \DB::rollBack();

            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Reject verification.
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'catatan_verifikasi' => 'required|string|max:1000',
        ]);

        try {
            \DB::beginTransaction();

            $proposal = RekomendasiAplikasiForm::findOrFail($id);
            $verifikasi = $proposal->verifikasi;

            if (!$verifikasi) {
                return redirect()
                    ->back()
                    ->with('error', 'Data verifikasi tidak ditemukan.');
            }

            $verifikasi->update([
                'status' => 'ditolak',
                'tanggal_verifikasi' => now(),
                'catatan_verifikasi' => $request->catatan_verifikasi,
            ]);

            $proposal->update([
                'status' => 'ditolak',
                'fase_saat_ini' => 'usulan',
            ]);

            // Log activity
            $proposal->logActivity(
                'Verifikasi Ditolak',
                'Usulan ditolak oleh verifikator: ' . auth()->user()->name . '. Alasan: ' . $request->catatan_verifikasi
            );

            \DB::commit();

            // TODO: Send notification to user

            return redirect()
                ->route('admin.rekomendasi.verifikasi.index')
                ->with('success', 'Usulan ditolak. Notifikasi telah dikirim ke pemohon.');

        } catch (\Exception $e) {
            \DB::rollBack();

            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Request revision.
     */
    public function requestRevision(Request $request, $id)
    {
        $request->validate([
            'catatan_verifikasi' => 'required|string|max:1000',
        ]);

        try {
            \DB::beginTransaction();

            $proposal = RekomendasiAplikasiForm::findOrFail($id);
            $verifikasi = $proposal->verifikasi;

            if (!$verifikasi) {
                return redirect()
                    ->back()
                    ->with('error', 'Data verifikasi tidak ditemukan.');
            }

            $verifikasi->update([
                'status' => 'perlu_revisi',
                'tanggal_verifikasi' => now(),
                'catatan_verifikasi' => $request->catatan_verifikasi,
            ]);

            $proposal->update([
                'status' => 'perlu_revisi',
                'fase_saat_ini' => 'usulan',
            ]);

            // Log activity
            $proposal->logActivity(
                'Revisi Diminta',
                'Revisi diminta oleh verifikator: ' . auth()->user()->name . '. Catatan: ' . $request->catatan_verifikasi
            );

            \DB::commit();

            // TODO: Send notification to user

            return redirect()
                ->route('admin.rekomendasi.verifikasi.index')
                ->with('success', 'Permintaan revisi berhasil dikirim ke pemohon.');

        } catch (\Exception $e) {
            \DB::rollBack();

            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Download document for verification.
     */
    public function downloadDokumen($id, $dokumenId)
    {
        try {
            $proposal = RekomendasiAplikasiForm::findOrFail($id);
            $dokumen = $proposal->dokumenUsulan()->findOrFail($dokumenId);

            $filePath = storage_path('app/public/' . $dokumen->file_path);

            if (!file_exists($filePath)) {
                return redirect()
                    ->back()
                    ->with('error', 'File tidak ditemukan.');
            }

            return response()->download($filePath, basename($dokumen->file_path));

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Update ministry (Kementerian) approval status.
     */
    public function updateMinistryStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:menunggu,diproses,disetujui,ditolak,revisi_diminta',
            'nomor_surat_respons' => 'nullable|string|max:255',
            'tanggal_surat_respons' => 'nullable|date',
            'file_respons' => 'nullable|file|mimes:pdf|max:10240',
            'catatan' => 'nullable|string|max:2000',
        ]);

        try {
            \DB::beginTransaction();

            $proposal = RekomendasiAplikasiForm::findOrFail($id);

            if ($proposal->status !== 'disetujui') {
                \DB::rollBack();
                return redirect()
                    ->back()
                    ->with('error', 'Hanya usulan yang sudah disetujui DKISP yang dapat diupdate status Kementerian.');
            }

            // Get or create status kementerian
            $statusKementerian = $proposal->statusKementerian;

            \Log::info('Ministry Status Debug - Proposal ID: ' . $proposal->id);
            \Log::info('Ministry Status Debug - StatusKementerian exists: ' . ($statusKementerian ? 'YES (ID: '.$statusKementerian->id.')' : 'NO'));

            $data = [
                'rekomendasi_aplikasi_form_id' => $proposal->id,
                'status' => $request->status,
                'nomor_surat_respons' => $request->nomor_surat_respons,
                'tanggal_surat_respons' => $request->tanggal_surat_respons,
                'catatan_internal' => $request->catatan,
                'updated_by' => auth()->id(),
            ];

            \Log::info('Ministry Status Debug - Data to save:', $data);

            // Handle file upload
            if ($request->hasFile('file_respons')) {
                \Log::info('Ministry Status Debug - File upload detected');
                // Delete old file if exists
                if ($statusKementerian && $statusKementerian->file_respons_path) {
                    Storage::disk('public')->delete($statusKementerian->file_respons_path);
                    \Log::info('Ministry Status Debug - Old file deleted: ' . $statusKementerian->file_respons_path);
                }

                $file = $request->file('file_respons');
                $filename = 'surat_kementerian_' . $proposal->ticket_number . '_' . time() . '.pdf';
                $path = $file->storeAs('rekomendasi/surat_kementerian', $filename, 'public');
                $data['file_respons_path'] = $path;
                \Log::info('Ministry Status Debug - File uploaded to: ' . $path);
            }

            if ($statusKementerian) {
                \Log::info('Ministry Status Debug - Calling UPDATE...');
                $result = $statusKementerian->update($data);
                \Log::info('Ministry Status Debug - Update result: ' . ($result ? 'TRUE' : 'FALSE'));
                \Log::info('Ministry Status Debug - After update:', $statusKementerian->fresh()->toArray());
            } else {
                \Log::info('Ministry Status Debug - Calling CREATE...');
                $statusKementerian = RekomendasiStatusKementerian::create($data);
                \Log::info('Ministry Status Debug - Created with ID: ' . $statusKementerian->id);
                \Log::info('Ministry Status Debug - Created data:', $statusKementerian->toArray());
            }

            // Update proposal fase based on status
            \Log::info('Ministry Status Debug - Updating proposal fase...');
            if ($request->status === 'disetujui') {
                $proposal->update(['fase_saat_ini' => 'pengembangan']);
                \Log::info('Ministry Status Debug - Fase updated to: pengembangan');
            } elseif ($request->status === 'ditolak') {
                $proposal->update(['fase_saat_ini' => 'ditolak']);
                \Log::info('Ministry Status Debug - Fase updated to: ditolak');
            } elseif (in_array($request->status, ['menunggu', 'diproses'])) {
                $proposal->update(['fase_saat_ini' => 'menunggu_kementerian']);
                \Log::info('Ministry Status Debug - Fase updated to: menunggu_kementerian');
            }

            // Log activity
            $statusLabels = [
                'menunggu' => 'Menunggu Respons Kementerian',
                'diproses' => 'Sedang Diproses Kementerian',
                'disetujui' => 'Disetujui Kementerian Komdigi',
                'ditolak' => 'Ditolak Kementerian Komdigi',
                'revisi_diminta' => 'Kementerian Meminta Revisi',
            ];
            $proposal->logActivity(
                'Status Kementerian Diperbarui',
                'Status persetujuan Kementerian diubah menjadi: ' . ($statusLabels[$request->status] ?? $request->status)
            );

            \Log::info('Ministry Status Debug - About to commit transaction...');
            \DB::commit();
            \Log::info('Ministry Status Debug - Transaction committed successfully!');

            return redirect()
                ->back()
                ->with('success', 'Status persetujuan Kementerian berhasil diperbarui.');

        } catch (\Exception $e) {
            \DB::rollBack();

            \Log::error('Ministry Status Update Error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());

            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Export proposal detail to PDF.
     */
    public function exportPdf($id)
    {
        try {
            $proposal = RekomendasiAplikasiForm::with([
                'user.unitKerja',
                'pemilikProsesBisnis',
                'dokumenUsulan.uploader',
                'verifikasi.verifikator',
                'historiAktivitas.user',
                'statusKementerian'
            ])->findOrFail($id);

            // Configure DomPDF options
            $options = new Options();
            $options->set('isRemoteEnabled', true);
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isPhpEnabled', true);
            $options->set('defaultFont', 'DejaVu Sans');

            // Create DomPDF instance
            $dompdf = new Dompdf($options);

            // Encode logo as base64 for PDF embedding
            $logoPath = public_path('logokaltarafix.png');
            $logoBase64 = '';
            if (file_exists($logoPath)) {
                $logoBase64 = base64_encode(file_get_contents($logoPath));
            }

            // Load HTML content from view
            $html = view('admin.rekomendasi.verifikasi.pdf', compact('proposal', 'logoBase64'))->render();
            $dompdf->loadHtml($html);

            // Set paper size and orientation
            $dompdf->setPaper('A4', 'portrait');

            // Render PDF
            $dompdf->render();

            // Generate filename
            $filename = 'Usulan_Rekomendasi_' . $proposal->ticket_number . '.pdf';

            // Log activity
            $proposal->logActivity(
                'PDF Diekspor',
                'Dokumen usulan rekomendasi diekspor ke PDF oleh ' . auth()->user()->name
            );

            // Stream PDF to browser
            return response()->streamDownload(function() use ($dompdf) {
                echo $dompdf->output();
            }, $filename, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $filename . '"'
            ]);

        } catch (\Exception $e) {
            \Log::error('PDF Export Error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());

            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan saat mengekspor PDF: ' . $e->getMessage());
        }
    }
}
