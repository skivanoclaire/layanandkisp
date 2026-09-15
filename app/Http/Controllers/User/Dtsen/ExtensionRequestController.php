<?php

namespace App\Http\Controllers\User\Dtsen;

use App\Http\Controllers\Controller;
use App\Models\DtsenDataRequest;
use App\Models\DtsenExtensionRequest;
use App\Models\DtsenRequestLog;
use Illuminate\Http\Request;

/**
 * Form 4.4 - Permohonan perpanjangan masa akses token/tautan unduh.
 */
class ExtensionRequestController extends Controller
{
    public function index()
    {
        $items = DtsenExtensionRequest::with(['dataRequest', 'decidedBy'])
            ->where('user_id', auth()->id())
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('user.dtsen.perpanjangan.index', compact('items'));
    }

    public function store(Request $request, $id)
    {
        $item = DtsenDataRequest::where('id', $id)->where('user_id', auth()->id())->firstOrFail();

        $data = $request->validate([
            'alasan' => ['required', 'string', 'max:2000'],
            'durasi_hari' => ['required', 'integer', 'min:1', 'max:' . DtsenExtensionRequest::MAX_DURASI_HARI],
        ], [
            'durasi_hari.max' => 'Durasi perpanjangan maksimal ' . DtsenExtensionRequest::MAX_DURASI_HARI . ' hari kalender per pengajuan.',
        ]);

        // Token terakhir dipakai sebagai acuan, termasuk bila sudah kedaluwarsa —
        // justru kondisi itulah yang biasanya memicu permohonan perpanjangan.
        $token = $item->tokens()->whereNull('revoked_at')->first();

        abort_if($token === null, 400, 'Belum ada token akses yang dapat diperpanjang untuk permohonan ini.');

        $pending = $item->extensionRequests()
            ->where('status', DtsenExtensionRequest::STATUS_DIAJUKAN)
            ->exists();

        if ($pending) {
            return back()->withErrors(['alasan' => 'Masih ada permohonan perpanjangan yang menunggu keputusan.']);
        }

        $extension = DtsenExtensionRequest::create([
            'dtsen_data_request_id' => $item->id,
            'dtsen_access_token_id' => $token->id,
            'user_id' => auth()->id(),
            'alasan' => $data['alasan'],
            'durasi_hari' => $data['durasi_hari'],
            'status' => DtsenExtensionRequest::STATUS_DIAJUKAN,
        ]);

        DtsenRequestLog::record(
            DtsenRequestLog::TYPE_PERPANJANGAN,
            $extension->id,
            'diajukan',
            "Perpanjangan {$data['durasi_hari']} hari diajukan untuk {$item->ticket_no}"
        );

        return back()->with('status', 'Permohonan perpanjangan masa akses dikirim ke narahubung layanan DTSEN (DKISP).');
    }
}
