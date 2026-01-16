<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\RekomendasiAplikasiForm;
use App\Models\RekomendasiFasePengembangan;
use App\Models\RekomendasiDokumenPengembangan;
use App\Models\RekomendasiMilestone;
use App\Models\RekomendasiTimPengembangan;
use Illuminate\Http\Request;

class RekomendasiFasePengembanganController extends Controller
{
    /**
     * Display all development phases for a proposal.
     */
    public function index($rekomendasiId)
    {
        $proposal = RekomendasiAplikasiForm::where('id', $rekomendasiId)
            ->where('user_id', auth()->id())
            ->whereIn('fase_saat_ini', ['pengembangan', 'selesai'])
            ->with([
                'fasePengembangan.dokumenPengembangan',
                'fasePengembangan.milestones',
                'timPengembangan',
                'surat.statusKementerian'
            ])
            ->firstOrFail();

        // Ensure all 5 phases exist
        $this->ensureAllPhasesExist($proposal);

        return view('user.rekomendasi.fase-pengembangan.index', compact('proposal'));
    }

    /**
     * Show specific phase details.
     */
    public function show($rekomendasiId, $faseId)
    {
        $proposal = RekomendasiAplikasiForm::where('id', $rekomendasiId)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $fase = RekomendasiFasePengembangan::where('id', $faseId)
            ->where('rekomendasi_aplikasi_form_id', $rekomendasiId)
            ->with(['dokumenPengembangan.uploader', 'milestones'])
            ->firstOrFail();

        return view('user.rekomendasi.fase-pengembangan.show', compact('proposal', 'fase'));
    }

