<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailAccount;
use App\Services\WhmApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EmailAccountController extends Controller
{
    protected $whmApi;

    public function __construct(WhmApiService $whmApi)
    {
        $this->whmApi = $whmApi;
    }

    public function index(Request $request)
    {
        $query = EmailAccount::query();

        // Filter by suspended status
        if ($request->filled('suspended')) {
            $query->where('suspended', $request->suspended);
        }

        // Search by email
        if ($request->filled('search')) {
            $query->where('email', 'like', '%' . $request->search . '%');
        }

        // Search by NIP (from requester info)
        if ($request->filled('nip')) {
            $query->where('requester_nip', 'like', '%' . $request->nip . '%');
        }

        // Search by Name (from requester info)
        if ($request->filled('name')) {
            $query->where('requester_name', 'like', '%' . $request->name . '%');
        }

        // Sorting
        $sortField = $request->get('sort', 'email');
        $sortDirection = $request->get('direction', 'asc');

        // Validate sort field
        $allowedSortFields = ['email', 'domain', 'user', 'disk_used', 'suspended', 'last_synced_at'];
        if (!in_array($sortField, $allowedSortFields)) {
            $sortField = 'email';
        }

        // Validate sort direction
        if (!in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'asc';
        }

        // Sort by username (part before @) when sorting by email
        if ($sortField === 'email') {
            $query->orderByRaw("SUBSTRING_INDEX(email, '@', 1) {$sortDirection}");
        } else {
            $query->orderBy($sortField, $sortDirection);
        }

        // Get all unique domains for filter dropdown
        $domains = EmailAccount::distinct()->pluck('domain')->sort();

        $emailAccounts = $query->paginate(50);

        return view('admin.email-accounts.index', compact('emailAccounts', 'domains'));
    }

    public function sync()
    {
        try {
            $emailData = $this->whmApi->getAllEmailAccountsData();

            if (!$emailData['success']) {
                return redirect()->back()->with('error', 'Gagal mengambil data dari WHM: ' . $emailData['message']);
            }

            $accounts = $emailData['accounts'];
            $syncedCount = 0;
            $errors = [];

            foreach ($accounts as $accountData) {
                try {
                    EmailAccount::updateOrCreate(
                        ['email' => $accountData['email']],
                        [
                            'domain' => $accountData['domain'],
                            'user' => $accountData['user'],
                            'disk_used' => $accountData['disk_used'],
                            'disk_quota' => $accountData['disk_quota'],
                            'diskused_readable' => $accountData['diskused_readable'],
                            'diskquota_readable' => $accountData['diskquota_readable'],
                            'suspended' => $accountData['suspended'],
                            'last_synced_at' => now(),
                        ]
                    );
                    $syncedCount++;
                } catch (\Exception $e) {
                    $errors[] = "Error syncing {$accountData['email']}: " . $e->getMessage();
                    Log::error("Email sync error: " . $e->getMessage());
                }
            }

            $message = "Berhasil sinkronisasi {$syncedCount} akun email.";
            if (count($errors) > 0) {
                $message .= " Dengan " . count($errors) . " error.";
            }

            return redirect()->route('admin.email-accounts.index')->with('success', $message);

        } catch (\Exception $e) {
            Log::error("WHM sync error: " . $e->getMessage());
            return redirect()->back()->with('error', 'Error saat sinkronisasi: ' . $e->getMessage());
        }
    }

    public function testConnection()
    {
        try {
            $result = $this->whmApi->testConnection();

            if ($result['success']) {
                return redirect()->back()->with('success', 'Koneksi berhasil! WHM Version: ' . $result['version']);
            } else {
                return redirect()->back()->with('error', 'Koneksi gagal: ' . $result['message']);
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function show(EmailAccount $emailAccount)
    {
        // Get the user who requested this email from email_requests
        $requestingUser = $emailAccount->getRequestingUser();

        // Create a mock user object if we have requester info stored directly in email_accounts
        if (!$requestingUser && ($emailAccount->requester_name || $emailAccount->requester_nip)) {
            $requestingUser = (object) [
                'name' => $emailAccount->requester_name,
                'nip' => $emailAccount->requester_nip,
                'instansi' => $emailAccount->requester_instansi,
                'email' => $emailAccount->requester_email,
                'phone' => $emailAccount->requester_phone,
                'roles' => collect([]), // Empty collection for roles
            ];
        }

        // Get unit kerja list for instansi dropdown
        $unitKerjaList = \App\Models\UnitKerja::active()->orderBy('nama')->get();

        return view('admin.email-accounts.show', compact('emailAccount', 'requestingUser', 'unitKerjaList'));
    }

    public function destroy(EmailAccount $emailAccount)
    {
        $email = $emailAccount->email;
        $emailAccount->delete();

        return redirect()->route('admin.email-accounts.index')
            ->with('success', "Akun email {$email} berhasil dihapus dari database lokal.");
    }

    public function destroyAll()
    {
        $count = EmailAccount::count();
        EmailAccount::truncate();

        return redirect()->route('admin.email-accounts.index')
            ->with('success', "Berhasil menghapus {$count} akun email dari database lokal.");
    }

    public function showImportNip()
    {
        return view('admin.email-accounts.import-nip');
    }

    public function importNip(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048'
        ]);

        try {
            $file = $request->file('csv_file');
            $csvData = array_map('str_getcsv', file($file->getRealPath()));

            $matchedCount = 0;
            $notFoundCount = 0;
            $updatedCount = 0;
            $invalidNipCount = 0;
            $errors = [];

            foreach ($csvData as $index => $row) {
                // Skip empty rows
                if (empty($row[0]) || empty($row[1])) {
                    continue;
                }

                $nip = trim($row[0]);
                $email = trim($row[1]);

                // Validate NIP must be exactly 18 digits
                if (!preg_match('/^\d{18}$/', $nip)) {
                    $invalidNipCount++;
                    Log::warning("Invalid NIP format", [
                        'nip' => $nip,
                        'email' => $email,
                        'length' => strlen($nip)
                    ]);
                    continue;
                }

                // Find email account matching the email address
                $emailAccount = EmailAccount::where('email', $email)->first();

                if ($emailAccount) {
                    $matchedCount++;

                    // Only update if NIP is different
                    if ($emailAccount->nip !== $nip) {
                        $emailAccount->nip = $nip;
                        $emailAccount->save();
                        $updatedCount++;

                        Log::info("NIP updated for {$emailAccount->email}", [
                            'nip' => $nip,
                            'email' => $email
                        ]);
                    }
                } else {
                    $notFoundCount++;
                    Log::warning("Email not found in master data", [
                        'email' => $email,
                        'nip' => $nip
                    ]);
                }
            }

            $message = "Import selesai! " .
                       "Ditemukan: {$matchedCount}, " .
                       "Diupdate: {$updatedCount}, " .
                       "Tidak ditemukan: {$notFoundCount}";

            if ($invalidNipCount > 0) {
                $message .= ", NIP tidak valid (bukan 18 digit): {$invalidNipCount}";
            }

            return redirect()->route('admin.email-accounts.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            Log::error("NIP import error: " . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error saat import NIP: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function updateNip(Request $request, EmailAccount $emailAccount)
    {
        $request->validate([
            'nip' => ['nullable', 'regex:/^\d{18}$/']
        ], [
            'nip.regex' => 'NIP harus berisi tepat 18 digit angka.'
        ]);

        $emailAccount->nip = $request->nip;
        $emailAccount->save();

        Log::info("NIP manually updated by admin", [
            'email' => $emailAccount->email,
            'nip' => $request->nip,
            'admin_id' => auth()->id()
        ]);

        return redirect()->route('admin.email-accounts.show', $emailAccount)
            ->with('success', 'NIP berhasil diupdate.');
    }

    public function updateRequesterInfo(Request $request, EmailAccount $emailAccount)
    {
        $request->validate([
            'requester_name' => ['required', 'string', 'max:255'],
            'requester_nip' => ['nullable', 'regex:/^\d{18}$/'],
            'requester_instansi' => ['nullable', 'string', 'max:255'],
            'requester_email' => ['nullable', 'email', 'max:255'],
            'requester_phone' => ['nullable', 'string', 'max:20'],
        ], [
            'requester_name.required' => 'Nama pemohon harus diisi.',
            'requester_nip.regex' => 'NIP harus berisi tepat 18 digit angka.',
            'requester_email.email' => 'Format email tidak valid.',
        ]);

        $emailAccount->update([
            'requester_name' => $request->requester_name,
            'requester_nip' => $request->requester_nip,
            'requester_instansi' => $request->requester_instansi,
            'requester_email' => $request->requester_email,
            'requester_phone' => $request->requester_phone,
        ]);

        Log::info("Requester info manually updated by admin", [
            'email' => $emailAccount->email,
            'requester_name' => $request->requester_name,
            'admin_id' => auth()->id()
        ]);

        // Redirect back to the return URL or index page
        $returnUrl = $request->input('return_url', route('admin.email-accounts.index'));

        return redirect($returnUrl)
            ->with('success', 'Informasi pemohon berhasil diupdate.');
    }
}
