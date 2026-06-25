<?php

namespace App\Http\Controllers\Admin\Splp;

use App\Http\Controllers\Controller;
use App\Models\SplpConsumer;
use App\Models\SplpConsumerRequest;
use App\Models\SplpRequestLog;
use App\Models\SplpServiceLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * V2 — Pendaftaran Akses Konsumen Layanan — sisi admin (verifikasi, keputusan, provisioning).
 */
class SplpConsumerRequestAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = SplpConsumerRequest::with(['user', 'unitKerja', 'service'])
            ->where('status', '!=', SplpConsumerRequest::STATUS_DRAFT)
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

        return view('admin.splp.consumer.index', compact('items', 'status'));
    }

    public function show($id)
    {
        $item = SplpConsumerRequest::with(['user', 'unitKerja', 'service.opdPemilik', 'instansiKonsumen', 'consumer', 'logs.actor'])
            ->findOrFail($id);

        return view('admin.splp.consumer.show', compact('item'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => ['required', Rule::in(SplpConsumerRequest::adminStatuses())],
            'note' => ['nullable', 'string', 'max:1000'],
            'catatan_verifikasi' => ['nullable', 'string', 'max:2000'],
            'rejection_reason' => ['required_if:status,ditolak', 'nullable', 'string', 'max:1000'],
            'check_administrasi' => ['nullable', 'boolean'],
            'check_koordinasi_opd' => ['nullable', 'boolean'],
            'check_teknis' => ['nullable', 'boolean'],
            'check_legalitas_data' => ['nullable', 'boolean'],
            // Provisioning credential metadata (diisi admin saat selesai)
            'credential_type' => ['nullable', Rule::in(SplpConsumer::CREDENTIAL_TYPES)],
            'credential_ref' => ['nullable', 'string', 'max:200'],
            'rate_limit' => ['nullable', 'string', 'max:100'],
            'acl' => ['nullable', 'string', 'max:200'],
            'expires_at' => ['nullable', 'date'],
        ]);

        $item = SplpConsumerRequest::with('service')->findOrFail($id);
        $old = $item->status;

        DB::transaction(function () use ($request, $item, $old) {
            $item->check_administrasi = $request->boolean('check_administrasi');
            $item->check_koordinasi_opd = $request->boolean('check_koordinasi_opd');
            $item->check_teknis = $request->boolean('check_teknis');
            $item->check_legalitas_data = $request->boolean('check_legalitas_data');
            if ($request->filled('catatan_verifikasi')) {
                $item->catatan_verifikasi = $request->catatan_verifikasi;
            }

            $item->status = $request->status;

            match ($request->status) {
                SplpConsumerRequest::STATUS_VERIF_ADMIN => $item->verif_admin_at = now(),
                SplpConsumerRequest::STATUS_VERIF_TEKNIS => $item->verif_teknis_at = now(),
                SplpConsumerRequest::STATUS_DISETUJUI => $item->decided_at = now(),
                SplpConsumerRequest::STATUS_SELESAI => $item->completed_at = now(),
                SplpConsumerRequest::STATUS_DITOLAK => $item->rejected_at = now(),
                default => null,
            };

            if ($request->status === SplpConsumerRequest::STATUS_DITOLAK) {
                $item->rejection_reason = $request->rejection_reason;
            }

            // Provisioning saat selesai — buat registry konsumen (record-keeping kredensial)
            if ($request->status === SplpConsumerRequest::STATUS_SELESAI && !$item->splp_consumer_id) {
                $credentialType = $request->credential_type
                    ?: ($item->credential_pref !== 'mengikuti_layanan' ? $item->credential_pref : $item->service->auth_type);
                $credentialType = in_array($credentialType, SplpConsumer::CREDENTIAL_TYPES, true) ? $credentialType : 'apikey';

                $consumer = SplpConsumer::create([
                    'splp_service_id' => $item->splp_service_id,
                    'instansi_id' => $item->instansi_konsumen_id,
                    'nama_konsumen' => $item->instansiKonsumen->nama ?? $item->nama,
                    'credential_type' => $credentialType,
                    'credential_ref' => $request->credential_ref,
                    'acl' => $request->acl,
                    'rate_limit' => $request->rate_limit,
                    'ip_whitelist' => $item->ip_domain,
                    'environment' => 'produksi',
                    'expires_at' => $request->expires_at,
                    'status' => 'aktif',
                    'source_request_type' => SplpConsumerRequest::REQUEST_TYPE,
                    'source_request_id' => $item->id,
                ]);

                $item->splp_consumer_id = $consumer->id;

                SplpServiceLog::record([
                    'splp_service_id' => $consumer->splp_service_id,
                    'splp_consumer_id' => $consumer->id,
                    'action' => 'consumer_provisioned',
                    'config_baru' => $consumer->only(['credential_type', 'rate_limit', 'acl', 'expires_at']),
                    'keterangan' => "Konsumen dibuat dari permohonan {$item->ticket_no}",
                ]);
            }

            $item->save();

            SplpRequestLog::record(
                SplpConsumerRequest::REQUEST_TYPE,
                $item->id,
                "status:{$old}->{$item->status}",
                $request->note
            );
        });

        return back()->with('status', "Status tiket {$item->ticket_no} diubah menjadi {$item->status_label}.");
    }
}
