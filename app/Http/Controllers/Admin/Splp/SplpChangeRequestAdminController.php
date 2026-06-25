<?php

namespace App\Http\Controllers\Admin\Splp;

use App\Http\Controllers\Controller;
use App\Models\SplpChangeRequest;
use App\Models\SplpRequestLog;
use App\Models\SplpServiceLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * V4 — Perubahan / Perpanjangan Konfigurasi Endpoint — sisi admin.
 */
class SplpChangeRequestAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = SplpChangeRequest::with(['user', 'unitKerja', 'service'])
            ->where('status', '!=', SplpChangeRequest::STATUS_DRAFT)
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

        return view('admin.splp.change.index', compact('items', 'status'));
    }

    public function show($id)
    {
        $item = SplpChangeRequest::with(['user', 'unitKerja', 'service.opdPemilik', 'logs.actor'])->findOrFail($id);

        return view('admin.splp.change.show', compact('item'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => ['required', Rule::in(SplpChangeRequest::adminStatuses())],
            'note' => ['nullable', 'string', 'max:1000'],
            'analisis_dampak' => ['nullable', 'string', 'max:2000'],
            'catatan_verifikasi' => ['nullable', 'string', 'max:2000'],
            'rejection_reason' => ['required_if:status,ditolak', 'nullable', 'string', 'max:1000'],
            'check_administrasi' => ['nullable', 'boolean'],
            'check_dampak' => ['nullable', 'boolean'],
        ]);

        $item = SplpChangeRequest::with('service')->findOrFail($id);
        $old = $item->status;

        DB::transaction(function () use ($request, $item, $old) {
            $item->check_administrasi = $request->boolean('check_administrasi');
            $item->check_dampak = $request->boolean('check_dampak');
            if ($request->filled('analisis_dampak')) {
                $item->analisis_dampak = $request->analisis_dampak;
            }
            if ($request->filled('catatan_verifikasi')) {
                $item->catatan_verifikasi = $request->catatan_verifikasi;
            }

            $item->status = $request->status;

            match ($request->status) {
                SplpChangeRequest::STATUS_VERIF_ADMIN => $item->verif_admin_at = now(),
                SplpChangeRequest::STATUS_VERIF_TEKNIS => $item->verif_teknis_at = now(),
                SplpChangeRequest::STATUS_DISETUJUI => $item->decided_at = now(),
                SplpChangeRequest::STATUS_SELESAI => $item->completed_at = now(),
                SplpChangeRequest::STATUS_DITOLAK => $item->rejected_at = now(),
                default => null,
            };

            if ($request->status === SplpChangeRequest::STATUS_DITOLAK) {
                $item->rejection_reason = $request->rejection_reason;
            }

            // Saat selesai: catat perubahan ke audit trail layanan (konfigurasi diperbarui manual via Master Data)
            if ($request->status === SplpChangeRequest::STATUS_SELESAI && $item->service) {
                SplpServiceLog::record([
                    'splp_service_id' => $item->splp_service_id,
                    'action' => $item->kategori === 'perpanjangan' ? 'service_extended' : 'service_changed',
                    'config_baru' => [
                        'kategori' => $item->kategori,
                        'jenis_perubahan' => $item->jenis_perubahan,
                        'detail' => $item->detail_perubahan,
                        'perpanjangan_sampai' => optional($item->perpanjangan_sampai)->toDateString(),
                    ],
                    'keterangan' => "Perubahan/perpanjangan dari permohonan {$item->ticket_no}: {$item->detail_perubahan}",
                ]);

                if ($item->kategori === 'perpanjangan' && $item->perpanjangan_sampai) {
                    $item->service->update(['tgl_aktif' => $item->service->tgl_aktif ?: now()->toDateString()]);
                }
            }

            $item->save();

            SplpRequestLog::record(
                SplpChangeRequest::REQUEST_TYPE,
                $item->id,
                "status:{$old}->{$item->status}",
                $request->note
            );
        });

        return back()->with('status', "Status tiket {$item->ticket_no} diubah menjadi {$item->status_label}.");
    }
}
