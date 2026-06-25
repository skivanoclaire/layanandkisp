<?php

namespace App\Http\Controllers\Admin\Splp;

use App\Http\Controllers\Controller;
use App\Models\SplpDeactivationRequest;
use App\Models\SplpRequestLog;
use App\Models\SplpServiceLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * V5 — Penonaktifan / Pencabutan Endpoint — sisi admin.
 */
class SplpDeactivationRequestAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = SplpDeactivationRequest::with(['user', 'unitKerja', 'service', 'consumer'])
            ->where('status', '!=', SplpDeactivationRequest::STATUS_DRAFT)
            ->orderByDesc('submitted_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('dari_tanggal')) {
            $query->whereDate('submitted_at', '>=', $request->dari_tanggal);
        }
        if ($request->filled('sampai_tanggal')) {
            $query->whereDate('submitted_at', '<=', $request->sampai_tanggal);
        }

        $items = $query->paginate(25)->withQueryString();
        $status = $request->status;

        return view('admin.splp.deactivation.index', compact('items', 'status'));
    }

    public function show($id)
    {
        $item = SplpDeactivationRequest::with(['user', 'unitKerja', 'service', 'consumer.service', 'logs.actor'])->findOrFail($id);

        return view('admin.splp.deactivation.show', compact('item'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => ['required', Rule::in(SplpDeactivationRequest::adminStatuses())],
            'note' => ['nullable', 'string', 'max:1000'],
            'catatan_verifikasi' => ['nullable', 'string', 'max:2000'],
            'rejection_reason' => ['required_if:status,ditolak', 'nullable', 'string', 'max:1000'],
            'check_administrasi' => ['nullable', 'boolean'],
            'check_dampak' => ['nullable', 'boolean'],
        ]);

        $item = SplpDeactivationRequest::with(['service', 'consumer'])->findOrFail($id);
        $old = $item->status;

        DB::transaction(function () use ($request, $item, $old) {
            $item->check_administrasi = $request->boolean('check_administrasi');
            $item->check_dampak = $request->boolean('check_dampak');
            if ($request->filled('catatan_verifikasi')) {
                $item->catatan_verifikasi = $request->catatan_verifikasi;
            }

            $item->status = $request->status;

            match ($request->status) {
                SplpDeactivationRequest::STATUS_VERIF_ADMIN => $item->verif_admin_at = now(),
                SplpDeactivationRequest::STATUS_VERIF_TEKNIS => $item->verif_teknis_at = now(),
                SplpDeactivationRequest::STATUS_DISETUJUI => $item->decided_at = now(),
                SplpDeactivationRequest::STATUS_SELESAI => $item->completed_at = now(),
                SplpDeactivationRequest::STATUS_DITOLAK => $item->rejected_at = now(),
                default => null,
            };

            if ($request->status === SplpDeactivationRequest::STATUS_DITOLAK) {
                $item->rejection_reason = $request->rejection_reason;
            }

            // Saat selesai: nonaktifkan/cabut objek target + arsipkan ke audit trail
            if ($request->status === SplpDeactivationRequest::STATUS_SELESAI) {
                $newStatus = $item->jenis_tindakan === 'cabut' ? 'dicabut' : 'nonaktif';

                if ($item->target_type === 'service' && $item->service) {
                    $item->service->update(['status' => $newStatus]);
                    SplpServiceLog::record([
                        'splp_service_id' => $item->splp_service_id,
                        'action' => $item->jenis_tindakan === 'cabut' ? 'service_revoked' : 'service_deactivated',
                        'config_baru' => ['status' => $newStatus],
                        'keterangan' => "Layanan di-{$item->jenis_tindakan} dari permohonan {$item->ticket_no}. Alasan: {$item->alasan}" . ($item->is_darurat ? ' [DARURAT]' : ''),
                    ]);
                } elseif ($item->target_type === 'consumer' && $item->consumer) {
                    $item->consumer->update(['status' => $newStatus]);
                    SplpServiceLog::record([
                        'splp_service_id' => $item->consumer->splp_service_id,
                        'splp_consumer_id' => $item->splp_consumer_id,
                        'action' => $item->jenis_tindakan === 'cabut' ? 'consumer_revoked' : 'consumer_deactivated',
                        'config_baru' => ['status' => $newStatus],
                        'keterangan' => "Konsumen di-{$item->jenis_tindakan} dari permohonan {$item->ticket_no}. Alasan: {$item->alasan}" . ($item->is_darurat ? ' [DARURAT]' : ''),
                    ]);
                }
            }

            $item->save();

            SplpRequestLog::record(
                SplpDeactivationRequest::REQUEST_TYPE,
                $item->id,
                "status:{$old}->{$item->status}",
                $request->note
            );
        });

        return back()->with('status', "Status tiket {$item->ticket_no} diubah menjadi {$item->status_label}.");
    }
}
