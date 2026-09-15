<?php

namespace App\Http\Controllers\User\Dtsen;

use App\Http\Controllers\Controller;
use App\Models\DtsenAccessToken;
use App\Models\DtsenRequestLog;
use Illuminate\Support\Facades\Storage;

/**
 * Fitur 4.3 - Pengambilan data lewat token/tautan unduh, lengkap dengan log audit.
 */
class AccessController extends Controller
{
    public function show(string $token)
    {
        $item = $this->resolveToken($token);

        return view('user.dtsen.akses.show', [
            'token' => $item,
            'item' => $item->dataRequest,
            'downloads' => $item->downloadLogs()->with('user')->limit(20)->get(),
        ]);
    }

    public function download(string $token)
    {
        $item = $this->resolveToken($token);

        abort_if($item->isRevoked(), 403, 'Token akses telah dicabut oleh DKISP.');
        abort_if($item->isExpired(), 410, 'Masa aktif token telah berakhir. Ajukan perpanjangan masa akses.');
        abort_if($item->reachedDownloadLimit(), 429, 'Batas jumlah unduhan untuk token ini telah tercapai.');
        abort_if(
            $item->metode !== 'excel_terenkripsi' || ! $item->file_path,
            404,
            'Tidak ada berkas unduhan untuk token ini. Gunakan parameter kanal yang diberikan DKISP.'
        );
        abort_unless(Storage::disk('public')->exists($item->file_path), 404, 'Berkas data tidak ditemukan.');

        $item->recordDownload(auth()->user());
        $item->dataRequest?->accountRequest?->touchUsage();

        DtsenRequestLog::record(
            DtsenRequestLog::TYPE_PERMOHONAN,
            $item->dtsen_data_request_id,
            'data_diunduh',
            'Data diunduh melalui token akses'
        );

        return Storage::disk('public')->download(
            $item->file_path,
            $item->nama_berkas ?: basename($item->file_path)
        );
    }

    /**
     * Token hanya boleh dipakai oleh pemohon atau personel yang terdaftar pada
     * akun DTSEN OPD yang bersangkutan.
     */
    private function resolveToken(string $token): DtsenAccessToken
    {
        $item = DtsenAccessToken::with('dataRequest.accountRequest.members')
            ->where('token', $token)
            ->firstOrFail();

        $request = $item->dataRequest;
        $user = auth()->user();

        $allowed = $request && (
            $request->user_id === $user->id
            || ($request->accountRequest?->members ?? collect())->contains(
                fn ($m) => $m->user_id === $user->id || $m->email === $user->email
            )
        );

        abort_unless($allowed, 403, 'Anda tidak berhak mengakses data pada token ini.');

        return $item;
    }
}
