<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Traits\ExportsDigitalData;
use App\Models\VidconRequest;
use App\Models\VidconRequestActivity;
use App\Models\User;
use App\Exports\VidconRequestExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VidconRequestAdminController extends Controller
{
    use ExportsDigitalData;
    // GET /admin/digital/vidcon
    public function index(Request $r)
    {
        $status = $r->query('status'); // ?status=menunggu|proses|ditolak|selesai
        $q = VidconRequest::with(['user', 'unitKerja'])
            ->orderByDesc('submitted_at');

        if ($status) {
            $q->where('status', $status);
        }

        // Filter by date range
        if ($r->filled('dari_tanggal')) {
            $q->whereDate('submitted_at', '>=', $r->dari_tanggal);
        }
        if ($r->filled('sampai_tanggal')) {
            $q->whereDate('submitted_at', '<=', $r->sampai_tanggal);
        }

        $items = $q->paginate(25);

        return view('admin.vidcon.index', compact('items', 'status'));
    }

    // GET /admin/digital/vidcon/{id}
    public function show($id)
    {
        $item = VidconRequest::with(['user', 'unitKerja', 'processedBy', 'operatorAssigned', 'operators'])
            ->findOrFail($id);

        // Get list of operators for assignment (Operator-Vidcon role) with workload data
        $operators = User::whereHas('roles', function ($query) {
            $query->where('name', 'Operator-Vidcon');
        })
        ->with(['vidconRequests' => function ($query) {
            $query->whereIn('status', ['proses', 'selesai']);
        }])
        ->orderBy('name')
        ->get();

        // Load workload attributes for each operator
        $operators->each(function ($operator) {
            $operator->active_vidcon_workload;
            $operator->vidcon_workload;
        });

        // Get AI recommendation
        $recommendedOperatorIds = VidconRequest::recommendOperators($operators, 2);

        return view('admin.vidcon.show', compact('item', 'operators', 'recommendedOperatorIds'));
    }

    // POST /admin/digital/vidcon/{id}/approve
    public function approve(Request $r, $id)
    {
        $r->validate([
            'link_meeting'         => 'required|string|max:500',
            'meeting_id'           => 'required|string|max:200',
            'meeting_password'     => 'required|string|max:200',
            'informasi_tambahan'   => 'nullable|string|max:1000',
            'operators'            => 'nullable|array',
            'operators.*'          => 'exists:users,id',
            'admin_notes'          => 'nullable|string|max:1000',
        ], [
            'link_meeting.required' => 'Link Meeting wajib diisi.',
            'meeting_id.required' => 'Meeting ID wajib diisi.',
            'meeting_password.required' => 'Password Meeting wajib diisi.',
        ]);

        $item = VidconRequest::with(['unitKerja'])->findOrFail($id);

        // Update request status to completed
        $item->status           = 'selesai';
        $item->completed_at     = now();
        $item->processed_by     = auth()->id();
        $item->link_meeting     = $r->link_meeting;
        $item->meeting_id       = $r->meeting_id;
        $item->meeting_password = $r->meeting_password;
        $item->informasi_tambahan = $r->informasi_tambahan;
        $item->admin_notes      = $r->admin_notes;
        $item->save();

        // Sync operators (attach multiple operators)
        if ($r->has('operators') && is_array($r->operators)) {
            $item->operators()->sync($r->operators);
        } else {
            $item->operators()->detach(); // Clear if none selected
        }

        // Reload operators relationship after sync
        $item->load('operators');

        // Automatically create VidconData entry with complete information
        $vidconData = \App\Models\VidconData::create([
            // Reference
            'vidcon_request_id' => $item->id,

            // Pemohon Info
            'nama_instansi' => $item->unitKerja->nama ?? '-',
            'nomor_surat' => $item->ticket_no,
            'nama_pemohon' => $item->nama,
            'nip_pemohon' => $item->nip,
            'email_pemohon' => $item->email_pemohon,
            'no_hp' => $item->no_hp,
            'unit_kerja_id' => $item->unit_kerja_id,

            // Kegiatan Info
            'judul_kegiatan' => $item->judul_kegiatan,
            'deskripsi_kegiatan' => $item->deskripsi_kegiatan,
            'lokasi' => 'Online',
            'tanggal_mulai' => $item->tanggal_mulai,
            'tanggal_selesai' => $item->tanggal_selesai,
            'jam_mulai' => $item->jam_mulai,
            'jam_selesai' => $item->jam_selesai,
            'platform' => $item->platform_display,
            'jumlah_peserta' => $item->jumlah_peserta,
            'keperluan_khusus' => $item->keperluan_khusus,

            // Meeting Info
            'link_meeting' => $item->link_meeting,
            'meeting_id' => $item->meeting_id,
            'meeting_password' => $item->meeting_password,
            'informasi_tambahan' => $item->informasi_tambahan,

            // Operator (legacy field)
            'operator' => $item->operators->pluck('name')->join(', '),

            // Backward compatibility fields
            'dokumentasi' => $item->link_meeting ?? '-',
            'akun_zoom' => $item->meeting_id ?? '-',
            'informasi_pimpinan' => $item->nama . ' - ' . $item->nip,
            'keterangan' => 'Auto-generated dari permohonan ' . $item->ticket_no,

            // Tracking
            'processed_by' => auth()->id(),
            'completed_at' => now(),
        ]);

        // Sync operators to VidconData
        if ($item->operators->count() > 0) {
            $vidconData->operators()->sync($item->operators->pluck('id')->toArray());
        }

        // Log activity
        VidconRequestActivity::create([
            'vidcon_request_id' => $item->id,
            'user_id' => auth()->id(),
            'action' => 'approved',
            'old_values' => ['status' => 'proses'],
            'new_values' => ['status' => 'selesai'],
            'notes' => 'Request approved and VidconData created (ID: ' . $vidconData->id . ')',
        ]);

        Log::info('Video conference request approved and auto-converted to VidconData', [
            'ticket_no' => $item->ticket_no,
            'approved_by' => auth()->id(),
            'user_id' => $item->user_id,
            'operators_count' => count($r->operators ?? []),
            'vidcon_data_id' => $vidconData->id,
        ]);

        return redirect()->route('admin.vidcon.show', $item->id)
            ->with('success', "Permohonan {$item->ticket_no} telah disetujui dan otomatis ditambahkan ke Data Fasilitasi Vidcon.");
    }

    // POST /admin/digital/vidcon/{id}/reject
    public function reject(Request $r, $id)
    {
        $r->validate([
            'admin_notes' => 'required|string|max:1000',
        ], [
            'admin_notes.required' => 'Alasan penolakan wajib diisi.',
        ]);

        $item = VidconRequest::findOrFail($id);

        // Update request status to rejected
        $item->status       = 'ditolak';
        $item->rejected_at  = now();
        $item->processed_by = auth()->id();
        $item->admin_notes  = $r->admin_notes;
        $item->save();

        Log::info('Video conference request rejected', [
            'ticket_no' => $item->ticket_no,
            'rejected_by' => auth()->id(),
            'user_id' => $item->user_id,
            'reason' => $r->admin_notes,
        ]);

        return redirect()->route('admin.vidcon.show', $item->id)
            ->with('success', "Permohonan {$item->ticket_no} telah ditolak.");
    }

    // POST /admin/digital/vidcon/{id}/process
    public function setProcess(Request $r, $id)
    {
        $r->validate([
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $item = VidconRequest::findOrFail($id);

        if ($item->status !== 'menunggu') {
            return back()->with('error', 'Request sudah diproses.');
        }

        // Update request status to in process
        $item->status        = 'proses';
        $item->processing_at = now();
        $item->processed_by  = auth()->id();

        if ($r->admin_notes) {
            $item->admin_notes = $r->admin_notes;
        }

        $item->save();

        // Log activity
        VidconRequestActivity::create([
            'vidcon_request_id' => $item->id,
            'user_id' => auth()->id(),
            'action' => 'status_changed',
            'old_values' => ['status' => 'menunggu'],
            'new_values' => ['status' => 'proses'],
            'notes' => 'Request taken for processing',
        ]);

        Log::info('Video conference request set to process', [
            'ticket_no' => $item->ticket_no,
            'processed_by' => auth()->id(),
            'user_id' => $item->user_id,
        ]);

        return redirect()->route('admin.vidcon.show', $item->id)
            ->with('success', "Permohonan {$item->ticket_no} sedang diproses.");
    }

    // POST /admin/digital/vidcon/{id}/update-info
    public function updateInfo(Request $r, $id)
    {
        $r->validate([
            'link_meeting'         => 'nullable|string|max:500',
            'meeting_id'           => 'nullable|string|max:200',
            'meeting_password'     => 'nullable|string|max:200',
            'informasi_tambahan'   => 'nullable|string|max:1000',
            'operators'            => 'nullable|array',
            'operators.*'          => 'exists:users,id',
            'admin_notes'          => 'nullable|string|max:1000',
        ]);

        $item = VidconRequest::findOrFail($id);

        // Prevent updates after approval or rejection
        if (in_array($item->status, ['selesai', 'ditolak'])) {
            return back()->with('error', 'Tidak dapat mengupdate informasi setelah request disetujui atau ditolak.');
        }

        // Only allow updating if status is proses
        if ($item->status !== 'proses') {
            return back()->with('error', 'Informasi hanya dapat diperbarui jika status sedang diproses.');
        }

        // Require at least one meeting field to be filled
        if (empty($r->link_meeting) && empty($r->meeting_id) && empty($r->meeting_password)) {
            return back()->with('error', 'Minimal satu field meeting harus diisi.');
        }

        // Store old values for activity log
        $oldValues = [
            'link_meeting' => $item->link_meeting,
            'meeting_id' => $item->meeting_id,
            'meeting_password' => $item->meeting_password,
            'informasi_tambahan' => $item->informasi_tambahan,
            'admin_notes' => $item->admin_notes,
        ];

        // Update fields
        $item->link_meeting       = $r->link_meeting ?? $item->link_meeting;
        $item->meeting_id         = $r->meeting_id ?? $item->meeting_id;
        $item->meeting_password   = $r->meeting_password ?? $item->meeting_password;
        $item->informasi_tambahan = $r->informasi_tambahan ?? $item->informasi_tambahan;
        $item->admin_notes        = $r->admin_notes ?? $item->admin_notes;

        // Update tracking fields
        $item->last_info_updated_at = now();
        $item->info_update_count = $item->info_update_count + 1;
        $item->last_updated_by = auth()->id();

        $item->save();

        // Store new values for activity log
        $newValues = [
            'link_meeting' => $item->link_meeting,
            'meeting_id' => $item->meeting_id,
            'meeting_password' => $item->meeting_password,
            'informasi_tambahan' => $item->informasi_tambahan,
            'admin_notes' => $item->admin_notes,
        ];

        // Log activity
        VidconRequestActivity::create([
            'vidcon_request_id' => $item->id,
            'user_id' => auth()->id(),
            'action' => 'info_updated',
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'notes' => 'Meeting information updated',
        ]);

        // Sync operators if provided
        if ($r->has('operators')) {
            if (is_array($r->operators) && count($r->operators) > 0) {
                $item->operators()->sync($r->operators);
            } else {
                $item->operators()->detach();
            }
        }

        return redirect()->route('admin.vidcon.show', $item->id)
            ->with('success', 'Informasi permohonan berhasil diperbarui.');
    }

    // GET /admin/digital/vidcon/export-excel
    public function exportExcel(Request $r)
    {
        return $this->exportToExcel(
            $r,
            VidconRequestExport::class,
            'permohonan-vidcon'
        );
    }

    // GET /admin/digital/vidcon/export-pdf
    public function exportPdf(Request $r)
    {
        return $this->exportToPdf(
            $r,
            VidconRequest::class,
            'admin.vidcon.pdf',
            'permohonan-vidcon'
        );
    }
}
