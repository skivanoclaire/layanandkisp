<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RekomendasiAplikasiForm;
use App\Models\RekomendasiSurat;
use Illuminate\Http\Request;

class RekomendasiSuratController extends Controller
{
    /**
     * Display a listing of approved proposals ready for letter generation.
     */
    public function index(Request $request)
    {
        $query = RekomendasiAplikasiForm::where('status', 'disetujui')
            ->where('fase_saat_ini', 'penandatanganan')
            ->with(['user', 'pemilikProsesBisnis', 'verifikasi', 'surat.statusKementerian'])
            ->latest();

        // Search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama_aplikasi', 'like', '%' . $request->search . '%')
                  ->orWhere('ticket_number', 'like', '%' . $request->search . '%');
            });
        }

        // Filter by letter status
        if ($request->filled('surat_status')) {
            if ($request->surat_status === 'belum_dibuat') {
                $query->doesntHave('surat');
            } else {
                $query->whereHas('surat', function ($q) use ($request) {
                    if ($request->surat_status === 'draft') {
                        $q->whereNull('file_signed_path');
                    } elseif ($request->surat_status === 'signed') {
                        $q->whereNotNull('file_signed_path');
                    }
                });
            }
        }

        $proposals = $query->paginate(15);

        return view('admin.rekomendasi.surat.index', compact('proposals'));
    }

    /**
     * Show letter generation form.
     */
    public function create($proposalId)
    {
        $proposal = RekomendasiAplikasiForm::with([
            'user',
            'pemilikProsesBisnis',
            'verifikasi',
            'dokumenUsulan'
        ])->findOrFail($proposalId);

        // Check if proposal is approved
        if ($proposal->status !== 'disetujui') {
            return redirect()
                ->back()
                ->with('error', 'Surat hanya dapat dibuat untuk usulan yang telah disetujui.');
        }

        // Check if letter already exists
        if ($proposal->surat) {
            return redirect()
                ->route('admin.rekomendasi.surat.edit', $proposal->surat->id)
                ->with('info', 'Surat sudah dibuat sebelumnya. Anda dapat mengeditnya.');
        }

        // Generate draft letter number
        $draftNomor = RekomendasiSurat::generateNomorSurat();

        return view('admin.rekomendasi.surat.create', compact('proposal', 'draftNomor'));
    }

    /**
     * Store a newly created letter.
     */
    public function store(Request $request, $proposalId)
    {
        $request->validate([
            'nomor_surat_draft' => 'required|string|max:100',
            'tanggal_surat' => 'required|date',
            'perihal' => 'required|string|max:500',
            'tujuan_surat' => 'required|string|max:500',
            'referensi_hukum' => 'nullable|array',
            'referensi_hukum.*' => 'string|max:500',
            'isi_surat' => 'required|string',
        ]);

        try {
            \DB::beginTransaction();

            $proposal = RekomendasiAplikasiForm::findOrFail($proposalId);

            // Create letter
            $surat = $proposal->surat()->create([
                'nomor_surat_draft' => $request->nomor_surat_draft,
                'tanggal_surat' => $request->tanggal_surat,
                'perihal' => $request->perihal,
                'tujuan_surat' => $request->tujuan_surat,
                'referensi_hukum' => $request->referensi_hukum ?? [],
                'isi_surat' => $request->isi_surat,
                'template_content' => $request->isi_surat,
                'created_by' => auth()->id(),
            ]);

            // Log activity
            $proposal->logActivity(
                'Surat Rekomendasi Dibuat',
                'Draft surat rekomendasi dibuat dengan nomor: ' . $request->nomor_surat_draft
            );

            \DB::commit();

            return redirect()
                ->route('admin.rekomendasi.surat.show', $surat->id)
                ->with('success', 'Draft surat berhasil dibuat. Silakan review dan tanda tangani.');

        } catch (\Exception $e) {
            \DB::rollBack();

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified letter.
     */
    public function show($id)
    {
        $surat = RekomendasiSurat::with([
            'proposal.user',
            'proposal.pemilikProsesBisnis',
            'proposal.verifikasi',
            'statusKementerian',
            'pengiriman',
            'creator'
        ])->findOrFail($id);

        return view('admin.rekomendasi.surat.show', compact('surat'));
    }

    /**
     * Show the form for editing the specified letter.
     */
    public function edit($id)
    {
        $surat = RekomendasiSurat::with([
            'proposal.user',
            'proposal.pemilikProsesBisnis'
        ])->findOrFail($id);

        // Cannot edit if already signed
        if ($surat->isSigned()) {
            return redirect()
                ->route('admin.rekomendasi.surat.show', $surat->id)
                ->with('error', 'Surat yang sudah ditandatangani tidak dapat diedit.');
        }

        return view('admin.rekomendasi.surat.edit', compact('surat'));
    }

    /**
     * Update the specified letter.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nomor_surat_draft' => 'required|string|max:100',
            'tanggal_surat' => 'required|date',
            'perihal' => 'required|string|max:500',
            'tujuan_surat' => 'required|string|max:500',
            'referensi_hukum' => 'nullable|array',
            'referensi_hukum.*' => 'string|max:500',
            'isi_surat' => 'required|string',
        ]);

        try {
            \DB::beginTransaction();

            $surat = RekomendasiSurat::findOrFail($id);

            // Cannot edit if already signed
            if ($surat->isSigned()) {
                return redirect()
                    ->back()
                    ->with('error', 'Surat yang sudah ditandatangani tidak dapat diedit.');
            }

            $surat->update([
                'nomor_surat_draft' => $request->nomor_surat_draft,
                'tanggal_surat' => $request->tanggal_surat,
                'perihal' => $request->perihal,
                'tujuan_surat' => $request->tujuan_surat,
                'referensi_hukum' => $request->referensi_hukum ?? [],
                'isi_surat' => $request->isi_surat,
                'template_content' => $request->isi_surat,
            ]);

            // Log activity
            $surat->proposal->logActivity(
                'Surat Rekomendasi Diperbarui',
                'Draft surat rekomendasi diperbarui'
            );

            \DB::commit();

            return redirect()
                ->route('admin.rekomendasi.surat.show', $surat->id)
                ->with('success', 'Draft surat berhasil diperbarui.');

        } catch (\Exception $e) {
            \DB::rollBack();

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Sign the letter (upload signed PDF).
     */
    public function sign(Request $request, $id)
    {
        $request->validate([
            'nomor_surat_final' => 'required|string|max:100',
            'file_signed' => 'required|file|mimes:pdf|max:10240',
            'tanggal_ttd' => 'required|date',
            'nama_penandatangan' => 'required|string|max:255',
            'jabatan_penandatangan' => 'required|string|max:255',
        ]);

        try {
            \DB::beginTransaction();

            $surat = RekomendasiSurat::findOrFail($id);

            if ($surat->isSigned()) {
                return redirect()
                    ->back()
                    ->with('error', 'Surat sudah ditandatangani sebelumnya.');
            }

            // Store signed file
            $file = $request->file('file_signed');
            $filename = 'surat_signed_' . $surat->proposal->ticket_number . '_' . time() . '.pdf';
            $path = $file->storeAs('rekomendasi/surat/signed', $filename, 'public');

            $surat->update([
                'nomor_surat_final' => $request->nomor_surat_final,
                'file_signed_path' => $path,
                'tanggal_ttd' => $request->tanggal_ttd,
                'nama_penandatangan' => $request->nama_penandatangan,
                'jabatan_penandatangan' => $request->jabatan_penandatangan,
            ]);

            // Log activity
            $surat->proposal->logActivity(
                'Surat Ditandatangani',
                'Surat rekomendasi ditandatangani dengan nomor final: ' . $request->nomor_surat_final
            );

            \DB::commit();

            return redirect()
                ->route('admin.rekomendasi.surat.show', $surat->id)
                ->with('success', 'Surat berhasil ditandatangani. Siap untuk dikirim ke kementerian.');

        } catch (\Exception $e) {
            \DB::rollBack();

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Record letter delivery to ministry.
     */
    public function recordDelivery(Request $request, $id)
    {
        $request->validate([
            'metode_pengiriman' => 'required|in:pos,email,online,kurir',
            'tanggal_pengiriman' => 'required|date',
            'penerima' => 'required|string|max:255',
            'nomor_resi' => 'nullable|string|max:100',
            'catatan_pengiriman' => 'nullable|string|max:1000',
            'file_bukti' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        try {
            \DB::beginTransaction();

            $surat = RekomendasiSurat::findOrFail($id);

            if (!$surat->isSigned()) {
                return redirect()
                    ->back()
                    ->with('error', 'Surat harus ditandatangani terlebih dahulu sebelum dikirim.');
            }

            // Store proof file if provided
            $fileBuktiPath = null;
            if ($request->hasFile('file_bukti')) {
                $file = $request->file('file_bukti');
                $filename = 'bukti_kirim_' . $surat->proposal->ticket_number . '_' . time() . '.' . $file->getClientOriginalExtension();
                $fileBuktiPath = $file->storeAs('rekomendasi/surat/bukti', $filename, 'public');
            }

            // Create delivery record
            $pengiriman = $surat->pengiriman()->create([
                'metode_pengiriman' => $request->metode_pengiriman,
                'tanggal_pengiriman' => $request->tanggal_pengiriman,
                'penerima' => $request->penerima,
                'nomor_resi' => $request->nomor_resi,
                'catatan_pengiriman' => $request->catatan_pengiriman,
                'file_bukti_path' => $fileBuktiPath,
                'dikirim_oleh' => auth()->id(),
            ]);

            // Create ministry status tracking
            $surat->statusKementerian()->create([
                'status' => 'terkirim',
                'tanggal_update' => now(),
            ]);

            // Update proposal phase
            $surat->proposal->update([
                'fase_saat_ini' => 'menunggu_kementerian',
            ]);

            // Log activity
            $surat->proposal->logActivity(
                'Surat Dikirim ke Kementerian',
                'Surat dikirim via ' . $request->metode_pengiriman . ' kepada ' . $request->penerima
            );

            \DB::commit();

            return redirect()
                ->route('admin.rekomendasi.surat.show', $surat->id)
                ->with('success', 'Pengiriman surat berhasil dicatat. Status: Menunggu Respons Kementerian.');

        } catch (\Exception $e) {
            \DB::rollBack();

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Update ministry response status.
     */
    public function updateMinistryStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:terkirim,diproses,disetujui,ditolak,perlu_revisi',
            'tanggal_respons' => 'nullable|date',
            'file_respons' => 'nullable|file|mimes:pdf|max:10240',
            'catatan_kementerian' => 'nullable|string|max:2000',
            'catatan_revisi' => 'nullable|array',
            'catatan_revisi.*' => 'string|max:1000',
        ]);

        try {
            \DB::beginTransaction();

            $surat = RekomendasiSurat::findOrFail($id);
            $statusKementerian = $surat->statusKementerian;

            if (!$statusKementerian) {
                return redirect()
                    ->back()
                    ->with('error', 'Surat belum dikirim ke kementerian.');
            }

            // Store response file if provided
            $fileResponsPath = null;
            if ($request->hasFile('file_respons')) {
                $file = $request->file('file_respons');
                $filename = 'respons_kementerian_' . $surat->proposal->ticket_number . '_' . time() . '.pdf';
                $fileResponsPath = $file->storeAs('rekomendasi/surat/respons', $filename, 'public');
            }

            $statusKementerian->update([
                'status' => $request->status,
                'tanggal_respons' => $request->tanggal_respons,
                'file_respons_path' => $fileResponsPath ?? $statusKementerian->file_respons_path,
                'catatan_kementerian' => $request->catatan_kementerian,
                'catatan_revisi' => $request->catatan_revisi ?? [],
                'tanggal_update' => now(),
            ]);

            // Update proposal phase based on ministry response
            if ($request->status === 'disetujui') {
                $surat->proposal->update([
                    'fase_saat_ini' => 'pengembangan',
                ]);
            }

            // Log activity
            $surat->proposal->logActivity(
                'Status Kementerian Diperbarui',
                'Status dari kementerian: ' . $request->status
            );

            \DB::commit();

            return redirect()
                ->route('admin.rekomendasi.surat.show', $surat->id)
                ->with('success', 'Status respons kementerian berhasil diperbarui.');

        } catch (\Exception $e) {
            \DB::rollBack();

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Download signed letter PDF.
     */
    public function downloadSigned($id)
    {
        try {
            $surat = RekomendasiSurat::findOrFail($id);

            if (!$surat->isSigned()) {
                return redirect()
                    ->back()
                    ->with('error', 'Surat belum ditandatangani.');
            }

            $filePath = storage_path('app/public/' . $surat->file_signed_path);

            if (!file_exists($filePath)) {
                return redirect()
                    ->back()
                    ->with('error', 'File tidak ditemukan.');
            }

            return response()->download($filePath, 'Surat_Rekomendasi_' . $surat->proposal->ticket_number . '.pdf');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
