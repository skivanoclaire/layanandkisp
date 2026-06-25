<?php

namespace App\Http\Controllers\Admin\Splp;

use App\Http\Controllers\Controller;
use App\Models\SplpConsumer;
use App\Models\SplpRequestLog;
use App\Models\SplpSandboxRequest;
use App\Models\SplpService;
use App\Models\SplpServiceLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * V3 — Permohonan Uji Coba (Sandbox) — sisi admin.
 */
class SplpSandboxRequestAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = SplpSandboxRequest::with(['user', 'unitKerja'])
            ->where('status', '!=', SplpSandboxRequest::STATUS_DRAFT)
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

        return view('admin.splp.sandbox.index', compact('items', 'status'));
    }

    public function show($id)
    {
        $item = SplpSandboxRequest::with(['user', 'unitKerja', 'service', 'consumer', 'logs.actor'])->findOrFail($id);

        return view('admin.splp.sandbox.show', compact('item'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => ['required', Rule::in(SplpSandboxRequest::adminStatuses())],
            'note' => ['nullable', 'string', 'max:1000'],
            'catatan_verifikasi' => ['nullable', 'string', 'max:2000'],
            'rejection_reason' => ['required_if:status,ditolak', 'nullable', 'string', 'max:1000'],
            'check_administrasi' => ['nullable', 'boolean'],
            'check_spesifikasi' => ['nullable', 'boolean'],
            'check_sumberdaya' => ['nullable', 'boolean'],
        ]);

        $item = SplpSandboxRequest::with('unitKerja')->findOrFail($id);
        $old = $item->status;

        DB::transaction(function () use ($request, $item, $old) {
            $item->check_administrasi = $request->boolean('check_administrasi');
            $item->check_spesifikasi = $request->boolean('check_spesifikasi');
            $item->check_sumberdaya = $request->boolean('check_sumberdaya');
            if ($request->filled('catatan_verifikasi')) {
                $item->catatan_verifikasi = $request->catatan_verifikasi;
            }

            $item->status = $request->status;

            match ($request->status) {
                SplpSandboxRequest::STATUS_VERIF_ADMIN => $item->verif_admin_at = now(),
                SplpSandboxRequest::STATUS_VERIF_TEKNIS => $item->verif_teknis_at = now(),
                SplpSandboxRequest::STATUS_DISETUJUI => $item->decided_at = now(),
                SplpSandboxRequest::STATUS_SELESAI => $item->completed_at = now(),
                SplpSandboxRequest::STATUS_DITOLAK => $item->rejected_at = now(),
                default => null,
            };

            if ($request->status === SplpSandboxRequest::STATUS_DITOLAK) {
                $item->rejection_reason = $request->rejection_reason;
            }

            // Provisioning sandbox saat selesai: buat layanan + kredensial sandbox sementara (tanpa data produksi)
            if ($request->status === SplpSandboxRequest::STATUS_SELESAI && !$item->splp_service_id) {
                $service = SplpService::create([
                    'kode_layanan' => SplpService::nextKode(),
                    'nama_layanan' => '[SANDBOX] ' . $item->nama_layanan,
                    'opd_pemilik_id' => $item->unit_kerja_id,
                    'deskripsi' => $item->spesifikasi_draft,
                    'environment' => 'sandbox',
                    'auth_type' => 'apikey',
                    'klasifikasi_data' => 'publik',
                    'status' => 'aktif',
                    'tgl_aktif' => now()->toDateString(),
                    'source_request_type' => SplpSandboxRequest::REQUEST_TYPE,
                    'source_request_id' => $item->id,
                ]);

                $consumer = SplpConsumer::create([
                    'splp_service_id' => $service->id,
                    'instansi_id' => $item->unit_kerja_id,
                    'nama_konsumen' => $item->unitKerja->nama ?? $item->nama,
                    'credential_type' => 'apikey',
                    'environment' => 'sandbox',
                    'expires_at' => now()->addDays($item->masa_uji_hari),
                    'status' => 'aktif',
                    'source_request_type' => SplpSandboxRequest::REQUEST_TYPE,
                    'source_request_id' => $item->id,
                ]);

                $item->splp_service_id = $service->id;
                $item->splp_consumer_id = $consumer->id;

                SplpServiceLog::record([
                    'splp_service_id' => $service->id,
                    'splp_consumer_id' => $consumer->id,
                    'action' => 'sandbox_provisioned',
                    'config_baru' => ['masa_uji_hari' => $item->masa_uji_hari, 'expires_at' => $consumer->expires_at?->toDateTimeString()],
                    'keterangan' => "Lingkungan sandbox dibuat dari permohonan {$item->ticket_no} (berlaku {$item->masa_uji_hari} hari)",
                ]);
            }

            $item->save();

            SplpRequestLog::record(
                SplpSandboxRequest::REQUEST_TYPE,
                $item->id,
                "status:{$old}->{$item->status}",
                $request->note
            );
        });

        return back()->with('status', "Status tiket {$item->ticket_no} diubah menjadi {$item->status_label}.");
    }
}
