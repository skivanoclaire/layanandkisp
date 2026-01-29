<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PseUpdateRequest;
use App\Models\PseUpdateRequestLog;
use App\Models\WebMonitor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PseUpdateAdminController extends Controller
{
    /**
     * Display listing of PSE update requests with filters
     */
    public function index(Request $request)
    {
        $status = $request->query('status');
        $search = $request->query('search');

        $query = PseUpdateRequest::with([
            'user.unitKerja',
            'webMonitor',
            'processedBy',
            'approvedBy',
            'rejectedBy',
            'revisionRequestedBy'
        ])->orderByDesc('created_at');

        // Filter by status
        if ($status) {
            $query->where('status', $status);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Search by ticket, subdomain, or user name
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('ticket_no', 'like', "%{$search}%")
                    ->orWhereHas('webMonitor', function ($wm) use ($search) {
                        $wm->where('subdomain', 'like', "%{$search}%")
                            ->orWhere('nama_aplikasi', 'like', "%{$search}%");
                    })
                    ->orWhereHas('user', function ($u) use ($search) {
                        $u->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $requests = $query->paginate(25)->withQueryString();

        // Get statistics (all requests, not just current page)
        $stats = [
            'diajukan' => PseUpdateRequest::where('status', 'diajukan')->count(),
            'diproses' => PseUpdateRequest::where('status', 'diproses')->count(),
            'perlu_revisi' => PseUpdateRequest::where('status', 'perlu_revisi')->count(),
            'disetujui' => PseUpdateRequest::where('status', 'disetujui')->count(),
            'ditolak' => PseUpdateRequest::where('status', 'ditolak')->count(),
        ];

        return view('admin.pse-update.index', compact('requests', 'stats'));
    }

    /**
     * Display specific PSE update request with all details
     */
    public function show($id)
    {
        $pseUpdate = PseUpdateRequest::with([
            'user.unitKerja',
            'webMonitor',
            'processedBy',
            'approvedBy',
            'rejectedBy',
            'revisionRequestedBy',
            'logs.actor'
        ])->findOrFail($id);

        $webMonitor = $pseUpdate->webMonitor;

        return view('admin.pse-update.show', compact('pseUpdate', 'webMonitor'));
    }

    /**
     * Update status of PSE update request (approve, revision, reject)
     */
    public function updateStatus(Request $request, $id)
    {
        $data = $request->validate([
            'action' => ['required', Rule::in(['approve', 'revision', 'reject'])],
            'admin_notes' => ['nullable', 'string', 'max:1000'],
            'revision_notes' => ['required_if:action,revision', 'nullable', 'string', 'max:1000'],
            'rejection_reason' => ['required_if:action,reject', 'nullable', 'string', 'max:1000'],
        ], [
            'action.required' => 'Aksi wajib dipilih.',
            'action.in' => 'Aksi tidak valid.',
            'revision_notes.required_if' => 'Catatan revisi wajib diisi jika meminta revisi.',
            'rejection_reason.required_if' => 'Alasan penolakan wajib diisi jika menolak permohonan.',
        ]);

        $pseUpdate = PseUpdateRequest::with('webMonitor')->findOrFail($id);

        $action = $data['action'];

        DB::beginTransaction();
        try {
            switch ($action) {
                case 'approve':
                    $this->handleApprove($pseUpdate, $data['admin_notes'] ?? null);
                    $message = "Permohonan {$pseUpdate->ticket_no} berhasil disetujui dan data WebMonitor telah diperbarui.";
                    break;

                case 'revision':
                    $this->handleRevision($pseUpdate, $data['revision_notes'], $data['admin_notes'] ?? null);
                    $message = "Permohonan {$pseUpdate->ticket_no} diminta untuk direvisi.";
                    break;

                case 'reject':
                    $this->handleReject($pseUpdate, $data['rejection_reason'], $data['admin_notes'] ?? null);
                    $message = "Permohonan {$pseUpdate->ticket_no} ditolak.";
                    break;

                default:
                    throw new \Exception('Invalid action');
            }

            DB::commit();

            return redirect()->route('admin.pse-update.show', $pseUpdate->id)
                ->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();

            // Log exception
            PseUpdateRequestLog::create([
                'pse_update_request_id' => $pseUpdate->id,
                'actor_id' => auth()->id(),
                'action' => 'processing_error',
                'note' => 'Error saat memproses permohonan: ' . $e->getMessage(),
            ]);

            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Handle approve action
     */
    private function handleApprove(PseUpdateRequest $item, ?string $adminNotes)
    {
        // Update request status
        $item->status = 'disetujui';
        $item->approved_at = now();
        $item->approved_by = auth()->id();
        $item->processed_by = auth()->id();
        $item->processing_at = now();
        if ($adminNotes) {
            $item->admin_notes = $adminNotes;
        }
        $item->save();

        // Log approval
        PseUpdateRequestLog::create([
            'pse_update_request_id' => $item->id,
            'actor_id' => auth()->id(),
            'action' => 'approved',
            'note' => 'Permohonan disetujui' . ($adminNotes ? ': ' . $adminNotes : ''),
        ]);

        // Update WebMonitor data
        $webMonitor = $item->webMonitor;

        if ($item->update_esc) {
            $webMonitor->esc_answers = $item->esc_answers;
            $webMonitor->esc_total_score = $item->esc_total_score;
            $webMonitor->esc_category = $item->esc_category;
            if ($item->esc_document_path) {
                $webMonitor->esc_document_path = $item->esc_document_path;
            }
            $webMonitor->esc_filled_at = now();
            $webMonitor->esc_updated_by = auth()->id();
        }

        if ($item->update_dc) {
            $webMonitor->dc_data_name = $item->dc_data_name;
            $webMonitor->dc_data_attributes = $item->dc_data_attributes;
            $webMonitor->dc_confidentiality = $item->dc_confidentiality;
            $webMonitor->dc_integrity = $item->dc_integrity;
            $webMonitor->dc_availability = $item->dc_availability;
            $webMonitor->dc_total_score = $item->dc_total_score;
            $webMonitor->dc_filled_at = now();
            $webMonitor->dc_updated_by = auth()->id();
        }

        $webMonitor->save();

        // Log WebMonitor update
        PseUpdateRequestLog::create([
            'pse_update_request_id' => $item->id,
            'actor_id' => auth()->id(),
            'action' => 'web_monitor_updated',
            'note' => 'Data WebMonitor (ID: ' . $webMonitor->id . ') berhasil diperbarui',
        ]);
    }

    /**
     * Handle revision request
     */
    private function handleRevision(PseUpdateRequest $item, string $revisionNotes, ?string $adminNotes)
    {
        $item->status = 'perlu_revisi';
        $item->revision_notes = $revisionNotes;
        $item->revision_requested_by = auth()->id();
        $item->revision_requested_at = now();
        $item->processed_by = auth()->id();
        $item->processing_at = now();
        if ($adminNotes) {
            $item->admin_notes = $adminNotes;
        }
        $item->save();

        // Log revision request
        PseUpdateRequestLog::create([
            'pse_update_request_id' => $item->id,
            'actor_id' => auth()->id(),
            'action' => 'revision_requested',
            'note' => 'Permohonan diminta revisi: ' . $revisionNotes,
        ]);
    }

    /**
     * Handle reject action
     */
    private function handleReject(PseUpdateRequest $item, string $rejectionReason, ?string $adminNotes)
    {
        $item->status = 'ditolak';
        $item->rejection_reason = $rejectionReason;
        $item->rejected_at = now();
        $item->rejected_by = auth()->id();
        $item->processed_by = auth()->id();
        $item->processing_at = now();
        if ($adminNotes) {
            $item->admin_notes = $adminNotes;
        }
        $item->save();

        // Log rejection
        PseUpdateRequestLog::create([
            'pse_update_request_id' => $item->id,
            'actor_id' => auth()->id(),
            'action' => 'rejected',
            'note' => 'Permohonan ditolak: ' . $rejectionReason,
        ]);
    }
}
