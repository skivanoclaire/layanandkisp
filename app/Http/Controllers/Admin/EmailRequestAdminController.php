<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Traits\ExportsDigitalData;
use App\Models\EmailRequest;
use App\Models\EmailRequestLog;
use App\Models\EmailAccount;
use App\Exports\EmailRequestExport;
use App\Services\WhmApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EmailRequestAdminController extends Controller
{
    use ExportsDigitalData;
    // GET /admin/digital/email
    public function index(Request $r)
    {
        $status = $r->query('status'); // ?status=menunggu|proses|ditolak|selesai
        $q = EmailRequest::with('user')->orderByDesc('submitted_at');

        if ($status) $q->where('status', $status);

        // Filter by date range
        if ($r->filled('dari_tanggal')) {
            $q->whereDate('submitted_at', '>=', $r->dari_tanggal);
        }
        if ($r->filled('sampai_tanggal')) {
            $q->whereDate('submitted_at', '<=', $r->sampai_tanggal);
        }

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
    public function updateStatus(Request $r, $id, WhmApiService $whmApi)
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

        // Auto-create email account in cPanel when status is changed to "selesai"
        $cpanelResult = null;
        if ($r->status === 'selesai') {
            try {
                $fullEmail = $item->username . '@kaltaraprov.go.id';
                $plainPassword = $item->getPlainPassword();

                Log::info('Attempting to create email account in cPanel', [
                    'ticket_no' => $item->ticket_no,
                    'email' => $fullEmail
                ]);

                $cpanelResult = $whmApi->createEmailAccount($fullEmail, $plainPassword, 0);

                if ($cpanelResult['success']) {
                    Log::info('Email account created successfully', [
                        'ticket_no' => $item->ticket_no,
                        'email' => $fullEmail,
                        'message' => $cpanelResult['message']
                    ]);

                    // Add log entry for successful creation
                    EmailRequestLog::create([
                        'email_request_id' => $item->id,
                        'actor_id' => auth()->id(),
                        'action'   => 'email_created_in_cpanel',
                        'note'     => 'Email berhasil dibuat di CPanel: ' . $cpanelResult['message'],
                    ]);

                    // Auto-create record in Master Data Email (email_accounts table)
                    try {
                        EmailAccount::updateOrCreate(
                            ['email' => $fullEmail], // Check if email already exists
                            [
                                'domain' => 'kaltaraprov.go.id',
                                'user' => $item->username,
                                'nip' => $item->nip,
                                'disk_used' => 0,
                                'disk_quota' => 0, // Unlimited quota
                                'diskused_readable' => '0 MB',
                                'diskquota_readable' => 'Unlimited',
                                'suspended' => 0,
                                'last_synced_at' => now(),
                            ]
                        );

                        Log::info('Email account created in Master Data Email', [
                            'ticket_no' => $item->ticket_no,
                            'email' => $fullEmail,
                            'nip' => $item->nip
                        ]);

                        EmailRequestLog::create([
                            'email_request_id' => $item->id,
                            'actor_id' => auth()->id(),
                            'action'   => 'email_added_to_master_data',
                            'note'     => 'Email ditambahkan ke Master Data Email dengan NIP: ' . $item->nip,
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Failed to create email in Master Data Email', [
                            'ticket_no' => $item->ticket_no,
                            'email' => $fullEmail,
                            'error' => $e->getMessage()
                        ]);
                    }
                } else {
                    Log::error('Failed to create email account in cPanel', [
                        'ticket_no' => $item->ticket_no,
                        'email' => $fullEmail,
                        'error' => $cpanelResult['message']
                    ]);

                    // Add log entry for failed creation
                    EmailRequestLog::create([
                        'email_request_id' => $item->id,
                        'actor_id' => auth()->id(),
                        'action'   => 'email_creation_failed',
                        'note'     => 'Gagal membuat email di CPanel: ' . $cpanelResult['message'],
                    ]);
                }

            } catch (\Exception $e) {
                Log::error('Exception while creating email in cPanel', [
                    'ticket_no' => $item->ticket_no,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);

                // Add log entry for exception
                EmailRequestLog::create([
                    'email_request_id' => $item->id,
                    'actor_id' => auth()->id(),
                    'action'   => 'email_creation_exception',
                    'note'     => 'Error saat membuat email: ' . $e->getMessage(),
                ]);

                $cpanelResult = [
                    'success' => false,
                    'message' => $e->getMessage()
                ];
            }
        }

        // Create status change log
        EmailRequestLog::create([
            'email_request_id' => $item->id,
            'actor_id' => auth()->id(),
            'action'   => "status:$old->$item->status",
            'note'     => $r->note,
        ]);

        // Build response message
        $message = "Status tiket {$item->ticket_no} diubah menjadi {$item->status}.";

        if ($cpanelResult) {
            if ($cpanelResult['success']) {
                $message .= " Email berhasil dibuat di CPanel server.";
            } else {
                $message .= " PERHATIAN: Gagal membuat email di CPanel - " . $cpanelResult['message'];
            }
        }

        return back()->with('status', $message);
    }

    // GET /admin/digital/email/export-excel
    public function exportExcel(Request $r)
    {
        return $this->exportToExcel(
            $r,
            EmailRequestExport::class,
            'permohonan-email'
        );
    }

    // GET /admin/digital/email/export-pdf
    public function exportPdf(Request $r)
    {
        return $this->exportToPdf(
            $r,
            EmailRequest::class,
            'admin.email.pdf',
            'permohonan-email'
        );
    }

    // GET /admin/digital/email/export/csv?status=selesai (KEPT FOR BACKWARD COMPATIBILITY - Username/Password Export)
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

    // POST /admin/digital/email/{id}/update-password
    public function updatePassword(Request $r, $id)
    {
        $r->validate([
            'new_password' => [
                'required',
                'string',
                Password::min(15)
                    ->mixedCase()      // Huruf besar dan kecil
                    ->numbers()         // Angka
                    ->symbols()         // Simbol
            ],
        ]);

        $item = EmailRequest::findOrFail($id);

        // Update password
        $item->setPlainPassword($r->new_password);
        $item->save();

        // Log the password update
        EmailRequestLog::create([
            'email_request_id' => $item->id,
            'actor_id' => auth()->id(),
            'action'   => 'password_updated',
            'note'     => 'Password diperbarui oleh admin',
        ]);

        return back()->with('password_updated', 'Password berhasil diperbarui.');
    }

}
