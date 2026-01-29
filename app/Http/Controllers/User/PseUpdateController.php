<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\PseUpdateRequest;
use App\Models\PseUpdateRequestLog;
use App\Models\WebMonitor;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PseUpdateController extends Controller
{
    /**
     * Display listing of user's PSE update requests
     */
    public function index()
    {
        $requests = PseUpdateRequest::where('user_id', auth()->id())
            ->with([
                'webMonitor',
                'processedBy',
                'approvedBy',
                'rejectedBy',
                'revisionRequestedBy'
            ])
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('user.pse-update.index', compact('requests'));
    }

    /**
     * Show list of subdomains to choose for update
     */
    public function create()
    {
        // Get all subdomains (not just user's own requests)
        // This allows users to update PSE data for any existing subdomain
        $webMonitors = WebMonitor::whereNotNull('subdomain')
            ->orderBy('subdomain')
            ->get();

        return view('user.pse-update.create', compact('webMonitors'));
    }

    /**
     * Show form to create update request for specific subdomain
     */
    public function createForm($webMonitorId)
    {
        // Allow any user to update any subdomain's PSE data
        $webMonitor = WebMonitor::findOrFail($webMonitorId);

        return view('user.pse-update.form', compact('webMonitor'));
    }

    /**
     * Store new PSE update request
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'web_monitor_id' => ['required', 'exists:web_monitors,id'],

            // ESC fields (all required) - using format 1_1 to 1_10
            'esc_answers' => ['required', 'array'],
            'esc_answers.1_1' => ['required', Rule::in(['A', 'B', 'C'])],
            'esc_answers.1_2' => ['required', Rule::in(['A', 'B', 'C'])],
            'esc_answers.1_3' => ['required', Rule::in(['A', 'B', 'C'])],
            'esc_answers.1_4' => ['required', Rule::in(['A', 'B', 'C'])],
            'esc_answers.1_5' => ['required', Rule::in(['A', 'B', 'C'])],
            'esc_answers.1_6' => ['required', Rule::in(['A', 'B', 'C'])],
            'esc_answers.1_7' => ['required', Rule::in(['A', 'B', 'C'])],
            'esc_answers.1_8' => ['required', Rule::in(['A', 'B', 'C'])],
            'esc_answers.1_9' => ['required', Rule::in(['A', 'B', 'C'])],
            'esc_answers.1_10' => ['required', Rule::in(['A', 'B', 'C'])],
            'esc_document' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:10240'],

            // DC fields (all required)
            'dc_data_name' => ['required', 'string', 'max:255'],
            'dc_data_attributes' => ['required', 'string'],
            'dc_confidentiality' => ['required', Rule::in(['Rendah', 'Sedang', 'Tinggi'])],
            'dc_integrity' => ['required', Rule::in(['Rendah', 'Sedang', 'Tinggi'])],
            'dc_availability' => ['required', Rule::in(['Rendah', 'Sedang', 'Tinggi'])],
        ], [
            'web_monitor_id.required' => 'Web Monitor wajib dipilih.',
            'web_monitor_id.exists' => 'Web Monitor tidak valid.',
            'esc_answers.required' => 'Jawaban kuesioner Kategori Sistem Elektronik wajib diisi.',
            'esc_answers.1_*.required' => 'Semua pertanyaan kuesioner wajib dijawab.',
            'esc_answers.1_*.in' => 'Jawaban harus berupa A, B, atau C.',
            'esc_document.mimes' => 'Dokumen Kategori Sistem Elektronik harus berformat PDF, DOC, atau DOCX.',
            'esc_document.max' => 'Ukuran dokumen Kategori Sistem Elektronik maksimal 10MB.',
            'dc_data_name.required' => 'Nama Data wajib diisi.',
            'dc_data_attributes.required' => 'Atribut Data wajib diisi.',
            'dc_confidentiality.required' => 'Tingkat Kerahasiaan wajib dipilih.',
            'dc_integrity.required' => 'Tingkat Integritas wajib dipilih.',
            'dc_availability.required' => 'Tingkat Ketersediaan wajib dipilih.',
        ]);

        // Both ESC and DC are always required
        $updateEsc = true;
        $updateDc = true;

        // Get web monitor (any user can update any subdomain)
        $webMonitor = WebMonitor::findOrFail($data['web_monitor_id']);

        // Create request
        $pseRequest = new PseUpdateRequest();
        $pseRequest->user_id = auth()->id();
        $pseRequest->web_monitor_id = $webMonitor->id;
        $pseRequest->update_esc = $updateEsc;
        $pseRequest->update_dc = $updateDc;
        $pseRequest->status = 'draft';

        // Handle ESC data
        if ($updateEsc && isset($data['esc_answers'])) {
            $pseRequest->esc_answers = $data['esc_answers'];
            $pseRequest->updateEscScoreAndCategory();

            // Handle document upload
            if ($request->hasFile('esc_document')) {
                $file = $request->file('esc_document');
                $filename = 'ESC_PSE_' . now()->format('YmdHis') . '_' . auth()->id() . '.' . $file->extension();
                $path = $file->storeAs('pse-update/esc', $filename, 'public');
                $pseRequest->esc_document_path = $path;
            }
        }

        // Handle DC data
        if ($updateDc) {
            $pseRequest->dc_data_name = $data['dc_data_name'] ?? null;
            $pseRequest->dc_data_attributes = $data['dc_data_attributes'] ?? null;
            $pseRequest->dc_confidentiality = $data['dc_confidentiality'] ?? null;
            $pseRequest->dc_integrity = $data['dc_integrity'] ?? null;
            $pseRequest->dc_availability = $data['dc_availability'] ?? null;
            $pseRequest->updateDcScore();
        }

        $pseRequest->save();

        // Create log
        PseUpdateRequestLog::create([
            'pse_update_request_id' => $pseRequest->id,
            'actor_id' => auth()->id(),
            'action' => 'created',
            'note' => 'Permohonan update data PSE dibuat',
        ]);

        return redirect()->route('user.pse-update.show', $pseRequest->id)
            ->with('status', 'Permohonan berhasil disimpan sebagai draft.');
    }

    /**
     * Display specific PSE update request
     */
    public function show($id)
    {
        $pseUpdate = PseUpdateRequest::where('user_id', auth()->id())
            ->with([
                'webMonitor',
                'processedBy',
                'approvedBy',
                'rejectedBy',
                'revisionRequestedBy',
                'logs.actor'
            ])
            ->findOrFail($id);

        $webMonitor = $pseUpdate->webMonitor;

        return view('user.pse-update.show', compact('pseUpdate', 'webMonitor'));
    }

    /**
     * Show edit form for PSE update request
     */
    public function edit($id)
    {
        $pseUpdate = PseUpdateRequest::where('user_id', auth()->id())
            ->with('webMonitor')
            ->findOrFail($id);

        // Only allow edit if status is draft or perlu_revisi
        if (!$pseUpdate->canBeEdited()) {
            abort(403, 'Permohonan tidak dapat diedit karena sudah diproses atau ditolak.');
        }

        $webMonitor = $pseUpdate->webMonitor;

        return view('user.pse-update.form', compact('pseUpdate', 'webMonitor'));
    }

    /**
     * Update PSE update request
     */
    public function update(Request $request, $id)
    {
        $item = PseUpdateRequest::where('user_id', auth()->id())
            ->findOrFail($id);

        // Only allow update if status is draft or perlu_revisi
        if (!$item->canBeEdited()) {
            throw ValidationException::withMessages([
                'status' => 'Permohonan tidak dapat diubah karena sudah diproses atau ditolak.'
            ]);
        }

        $data = $request->validate([
            // ESC fields (all required) - using format 1_1 to 1_10
            'esc_answers' => ['required', 'array'],
            'esc_answers.1_1' => ['required', Rule::in(['A', 'B', 'C'])],
            'esc_answers.1_2' => ['required', Rule::in(['A', 'B', 'C'])],
            'esc_answers.1_3' => ['required', Rule::in(['A', 'B', 'C'])],
            'esc_answers.1_4' => ['required', Rule::in(['A', 'B', 'C'])],
            'esc_answers.1_5' => ['required', Rule::in(['A', 'B', 'C'])],
            'esc_answers.1_6' => ['required', Rule::in(['A', 'B', 'C'])],
            'esc_answers.1_7' => ['required', Rule::in(['A', 'B', 'C'])],
            'esc_answers.1_8' => ['required', Rule::in(['A', 'B', 'C'])],
            'esc_answers.1_9' => ['required', Rule::in(['A', 'B', 'C'])],
            'esc_answers.1_10' => ['required', Rule::in(['A', 'B', 'C'])],
            'esc_document' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:10240'],

            // DC fields (all required)
            'dc_data_name' => ['required', 'string', 'max:255'],
            'dc_data_attributes' => ['required', 'string'],
            'dc_confidentiality' => ['required', Rule::in(['Rendah', 'Sedang', 'Tinggi'])],
            'dc_integrity' => ['required', Rule::in(['Rendah', 'Sedang', 'Tinggi'])],
            'dc_availability' => ['required', Rule::in(['Rendah', 'Sedang', 'Tinggi'])],
        ], [
            'esc_answers.required' => 'Jawaban kuesioner Kategori Sistem Elektronik wajib diisi.',
            'esc_answers.1_*.required' => 'Semua pertanyaan kuesioner wajib dijawab.',
            'esc_answers.1_*.in' => 'Jawaban harus berupa A, B, atau C.',
            'esc_document.mimes' => 'Dokumen Kategori Sistem Elektronik harus berformat PDF, DOC, atau DOCX.',
            'esc_document.max' => 'Ukuran dokumen Kategori Sistem Elektronik maksimal 10MB.',
            'dc_data_name.required' => 'Nama Data wajib diisi.',
            'dc_data_attributes.required' => 'Atribut Data wajib diisi.',
            'dc_confidentiality.required' => 'Tingkat Kerahasiaan wajib dipilih.',
            'dc_integrity.required' => 'Tingkat Integritas wajib dipilih.',
            'dc_availability.required' => 'Tingkat Ketersediaan wajib dipilih.',
        ]);

        $wasRevision = $item->status === 'perlu_revisi';

        // Both ESC and DC are always required
        $updateEsc = true;
        $updateDc = true;

        $item->update_esc = $updateEsc;
        $item->update_dc = $updateDc;

        // Update ESC data
        if ($updateEsc && isset($data['esc_answers'])) {
            $item->esc_answers = $data['esc_answers'];
            $item->updateEscScoreAndCategory();

            // Handle document upload
            if ($request->hasFile('esc_document')) {
                // Delete old document if exists
                if ($item->esc_document_path && \Storage::disk('public')->exists($item->esc_document_path)) {
                    \Storage::disk('public')->delete($item->esc_document_path);
                }

                $file = $request->file('esc_document');
                $filename = 'ESC_PSE_' . now()->format('YmdHis') . '_' . auth()->id() . '.' . $file->extension();
                $path = $file->storeAs('pse-update/esc', $filename, 'public');
                $item->esc_document_path = $path;
            }
        } else {
            // Clear ESC data if not updating
            $item->esc_answers = null;
            $item->esc_total_score = null;
            $item->esc_category = null;
        }

        // Update DC data
        if ($updateDc) {
            $item->dc_data_name = $data['dc_data_name'] ?? null;
            $item->dc_data_attributes = $data['dc_data_attributes'] ?? null;
            $item->dc_confidentiality = $data['dc_confidentiality'] ?? null;
            $item->dc_integrity = $data['dc_integrity'] ?? null;
            $item->dc_availability = $data['dc_availability'] ?? null;
            $item->updateDcScore();
        } else {
            // Clear DC data if not updating
            $item->dc_data_name = null;
            $item->dc_data_attributes = null;
            $item->dc_confidentiality = null;
            $item->dc_integrity = null;
            $item->dc_availability = null;
            $item->dc_total_score = null;
        }

        // Check if user wants to submit directly
        $action = $request->input('action', 'draft');

        if ($action === 'submit') {
            // Submit directly
            $item->status = 'diajukan';
            $item->submitted_at = now();

            // Clear processing fields if resubmitting after revision
            if ($wasRevision) {
                $item->processed_by = null;
                $item->processing_at = null;
            }

            $item->save();

            // Create submit log
            PseUpdateRequestLog::create([
                'pse_update_request_id' => $item->id,
                'actor_id' => auth()->id(),
                'action' => $wasRevision ? 'revision_submitted' : 'submitted',
                'note' => $wasRevision ? 'Permohonan diajukan kembali setelah revisi' : 'Permohonan diajukan untuk diproses',
            ]);

            return redirect()->route('user.pse-update.show', $item->id)
                ->with('status', $wasRevision ? 'Permohonan berhasil diajukan kembali setelah revisi.' : 'Permohonan berhasil diajukan.');
        }

        // Save as draft
        $item->save();

        // Create update log
        PseUpdateRequestLog::create([
            'pse_update_request_id' => $item->id,
            'actor_id' => auth()->id(),
            'action' => 'updated',
            'note' => 'Permohonan update data PSE diperbarui',
        ]);

        return redirect()->route('user.pse-update.show', $item->id)
            ->with('status', 'Permohonan berhasil diperbarui.');
    }

    /**
     * Submit PSE update request (draft -> diajukan or perlu_revisi -> diajukan)
     */
    public function submit($id)
    {
        $item = PseUpdateRequest::where('user_id', auth()->id())
            ->findOrFail($id);

        // Only allow submit if status is draft or perlu_revisi
        if (!in_array($item->status, ['draft', 'perlu_revisi'])) {
            return back()->with('error', 'Permohonan tidak dapat disubmit karena sudah diproses.');
        }

        $wasRevision = $item->status === 'perlu_revisi';

        $item->status = 'diajukan';
        $item->submitted_at = now();

        // Clear processing fields when resubmitting after revision
        // This allows admin to process the request again
        if ($wasRevision) {
            $item->processed_by = null;
            $item->processing_at = null;
            // Keep revision history for audit trail, but admin will see it needs re-processing
        }

        $item->save();

        // Create log
        PseUpdateRequestLog::create([
            'pse_update_request_id' => $item->id,
            'actor_id' => auth()->id(),
            'action' => $wasRevision ? 'revision_submitted' : 'submitted',
            'note' => $wasRevision ? 'Permohonan diajukan kembali setelah revisi' : 'Permohonan diajukan untuk diproses',
        ]);

        return redirect()->route('user.pse-update.show', $item->id)
            ->with('status', 'Permohonan berhasil diajukan.');
    }

    /**
     * Delete PSE update request (only if draft)
     */
    public function destroy($id)
    {
        $item = PseUpdateRequest::where('user_id', auth()->id())
            ->findOrFail($id);

        // Only allow delete if status is draft
        if ($item->status !== 'draft') {
            abort(403, 'Permohonan tidak dapat dihapus karena sudah diproses.');
        }

        // Delete document if exists
        if ($item->esc_document_path && \Storage::disk('public')->exists($item->esc_document_path)) {
            \Storage::disk('public')->delete($item->esc_document_path);
        }

        $item->delete();

        return redirect()->route('user.pse-update.index')
            ->with('status', 'Permohonan berhasil dihapus.');
    }
}
