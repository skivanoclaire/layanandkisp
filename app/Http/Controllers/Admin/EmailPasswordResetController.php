<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailPasswordResetRequest;
use App\Services\CpanelEmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmailPasswordResetController extends Controller
{
    /**
     * Display all password reset requests
     */
    public function index(Request $request)
    {
        $query = EmailPasswordResetRequest::with(['user', 'processedBy']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('email_address', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $requests = $query->latest()->paginate(15)->appends($request->except('page'));

        return view('admin.email-password-reset.index', compact('requests'));
    }

    /**
     * Show specific request detail
     */
    public function show($id)
    {
        $resetRequest = EmailPasswordResetRequest::with(['user', 'processedBy'])
            ->findOrFail($id);

        // Decrypt password for admin view (be careful with this)
        $decryptedPassword = null;
        if ($resetRequest->status === 'pending') {
            $decryptedPassword = $resetRequest->getDecryptedPassword();
        }

        return view('admin.email-password-reset.show', compact('resetRequest', 'decryptedPassword'));
    }

    /**
     * Process (approve) password reset request
     */
    public function process(Request $request, $id)
    {
        $validated = $request->validate([
            'admin_notes' => 'nullable|string|max:500',
            'reset_method' => 'required|in:manual,api',
        ]);

        $resetRequest = EmailPasswordResetRequest::findOrFail($id);

        if ($resetRequest->status !== 'pending') {
            return back()->with('error', 'Permintaan ini sudah diproses sebelumnya.');
        }

        $successMessage = 'Permintaan reset password berhasil diproses.';

        // If API method, attempt to reset password via cPanel API
        if ($validated['reset_method'] === 'api') {
            try {
                $cpanelService = new CpanelEmailService();
                $decryptedPassword = $resetRequest->getDecryptedPassword();

                $result = $cpanelService->resetPassword(
                    $resetRequest->email_address,
                    $decryptedPassword
                );

                if (!$result['success']) {
                    // API failed, don't mark as processed
                    return back()->with('error', 'Gagal mereset password via API cPanel: ' . $result['message'] . '. Silakan gunakan metode Manual.');
                }

                $successMessage = 'Password berhasil direset otomatis via API cPanel untuk email: ' . $resetRequest->email_address;

            } catch (\Exception $e) {
                // If API fails, don't mark as processed
                return back()->with('error', 'Error saat menghubungi cPanel API: ' . $e->getMessage());
            }
        } else {
            // Manual method
            $successMessage = 'Permintaan berhasil diproses. Silakan reset password secara manual di cPanel untuk email: ' . $resetRequest->email_address;
        }

        // Update status to processed
        $resetRequest->update([
            'status' => 'processed',
            'processed_by' => Auth::id(),
            'processed_at' => now(),
            'admin_notes' => $validated['admin_notes'],
            'reset_method' => $validated['reset_method'],
        ]);

        return redirect()->route('admin.email-password-reset.index')
            ->with('success', $successMessage);
    }

    /**
     * Reject password reset request
     */
    public function reject(Request $request, $id)
    {
        $validated = $request->validate([
            'admin_notes' => 'required|string|max:500',
        ], [
            'admin_notes.required' => 'Alasan penolakan harus diisi',
        ]);

        $resetRequest = EmailPasswordResetRequest::findOrFail($id);

        if ($resetRequest->status !== 'pending') {
            return back()->with('error', 'Permintaan ini sudah diproses sebelumnya.');
        }

        $resetRequest->update([
            'status' => 'rejected',
            'processed_by' => Auth::id(),
            'processed_at' => now(),
            'admin_notes' => $validated['admin_notes'],
        ]);

        return redirect()->route('admin.email-password-reset.index')
            ->with('success', 'Permintaan reset password berhasil ditolak.');
    }
}
