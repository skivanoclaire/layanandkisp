<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RekomendasiAplikasiForm;
use App\Models\RekomendasiVerifikasi;
use Illuminate\Http\Request;

class RekomendasiVerifikasiController extends Controller
{
    /**
     * Display a listing of proposals awaiting verification.
     */
    public function index(Request $request)
    {
        $query = RekomendasiAplikasiForm::whereIn('status', ['diajukan', 'perlu_revisi', 'diproses'])
            ->with(['user.unitKerja', 'pemilikProsesBisnis', 'verifikasi.verifikator', 'dokumenUsulan'])
            ->latest();

        // Filter by verification status
        if ($request->filled('verification_status')) {
            $query->whereHas('verifikasi', function ($q) use ($request) {
                $q->where('status', $request->verification_status);
            });
        }

        // Filter by proposal status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
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
            'historiAktivitas.user'
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
                return redirect()
                    ->back()
                    ->with('error', 'Data verifikasi tidak ditemukan.');
            }

            $verifikasi->update([
                'verifikator_id' => auth()->id(),
                'status' => 'sedang_diverifikasi',
                'tanggal_mulai_verifikasi' => now(),
            ]);

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
            'checklist_analisis_kebutuhan' => 'boolean',
            'checklist_perencanaan' => 'boolean',
            'checklist_manajemen_risiko' => 'boolean',
            'checklist_kelengkapan_data' => 'boolean',
            'checklist_kesesuaian_peraturan' => 'boolean',
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
                'fase_saat_ini' => 'penandatanganan',
            ]);

            // Log activity
            $proposal->logActivity(
                'Verifikasi Disetujui',
                'Usulan disetujui oleh verifikator: ' . auth()->user()->name
            );

            \DB::commit();

            // TODO: Send notification to user
            // TODO: Trigger letter generation process

            return redirect()
                ->route('admin.rekomendasi.verifikasi.index')
                ->with('success', 'Usulan berhasil disetujui. Proses selanjutnya: Penandatanganan surat.');

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
}