    /**
     * Update phase progress.
     */
    public function updateProgress(Request $request, $rekomendasiId, $faseId)
    {
        $request->validate([
            'progress_persen' => 'required|integer|min:0|max:100',
            'keterangan' => 'nullable|string|max:1000',
        ]);

        try {
            $fase = RekomendasiFasePengembangan::where('id', $faseId)
                ->where('rekomendasi_aplikasi_form_id', $rekomendasiId)
                ->firstOrFail();

            $fase->update([
                'progress_persen' => $request->progress_persen,
                'keterangan' => $request->keterangan,
            ]);

            // Auto-update status based on progress
            if ($request->progress_persen == 0) {
                $fase->update(['status' => 'belum_mulai']);
            } elseif ($request->progress_persen > 0 && $request->progress_persen < 100) {
                $fase->update(['status' => 'sedang_berjalan']);
                if (!$fase->tanggal_mulai) {
                    $fase->update(['tanggal_mulai' => now()]);
                }
            } elseif ($request->progress_persen == 100) {
                $fase->update([
                    'status' => 'selesai',
                    'tanggal_selesai' => now(),
                ]);
            }

            // Log activity
            $fase->proposal->logActivity(
                'Progress Fase Diperbarui',
                "Progress fase {$fase->fase} diperbarui menjadi {$request->progress_persen}%"
            );

            return redirect()
                ->back()
                ->with('success', 'Progress berhasil diperbarui.');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Upload phase document.
     */
    public function uploadDokumen(Request $request, $rekomendasiId, $faseId)
    {
        $request->validate([
            'jenis_dokumen' => 'required|string|max:100',
            'kategori' => 'required|in:dokumentasi,timeline,tim,pengembangan,instalasi,antarmuka,sosialisasi,serah_terima,testing',
            'file' => 'required|file|max:10240', // 10MB max
            'keterangan' => 'nullable|string|max:500',
        ]);

        try {
            \DB::beginTransaction();

            $fase = RekomendasiFasePengembangan::where('id', $faseId)
                ->where('rekomendasi_aplikasi_form_id', $rekomendasiId)
                ->firstOrFail();

            // Store file
            $file = $request->file('file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('rekomendasi/fase-pengembangan', $filename, 'public');

            // Create document record
            $dokumen = $fase->dokumenPengembangan()->create([
                'jenis_dokumen' => $request->jenis_dokumen,
                'kategori' => $request->kategori,
                'nama_file' => $file->getClientOriginalName(),
                'file_path' => $path,
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'keterangan' => $request->keterangan,
                'uploaded_by' => auth()->id(),
            ]);

            // Log activity
            $fase->proposal->logActivity(
                'Dokumen Pengembangan Diupload',
                "Dokumen {$request->jenis_dokumen} diupload untuk fase {$fase->fase}"
            );

            \DB::commit();

            return redirect()
                ->back()
                ->with('success', 'Dokumen berhasil diupload.');

        } catch (\Exception $e) {
            \DB::rollBack();

            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Delete phase document.
     */
    public function deleteDokumen($rekomendasiId, $faseId, $dokumenId)
    {
        try {
            $dokumen = RekomendasiDokumenPengembangan::where('id', $dokumenId)
                ->whereHas('fasePengembangan', function ($q) use ($rekomendasiId, $faseId) {
                    $q->where('id', $faseId)
                      ->where('rekomendasi_aplikasi_form_id', $rekomendasiId);
                })
                ->firstOrFail();

            // Delete file from storage
            if (\Storage::disk('public')->exists($dokumen->file_path)) {
                \Storage::disk('public')->delete($dokumen->file_path);
            }

            $namaFile = $dokumen->nama_file;
            $dokumen->delete();

            return redirect()
                ->back()
                ->with('success', "Dokumen '{$namaFile}' berhasil dihapus.");

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Create milestone.
     */
    public function createMilestone(Request $request, $rekomendasiId, $faseId)
    {
        $request->validate([
            'nama_milestone' => 'required|string|max:255',
            'target_tanggal' => 'required|date',
            'keterangan' => 'nullable|string|max:500',
        ]);

        try {
            $fase = RekomendasiFasePengembangan::where('id', $faseId)
                ->where('rekomendasi_aplikasi_form_id', $rekomendasiId)
                ->firstOrFail();

            $milestone = $fase->milestones()->create([
                'nama_milestone' => $request->nama_milestone,
                'target_tanggal' => $request->target_tanggal,
                'status' => 'not_started',
                'keterangan' => $request->keterangan,
            ]);

            // Log activity
            $fase->proposal->logActivity(
                'Milestone Dibuat',
                "Milestone '{$request->nama_milestone}' dibuat untuk fase {$fase->fase}"
            );

            return redirect()
                ->back()
                ->with('success', 'Milestone berhasil dibuat.');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Update milestone.
     */
    public function updateMilestone(Request $request, $rekomendasiId, $faseId, $milestoneId)
    {
        $request->validate([
            'status' => 'required|in:not_started,in_progress,completed',
            'file_bukti' => 'nullable|file|max:5120', // 5MB max
            'keterangan' => 'nullable|string|max:500',
        ]);

        try {
            \DB::beginTransaction();

            $milestone = RekomendasiMilestone::where('id', $milestoneId)
                ->whereHas('fasePengembangan', function ($q) use ($rekomendasiId, $faseId) {
                    $q->where('id', $faseId)
                      ->where('rekomendasi_aplikasi_form_id', $rekomendasiId);
                })
                ->firstOrFail();

            $updateData = [
                'status' => $request->status,
                'keterangan' => $request->keterangan,
            ];

            // Handle file upload
            if ($request->hasFile('file_bukti')) {
                // Delete old file if exists
                if ($milestone->file_bukti && \Storage::disk('public')->exists($milestone->file_bukti)) {
                    \Storage::disk('public')->delete($milestone->file_bukti);
                }

                $file = $request->file('file_bukti');
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('rekomendasi/milestone-bukti', $filename, 'public');
                $updateData['file_bukti'] = $path;
            }

            $milestone->update($updateData);

            // Log activity
            $milestone->fasePengembangan->proposal->logActivity(
                'Milestone Diperbarui',
                "Status milestone '{$milestone->nama_milestone}' diperbarui menjadi {$request->status}"
            );

            \DB::commit();

            return redirect()
                ->back()
                ->with('success', 'Milestone berhasil diperbarui.');

        } catch (\Exception $e) {
            \DB::rollBack();

            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Delete milestone.
     */
    public function deleteMilestone($rekomendasiId, $faseId, $milestoneId)
    {
        try {
            $milestone = RekomendasiMilestone::where('id', $milestoneId)
                ->whereHas('fasePengembangan', function ($q) use ($rekomendasiId, $faseId) {
                    $q->where('id', $faseId)
                      ->where('rekomendasi_aplikasi_form_id', $rekomendasiId);
                })
                ->firstOrFail();

            $namaMilestone = $milestone->nama_milestone;
            $milestone->delete();

            return redirect()
                ->back()
                ->with('success', "Milestone '{$namaMilestone}' berhasil dihapus.");

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Manage development team.
     */
    public function manageTeam($rekomendasiId)
    {
        $proposal = RekomendasiAplikasiForm::where('id', $rekomendasiId)
            ->where('user_id', auth()->id())
            ->with('timPengembangan')
            ->firstOrFail();

        return view('user.rekomendasi.fase-pengembangan.team', compact('proposal'));
    }

    /**
     * Add team member.
     */
    public function addTeamMember(Request $request, $rekomendasiId)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'peran' => 'required|string|max:255',
            'kontak' => 'nullable|string|max:255',
        ]);

        try {
            $proposal = RekomendasiAplikasiForm::where('id', $rekomendasiId)
                ->where('user_id', auth()->id())
                ->firstOrFail();

            $proposal->timPengembangan()->create([
                'nama' => $request->nama,
                'peran' => $request->peran,
                'kontak' => $request->kontak,
            ]);

            // Log activity
            $proposal->logActivity(
                'Anggota Tim Ditambahkan',
                "Anggota tim '{$request->nama}' ditambahkan sebagai {$request->peran}"
            );

            return redirect()
                ->back()
                ->with('success', 'Anggota tim berhasil ditambahkan.');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Delete team member.
     */
    public function deleteTeamMember($rekomendasiId, $teamId)
    {
        try {
            $member = RekomendasiTimPengembangan::where('id', $teamId)
                ->where('rekomendasi_aplikasi_form_id', $rekomendasiId)
                ->firstOrFail();

            $nama = $member->nama;
            $member->delete();

            return redirect()
                ->back()
                ->with('success', "Anggota tim '{$nama}' berhasil dihapus.");

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Mark phase as complete.
     */
    public function markPhaseComplete($rekomendasiId, $faseId)
    {
        try {
            \DB::beginTransaction();

            $fase = RekomendasiFasePengembangan::where('id', $faseId)
                ->where('rekomendasi_aplikasi_form_id', $rekomendasiId)
                ->firstOrFail();

            $fase->update([
                'status' => 'selesai',
                'progress_persen' => 100,
                'tanggal_selesai' => now(),
            ]);

            // Log activity
            $fase->proposal->logActivity(
                'Fase Selesai',
                "Fase {$fase->fase} telah selesai dikerjakan"
            );

            \DB::commit();

            return redirect()
                ->back()
                ->with('success', 'Fase berhasil ditandai selesai.');

        } catch (\Exception $e) {
            \DB::rollBack();

            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Ensure all 5 development phases exist for the proposal.
     */
    private function ensureAllPhasesExist($proposal)
    {
        $phases = ['rancang_bangun', 'implementasi', 'uji_kelaikan', 'pemeliharaan', 'evaluasi'];

        foreach ($phases as $phase) {
            RekomendasiFasePengembangan::firstOrCreate(
                [
                    'rekomendasi_aplikasi_form_id' => $proposal->id,
                    'fase' => $phase,
                ],
                [
                    'status' => 'belum_mulai',
                    'progress_persen' => 0,
                ]
            );
        }
    }
}
