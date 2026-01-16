<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\RekomendasiAplikasiForm;
use App\Models\UnitKerja;
use App\Http\Requests\Rekomendasi\StoreUsulanRequest;
use App\Http\Requests\Rekomendasi\StoreUsulanV2Request;
use App\Http\Requests\Rekomendasi\UploadDokumenRequest;
use App\Services\RekomendasiDocumentService;
use Illuminate\Http\Request;

class RekomendasiUsulanController extends Controller
{
    protected $documentService;

    public function __construct(RekomendasiDocumentService $documentService)
    {
        $this->documentService = $documentService;
    }

    /**
     * Display a listing of user's proposals.
     */
    public function index(Request $request)
    {
        $query = RekomendasiAplikasiForm::where('user_id', auth()->id())
            ->with(['pemilikProsesBisnis', 'verifikasi', 'surat.statusKementerian'])
            ->latest();

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by fase
        if ($request->filled('fase')) {
            $query->where('fase_saat_ini', $request->fase);
        }

        // Search by title
        if ($request->filled('search')) {
            $query->where('nama_aplikasi', 'like', '%' . $request->search . '%');
        }

        $proposals = $query->paginate(10);

        return view('user.rekomendasi.usulan.index', compact('proposals'));
    }

    /**
     * Show the form for creating a new proposal.
     */
    public function create()
    {
        $unitKerjaList = UnitKerja::orderBy('nama')->get();

        return view('user.rekomendasi.usulan.create', compact('unitKerjaList'));
    }

    /**
     * Store a newly created proposal in storage.
     */
    public function store(StoreUsulanV2Request $request)
    {
        try {
            \DB::beginTransaction();

            // Prepare data from validated request
            $data = $request->validated();
            $data['user_id'] = auth()->id();
            $data['status'] = 'draft';
            $data['fase_saat_ini'] = 'usulan';

            // Handle file upload for proses_bisnis_file
            if ($request->hasFile('proses_bisnis_file')) {
                $file = $request->file('proses_bisnis_file');
                $filename = 'proses_bisnis_' . time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('rekomendasi/proses_bisnis', $filename, 'public');
                $data['proses_bisnis_file'] = $path;
            }

            // Create the main form
            $form = RekomendasiAplikasiForm::create($data);

            // Log activity
            $form->logActivity(
                'Usulan Dibuat',
                'Usulan rekomendasi aplikasi dibuat dengan nama: ' . $form->nama_aplikasi
            );

            \DB::commit();

            return redirect()
                ->route('user.rekomendasi.usulan.show', $form->id)
                ->with('success', 'Usulan berhasil dibuat. Silakan upload dokumen pendukung.');

        } catch (\Exception $e) {
            \DB::rollBack();

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified proposal.
     */
    public function show($id)
    {
        $proposal = RekomendasiAplikasiForm::where('id', $id)
            ->where('user_id', auth()->id())
            ->with([
                'pemilikProsesBisnis',
                'dokumenUsulan.uploader',
                'verifikasi.verifikator',
                'surat.statusKementerian',
                'historiAktivitas.user'
            ])
            ->firstOrFail();

        return view('user.rekomendasi.usulan.show', compact('proposal'));
    }

    /**
     * Show the form for editing the specified proposal.
     */
    public function edit($id)
    {
        $proposal = RekomendasiAplikasiForm::where('id', $id)
            ->where('user_id', auth()->id())
            ->whereIn('status', ['draft', 'perlu_revisi'])
            ->with('verifikasi')
            ->firstOrFail();

        $unitKerjaList = UnitKerja::orderBy('nama')->get();

        return view('user.rekomendasi.usulan.edit', compact('proposal', 'unitKerjaList'));
    }

    /**
     * Update the specified proposal in storage.
     */
    public function update(StoreUsulanV2Request $request, $id)
    {
        try {
            \DB::beginTransaction();

            $proposal = RekomendasiAplikasiForm::where('id', $id)
                ->where('user_id', auth()->id())
                ->whereIn('status', ['draft', 'perlu_revisi'])
                ->firstOrFail();

            // Prepare data from validated request
            $data = $request->validated();

            // Handle file upload for proses_bisnis_file
            if ($request->hasFile('proses_bisnis_file')) {
                // Delete old file if exists
                if ($proposal->proses_bisnis_file && \Storage::disk('public')->exists($proposal->proses_bisnis_file)) {
                    \Storage::disk('public')->delete($proposal->proses_bisnis_file);
                }

                $file = $request->file('proses_bisnis_file');
                $filename = 'proses_bisnis_' . time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('rekomendasi/proses_bisnis', $filename, 'public');
                $data['proses_bisnis_file'] = $path;
            }

            $proposal->update($data);

            // Log activity
            $proposal->logActivity(
                'Usulan Diperbarui',
                'Usulan rekomendasi aplikasi diperbarui'
            );

            \DB::commit();

            return redirect()
                ->route('user.rekomendasi.usulan.show', $proposal->id)
                ->with('success', 'Usulan berhasil diperbarui.');

        } catch (\Exception $e) {
            \DB::rollBack();

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified proposal from storage.
     */
    public function destroy($id)
    {
        try {
            $proposal = RekomendasiAplikasiForm::where('id', $id)
                ->where('user_id', auth()->id())
                ->where('status', 'draft')
                ->firstOrFail();

            $title = $proposal->nama_aplikasi;
            $proposal->delete();

            return redirect()
                ->route('user.rekomendasi.usulan.index')
                ->with('success', "Usulan '{$title}' berhasil dihapus.");

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Upload document for proposal.
     */
    public function uploadDokumen(UploadDokumenRequest $request, $id)
    {
        try {
            $proposal = RekomendasiAplikasiForm::where('id', $id)
                ->where('user_id', auth()->id())
                ->firstOrFail();

            $document = $this->documentService->uploadDocument(
                $request->file('file'),
                $request->jenis_dokumen,
                $id
            );

            // Log activity
            $proposal->logActivity(
                'Dokumen Diupload',
                "Dokumen {$document->jenis_dokumen_display} v{$document->versi} berhasil diupload"
            );

            return response()->json([
                'success' => true,
                'message' => 'Dokumen berhasil diupload',
                'document' => $document,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Download document.
     */
    public function downloadDokumen($id, $dokumenId)
    {
        try {
            // Verify ownership
            $proposal = RekomendasiAplikasiForm::where('id', $id)
                ->where('user_id', auth()->id())
                ->firstOrFail();

            return $this->documentService->downloadDocument($dokumenId);

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Submit proposal for verification.
     */
    public function submit($id)
    {
        try {
            $proposal = RekomendasiAplikasiForm::where('id', $id)
                ->where('user_id', auth()->id())
                ->where('status', 'draft')
                ->firstOrFail();

            // Check if all required documents are uploaded
            if (!$this->documentService->hasAllRequiredDocuments($id)) {
                return redirect()
                    ->back()
                    ->with('error', 'Harap upload semua dokumen wajib sebelum mengajukan usulan.');
            }

            $proposal->update([
                'status' => 'diajukan',
                'fase_saat_ini' => 'verifikasi',
            ]);

            // Create verifikasi record
            $proposal->verifikasi()->create([
                'verifikator_id' => null,
                'status' => 'menunggu',
            ]);

            // Log activity
            $proposal->logActivity(
                'Usulan Diajukan',
                'Usulan diajukan untuk verifikasi Diskominfo'
            );

            // TODO: Send notification to admin

            return redirect()
                ->route('user.rekomendasi.usulan.show', $proposal->id)
                ->with('success', 'Usulan berhasil diajukan. Menunggu verifikasi dari Diskominfo.');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
