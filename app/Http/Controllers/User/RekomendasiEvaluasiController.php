<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\RekomendasiAplikasiForm;
use App\Models\RekomendasiEvaluasi;
use Illuminate\Http\Request;

class RekomendasiEvaluasiController extends Controller
{
    /**
     * Display a listing of evaluations for a proposal.
     */
    public function index($rekomendasiId)
    {
        $proposal = RekomendasiAplikasiForm::where('id', $rekomendasiId)
            ->where('user_id', auth()->id())
            ->with('evaluasi')
            ->firstOrFail();

        return view('user.rekomendasi.evaluasi.index', compact('proposal'));
    }

    /**
     * Show the form for creating a new evaluation.
     */
    public function create($rekomendasiId)
    {
        $proposal = RekomendasiAplikasiForm::where('id', $rekomendasiId)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        return view('user.rekomendasi.evaluasi.create', compact('proposal'));
    }

    /**
     * Store a newly created evaluation.
     */
    public function store(Request $request, $rekomendasiId)
    {
        $request->validate([
            'periode' => 'required|string|max:100',
            'tanggal_evaluasi' => 'required|date',
            'kebijakan_internal' => 'nullable|string',
            'rating_fungsionalitas' => 'required|integer|min:1|max:5',
            'rating_keamanan' => 'required|integer|min:1|max:5',
            'rating_performance' => 'required|integer|min:1|max:5',
            'rating_ux' => 'required|integer|min:1|max:5',
            'jumlah_pengguna' => 'nullable|integer|min:0',
            'frekuensi_akses' => 'nullable|string|max:255',
            'fitur_populer' => 'nullable|string',
            'file_survey' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx|max:10240',
            'feedback_pengguna' => 'nullable|string',
            'file_laporan_evaluasi' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
            'rekomendasi_tindak_lanjut' => 'required|in:tetap_digunakan,perlu_pengembangan,perlu_perbaikan,penghentian',
            'file_laporan_pimpinan' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
            'tanggal_penyampaian_pimpinan' => 'nullable|date',
        ]);

        try {
            \DB::beginTransaction();

            $proposal = RekomendasiAplikasiForm::where('id', $rekomendasiId)
                ->where('user_id', auth()->id())
                ->firstOrFail();

            // Handle file uploads
            $fileSurveyPath = null;
            if ($request->hasFile('file_survey')) {
                $file = $request->file('file_survey');
                $filename = 'survey_' . time() . '_' . $file->getClientOriginalName();
                $fileSurveyPath = $file->storeAs('rekomendasi/evaluasi/survey', $filename, 'public');
            }

            $fileLaporanEvaluasiPath = null;
            if ($request->hasFile('file_laporan_evaluasi')) {
                $file = $request->file('file_laporan_evaluasi');
                $filename = 'laporan_evaluasi_' . time() . '_' . $file->getClientOriginalName();
                $fileLaporanEvaluasiPath = $file->storeAs('rekomendasi/evaluasi/laporan', $filename, 'public');
            }

            $fileLaporanPimpinanPath = null;
            if ($request->hasFile('file_laporan_pimpinan')) {
                $file = $request->file('file_laporan_pimpinan');
                $filename = 'laporan_pimpinan_' . time() . '_' . $file->getClientOriginalName();
                $fileLaporanPimpinanPath = $file->storeAs('rekomendasi/evaluasi/pimpinan', $filename, 'public');
            }

            // Create evaluation
            $evaluasi = $proposal->evaluasi()->create([
                'periode' => $request->periode,
                'tanggal_evaluasi' => $request->tanggal_evaluasi,
                'kebijakan_internal' => $request->kebijakan_internal,
                'rating_fungsionalitas' => $request->rating_fungsionalitas,
                'rating_keamanan' => $request->rating_keamanan,
                'rating_performance' => $request->rating_performance,
                'rating_ux' => $request->rating_ux,
                'jumlah_pengguna' => $request->jumlah_pengguna,
                'frekuensi_akses' => $request->frekuensi_akses,
                'fitur_populer' => $request->fitur_populer,
                'file_survey' => $fileSurveyPath,
                'feedback_pengguna' => $request->feedback_pengguna,
                'file_laporan_evaluasi' => $fileLaporanEvaluasiPath,
                'rekomendasi_tindak_lanjut' => $request->rekomendasi_tindak_lanjut,
                'file_laporan_pimpinan' => $fileLaporanPimpinanPath,
                'tanggal_penyampaian_pimpinan' => $request->tanggal_penyampaian_pimpinan,
            ]);

            // Log activity
            $proposal->logActivity(
                'Evaluasi Dibuat',
                "Evaluasi periode {$request->periode} telah dibuat"
            );

            \DB::commit();

            return redirect()
                ->route('user.rekomendasi.evaluasi.show', [$rekomendasiId, $evaluasi->id])
                ->with('success', 'Evaluasi berhasil dibuat.');

        } catch (\Exception $e) {
            \DB::rollBack();

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified evaluation.
     */
    public function show($rekomendasiId, $evaluasiId)
    {
        $proposal = RekomendasiAplikasiForm::where('id', $rekomendasiId)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $evaluasi = RekomendasiEvaluasi::where('id', $evaluasiId)
            ->where('rekomendasi_aplikasi_form_id', $rekomendasiId)
            ->firstOrFail();

        return view('user.rekomendasi.evaluasi.show', compact('proposal', 'evaluasi'));
    }

    /**
     * Show the form for editing the specified evaluation.
     */
    public function edit($rekomendasiId, $evaluasiId)
    {
        $proposal = RekomendasiAplikasiForm::where('id', $rekomendasiId)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $evaluasi = RekomendasiEvaluasi::where('id', $evaluasiId)
            ->where('rekomendasi_aplikasi_form_id', $rekomendasiId)
            ->firstOrFail();

        return view('user.rekomendasi.evaluasi.edit', compact('proposal', 'evaluasi'));
    }

    /**
     * Update the specified evaluation.
     */
    public function update(Request $request, $rekomendasiId, $evaluasiId)
    {
        $request->validate([
            'periode' => 'required|string|max:100',
            'tanggal_evaluasi' => 'required|date',
            'kebijakan_internal' => 'nullable|string',
            'rating_fungsionalitas' => 'required|integer|min:1|max:5',
            'rating_keamanan' => 'required|integer|min:1|max:5',
            'rating_performance' => 'required|integer|min:1|max:5',
            'rating_ux' => 'required|integer|min:1|max:5',
            'jumlah_pengguna' => 'nullable|integer|min:0',
            'frekuensi_akses' => 'nullable|string|max:255',
            'fitur_populer' => 'nullable|string',
            'file_survey' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx|max:10240',
            'feedback_pengguna' => 'nullable|string',
            'file_laporan_evaluasi' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
            'rekomendasi_tindak_lanjut' => 'required|in:tetap_digunakan,perlu_pengembangan,perlu_perbaikan,penghentian',
            'file_laporan_pimpinan' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
            'tanggal_penyampaian_pimpinan' => 'nullable|date',
        ]);

        try {
            \DB::beginTransaction();

            $evaluasi = RekomendasiEvaluasi::where('id', $evaluasiId)
                ->where('rekomendasi_aplikasi_form_id', $rekomendasiId)
                ->firstOrFail();

            $updateData = [
                'periode' => $request->periode,
                'tanggal_evaluasi' => $request->tanggal_evaluasi,
                'kebijakan_internal' => $request->kebijakan_internal,
                'rating_fungsionalitas' => $request->rating_fungsionalitas,
                'rating_keamanan' => $request->rating_keamanan,
                'rating_performance' => $request->rating_performance,
                'rating_ux' => $request->rating_ux,
                'jumlah_pengguna' => $request->jumlah_pengguna,
                'frekuensi_akses' => $request->frekuensi_akses,
                'fitur_populer' => $request->fitur_populer,
                'feedback_pengguna' => $request->feedback_pengguna,
                'rekomendasi_tindak_lanjut' => $request->rekomendasi_tindak_lanjut,
                'tanggal_penyampaian_pimpinan' => $request->tanggal_penyampaian_pimpinan,
            ];

            // Handle file uploads
            if ($request->hasFile('file_survey')) {
                if ($evaluasi->file_survey && \Storage::disk('public')->exists($evaluasi->file_survey)) {
                    \Storage::disk('public')->delete($evaluasi->file_survey);
                }
                $file = $request->file('file_survey');
                $filename = 'survey_' . time() . '_' . $file->getClientOriginalName();
                $updateData['file_survey'] = $file->storeAs('rekomendasi/evaluasi/survey', $filename, 'public');
            }

            if ($request->hasFile('file_laporan_evaluasi')) {
                if ($evaluasi->file_laporan_evaluasi && \Storage::disk('public')->exists($evaluasi->file_laporan_evaluasi)) {
                    \Storage::disk('public')->delete($evaluasi->file_laporan_evaluasi);
                }
                $file = $request->file('file_laporan_evaluasi');
                $filename = 'laporan_evaluasi_' . time() . '_' . $file->getClientOriginalName();
                $updateData['file_laporan_evaluasi'] = $file->storeAs('rekomendasi/evaluasi/laporan', $filename, 'public');
            }

            if ($request->hasFile('file_laporan_pimpinan')) {
                if ($evaluasi->file_laporan_pimpinan && \Storage::disk('public')->exists($evaluasi->file_laporan_pimpinan)) {
                    \Storage::disk('public')->delete($evaluasi->file_laporan_pimpinan);
                }
                $file = $request->file('file_laporan_pimpinan');
                $filename = 'laporan_pimpinan_' . time() . '_' . $file->getClientOriginalName();
                $updateData['file_laporan_pimpinan'] = $file->storeAs('rekomendasi/evaluasi/pimpinan', $filename, 'public');
            }

            $evaluasi->update($updateData);

            // Log activity
            $evaluasi->proposal->logActivity(
                'Evaluasi Diperbarui',
                "Evaluasi periode {$request->periode} telah diperbarui"
            );

            \DB::commit();

            return redirect()
                ->route('user.rekomendasi.evaluasi.show', [$rekomendasiId, $evaluasi->id])
                ->with('success', 'Evaluasi berhasil diperbarui.');

        } catch (\Exception $e) {
            \DB::rollBack();

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified evaluation.
     */
    public function destroy($rekomendasiId, $evaluasiId)
    {
        try {
            $evaluasi = RekomendasiEvaluasi::where('id', $evaluasiId)
                ->where('rekomendasi_aplikasi_form_id', $rekomendasiId)
                ->firstOrFail();

            // Delete files
            if ($evaluasi->file_survey && \Storage::disk('public')->exists($evaluasi->file_survey)) {
                \Storage::disk('public')->delete($evaluasi->file_survey);
            }
            if ($evaluasi->file_laporan_evaluasi && \Storage::disk('public')->exists($evaluasi->file_laporan_evaluasi)) {
                \Storage::disk('public')->delete($evaluasi->file_laporan_evaluasi);
            }
            if ($evaluasi->file_laporan_pimpinan && \Storage::disk('public')->exists($evaluasi->file_laporan_pimpinan)) {
                \Storage::disk('public')->delete($evaluasi->file_laporan_pimpinan);
            }

            $periode = $evaluasi->periode;
            $evaluasi->delete();

            return redirect()
                ->route('user.rekomendasi.evaluasi.index', $rekomendasiId)
                ->with('success', "Evaluasi periode '{$periode}' berhasil dihapus.");

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
