<?php

// app/Http/Controllers/Admin/EmailRequestAdminController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailRequest;
use App\Models\EmailRequestLog;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EmailRequestAdminController extends Controller
{
    public function index(Request $r)
    {
        $status = $r->query('status'); // opsional filter
        $q = EmailRequest::with('user')->orderByDesc('created_at');
        if ($status) $q->where('status',$status);
        $items = $q->paginate(25);
        return view('admin.email.index', compact('items','status'));
    }

    public function show($id)
    {
        $item = EmailRequest::with(['user','logs.actor'])->findOrFail($id);
        return view('admin.email.show', compact('item'));
    }

    public function updateStatus(Request $r, $id)
    {
        $r->validate([
            'status' => ['required','in:menunggu,proses,ditolak,selesai'],
            'note'   => ['nullable','string','max:1000'],
        ]);

        $item = EmailRequest::findOrFail($id);
        $old = $item->status;
        $item->status = $r->status;

        // set jejak waktu
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

    public function exportCsv(Request $r): StreamedResponse
    {
        $status = $r->query('status','selesai'); // default export yang selesai
        $rows = EmailRequest::where('status',$status)->get();

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="email_requests_'.$status.'_'.now()->format('Ymd_His').'.csv"',
        ];

        $columns = ['ticket_no','nama','nip','instansi','username','email_alternatif','no_hp','password_plain','status','submitted_at','completed_at'];

        return response()->stream(function() use ($rows,$columns) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $columns);

            foreach ($rows as $r) {
                /** @var \App\Models\EmailRequest $r */
                $pwd = '';
                try { $pwd = $r->getPlainPassword(); } catch (\Throwable $e) {}
                fputcsv($out, [
                    $r->ticket_no, $r->nama, $r->nip, $r->instansi, $r->username, $r->email_alternatif,
                    $r->no_hp, $pwd, $r->status, optional($r->submitted_at)->toDateTimeString(), optional($r->completed_at)->toDateTimeString(),
                ]);
            }
            fclose($out);
        }, 200, $headers);
    }
}
