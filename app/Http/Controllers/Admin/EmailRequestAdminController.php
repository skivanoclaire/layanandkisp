<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailRequest;
use App\Models\EmailRequestLog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EmailRequestAdminController extends Controller
{
    // GET /admin/digital/email
    public function index(Request $r)
    {
        $status = $r->query('status'); // ?status=menunggu|proses|ditolak|selesai
        $q = EmailRequest::with('user')->orderByDesc('created_at');
        if ($status) $q->where('status', $status);
        $items = $q->paginate(25);

        return view('admin.email.index', compact('items', 'status'));
    }

    // GET /admin/digital/email/{id}
    public function show($id)
    {
        $item = EmailRequest::with(['user', 'logs.actor'])->findOrFail($id);
        return view('admin.email.show', compact('item'));
    }

    // POST /admin/digital/email/{id}/status
    public function updateStatus(Request $r, $id)
    {
        $r->validate([
            'status' => 'required|in:menunggu,proses,ditolak,selesai',
            'note'   => 'nullable|string|max:1000',
        ]);

        $item = EmailRequest::findOrFail($id);
        $old  = $item->status;
        $item->status = $r->status;

        // cap waktu proses
        if ($r->status === 'proses')   $item->processing_at = now();
        if ($r->status === 'ditolak')  $item->rejected_at   = now();
        if ($r->status === 'selesai')  $item->completed_at  = now();

        $item->save();

        EmailRequestLog::create([
            'email_request_id' => $item->id,
            'actor_id' => auth()->id(),
            'action'   => "status:$old->$item->status",
            'note'     => $r->note,
        ]);

        return back()->with('status', "Status tiket {$item->ticket_no} diubah menjadi {$item->status}.");
    }

    // GET /admin/digital/email/export/csv?status=selesai
    public function exportCsv(Request $r): StreamedResponse
    {
        // Default ekspor yang sudah selesai. Bisa override via ?status=menunggu|proses|ditolak|selesai
        $status = $r->query('status', 'selesai');

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="email_accounts_'.$status.'_'.now()->format('Ymd_His').'.csv"',
            'Cache-Control'       => 'no-store, no-cache',
        ];

        return response()->stream(function () use ($status) {
            $out = fopen('php://output', 'w');

            // (Opsional) tulis BOM supaya Excel Windows baca UTF-8 dengan benar.
            echo "\xEF\xBB\xBF";

            fputcsv($out, ['username', 'password'], ';');

            \App\Models\EmailRequest::where('status', $status)
                ->orderBy('id')
                ->chunk(500, function ($rows) use ($out) {
                    foreach ($rows as $row) {
                        $plain = '';
                        try { $plain = $row->getPlainPassword(); } catch (\Throwable $e) {}

                        // Ekspor dengan pemisah ;
                        fputcsv($out, [$row->username, $plain], ';');
                    }
                });


            fclose($out);
        }, 200, $headers);
    }

}
