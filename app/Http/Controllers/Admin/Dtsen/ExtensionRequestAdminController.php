<?php

namespace App\Http\Controllers\Admin\Dtsen;

use App\Http\Controllers\Controller;
use App\Models\DtsenDataRequest;
use App\Models\DtsenExtensionRequest;
use App\Models\DtsenRequestLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Form 4.4 - Keputusan atas permohonan perpanjangan masa akses (narahubung DKISP).
 */
class ExtensionRequestAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = DtsenExtensionRequest::with(['dataRequest.unitKerja', 'user', 'token', 'decidedBy'])
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $items = $query->paginate(25)->withQueryString();

        return view('admin.dtsen.perpanjangan.index', compact('items'));
    }

    public function decide(Request $request, $id)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in([
                DtsenExtensionRequest::STATUS_DISETUJUI,
                DtsenExtensionRequest::STATUS_DITOLAK,
            ])],
            'durasi_hari' => ['nullable', 'integer', 'min:1', 'max:' . DtsenExtensionRequest::MAX_DURASI_HARI],
            'catatan' => ['required_if:status,' . DtsenExtensionRequest::STATUS_DITOLAK, 'nullable', 'string', 'max:1000'],
        ], [
            'catatan.required_if' => 'Alasan penolakan wajib diisi.',
        ]);

        $item = DtsenExtensionRequest::with(['token', 'dataRequest'])->findOrFail($id);

        abort_unless(
            $item->status === DtsenExtensionRequest::STATUS_DIAJUKAN,
            400,
            'Permohonan perpanjangan ini sudah diputuskan.'
        );

        DB::transaction(function () use ($item, $data) {
            // Admin boleh memberi durasi berbeda dari yang diminta pemohon.
            $durasi = (int) ($data['durasi_hari'] ?? $item->durasi_hari);

            $item->status = $data['status'];
            $item->durasi_hari = $durasi;
            $item->catatan = $data['catatan'] ?? null;
            $item->decided_by = auth()->id();
            $item->decided_at = now();
            $item->save();

            if ($data['status'] === DtsenExtensionRequest::STATUS_DISETUJUI && $item->token) {
                $token = $item->token;
                // Perpanjangan dihitung dari tanggal kedaluwarsa bila masih berlaku,
                // atau dari hari ini bila token sudah lewat masa aktifnya.
                $anchor = $token->expires_at && $token->expires_at->isFuture() ? $token->expires_at : now();
                $token->expires_at = $anchor->copy()->addDays($durasi);
                $token->expiry_warned_at = null;
                $token->save();

                // Permohonan yang sempat ditandai kedaluwarsa dibuka kembali
                // begitu masa akses diperpanjang.
                $dataRequest = $item->dataRequest;
                if ($dataRequest && $dataRequest->status === DtsenDataRequest::STATUS_KEDALUWARSA) {
                    $dataRequest->status = DtsenDataRequest::STATUS_DATA_TERSEDIA;
                    $dataRequest->save();
                }
            }
        });

        DtsenRequestLog::record(
            DtsenRequestLog::TYPE_PERPANJANGAN,
            $item->id,
            $item->status,
            $item->status === DtsenExtensionRequest::STATUS_DISETUJUI
                ? "Masa akses diperpanjang {$item->durasi_hari} hari"
                : $item->catatan
        );

        return back()->with('status', 'Keputusan perpanjangan masa akses disimpan.');
    }
}
