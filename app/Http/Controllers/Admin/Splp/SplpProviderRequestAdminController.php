<?php

namespace App\Http\Controllers\Admin\Splp;

use App\Http\Controllers\Controller;
use App\Models\SplpProviderRequest;
use App\Models\SplpRequestLog;
use App\Models\SplpService;
use App\Models\SplpServiceLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * V1 — Pendaftaran Endpoint Penyedia Layanan — sisi admin (verifikasi, keputusan, provisioning).
 */
class SplpProviderRequestAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = SplpProviderRequest::with(['user', 'unitKerja'])
            ->where('status', '!=', SplpProviderRequest::STATUS_DRAFT)
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

        return view('admin.splp.provider.index', compact('items', 'status'));
    }

    public function show($id)
    {
        $item = SplpProviderRequest::with(['user', 'unitKerja', 'service', 'logs.actor'])->findOrFail($id);

        return view('admin.splp.provider.show', compact('item'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => ['required', Rule::in(SplpProviderRequest::adminStatuses())],
            'note' => ['nullable', 'string', 'max:1000'],
            'catatan_verifikasi' => ['nullable', 'string', 'max:2000'],
            'rejection_reason' => ['required_if:status,ditolak', 'nullable', 'string', 'max:1000'],
            'check_administrasi' => ['nullable', 'boolean'],
            'check_teknis' => ['nullable', 'boolean'],
            'check_dokumentasi' => ['nullable', 'boolean'],
            'check_klasifikasi_data' => ['nullable', 'boolean'],
        ]);

        $item = SplpProviderRequest::with('unitKerja')->findOrFail($id);
        $old = $item->status;

        DB::transaction(function () use ($request, $item, $old) {
            // Update checklist & catatan verifikasi
            $item->check_administrasi = $request->boolean('check_administrasi');
            $item->check_teknis = $request->boolean('check_teknis');
            $item->check_dokumentasi = $request->boolean('check_dokumentasi');
            $item->check_klasifikasi_data = $request->boolean('check_klasifikasi_data');
            if ($request->filled('catatan_verifikasi')) {
                $item->catatan_verifikasi = $request->catatan_verifikasi;
            }

            $item->status = $request->status;

            // Timestamp per tahap
            match ($request->status) {
                SplpProviderRequest::STATUS_VERIF_ADMIN => $item->verif_admin_at = now(),
                SplpProviderRequest::STATUS_VERIF_TEKNIS => $item->verif_teknis_at = now(),
                SplpProviderRequest::STATUS_DISETUJUI => $item->decided_at = now(),
                SplpProviderRequest::STATUS_SELESAI => $item->completed_at = now(),
                SplpProviderRequest::STATUS_DITOLAK => $item->rejected_at = now(),
                default => null,
            };

            if ($request->status === SplpProviderRequest::STATUS_DITOLAK) {
                $item->rejection_reason = $request->rejection_reason;
            }

            // Provisioning (record-keeping) saat selesai — buat registry layanan bila belum ada
            if ($request->status === SplpProviderRequest::STATUS_SELESAI && !$item->splp_service_id) {
                $service = SplpService::create([
                    'kode_layanan' => SplpService::nextKode(),
                    'nama_layanan' => $item->nama_layanan,
                    'opd_pemilik_id' => $item->unit_kerja_id,
                    'deskripsi' => $item->deskripsi,
                    'backend_url' => $item->backend_url,
                    'route_path' => $item->route_path,
                    'environment' => 'produksi',
                    'auth_type' => $item->auth_type,
                    'klasifikasi_data' => $item->klasifikasi_data,
                    'status' => 'aktif',
                    'tgl_aktif' => now()->toDateString(),
                    'source_request_type' => SplpProviderRequest::REQUEST_TYPE,
                    'source_request_id' => $item->id,
                ]);

                $item->splp_service_id = $service->id;

                SplpServiceLog::record([
                    'splp_service_id' => $service->id,
                    'action' => 'service_provisioned',
                    'config_baru' => $service->only([
                        'kode_layanan', 'nama_layanan', 'backend_url', 'route_path', 'auth_type', 'klasifikasi_data',
                    ]),
                    'keterangan' => "Layanan dibuat dari permohonan {$item->ticket_no}",
                ]);
            }

            $item->save();

            SplpRequestLog::record(
                SplpProviderRequest::REQUEST_TYPE,
                $item->id,
                "status:{$old}->{$item->status}",
                $request->note
            );
        });

        return back()->with('status', "Status tiket {$item->ticket_no} diubah menjadi {$item->status_label}.");
    }
}
