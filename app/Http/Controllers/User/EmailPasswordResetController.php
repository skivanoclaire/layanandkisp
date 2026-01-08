<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\EmailPasswordResetRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class EmailPasswordResetController extends Controller
{
    /**
     * Display form to request password reset
     */
    public function create()
    {
        $user = Auth::user();

        // Check if user has NIP
        if (empty($user->nip)) {
            return redirect()->route('user.email-password-reset.index')
                ->with('error', 'NIP Anda belum terverifikasi, hubungi Admin untuk pembaruan data.');
        }

        // Check if email exists in email_accounts (master data) by matching NIP
        $emailAccount = \App\Models\EmailAccount::where('nip', $user->nip)->first();

        if (!$emailAccount) {
            return redirect()->route('user.email-password-reset.index')
                ->with('error', 'Email Anda belum terverifikasi, hubungi Admin untuk pembaruan data.');
        }

        // Get email address from master data
        $emailAddress = $emailAccount->email;

        return view('user.email-password-reset.create', compact('user', 'emailAddress'));
    }

    /**
     * Store password reset request
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        // Check if user has NIP
        if (empty($user->nip)) {
            return back()->with('error', 'NIP Anda belum terverifikasi, hubungi Admin untuk pembaruan data.');
        }

        // Check if email exists in email_accounts (master data) by matching NIP
        $emailAccount = \App\Models\EmailAccount::where('nip', $user->nip)->first();

        if (!$emailAccount) {
            return back()->with('error', 'Email Anda belum terverifikasi, hubungi Admin untuk pembaruan data.');
        }

        // Get email address from master data
        $emailAddress = $emailAccount->email;

        // Validate input
        $validated = $request->validate([
            'password' => [
                'required',
                'confirmed',
                Password::min(10)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(),
            ],
        ], [
            'password.required' => 'Password baru harus diisi',
            'password.confirmed' => 'Konfirmasi password tidak cocok',
            'password.min' => 'Password minimal 10 karakter',
            'password.mixed_case' => 'Password harus mengandung huruf besar dan kecil',
            'password.numbers' => 'Password harus mengandung angka',
            'password.symbols' => 'Password harus mengandung simbol',
            'password.uncompromised' => 'Password ini telah terdeteksi dalam data breach, gunakan password lain',
        ]);

        // Check if there's already a pending request
        $existingRequest = EmailPasswordResetRequest::where('user_id', $user->id)
            ->where('status', 'pending')
            ->first();

        if ($existingRequest) {
            return back()->with('error', 'Anda sudah memiliki permintaan reset password yang sedang diproses. Silakan tunggu admin memproses permintaan sebelumnya.');
        }

        // Create reset request
        $resetRequest = EmailPasswordResetRequest::create([
            'user_id' => $user->id,
            'email_address' => $emailAddress,
            'nip' => $user->nip,
            'encrypted_password' => \Illuminate\Support\Facades\Crypt::encryptString($validated['password']),
            'status' => 'pending',
        ]);

        return redirect()->route('user.email-password-reset.index')
            ->with('success', 'Permintaan reset password email berhasil diajukan. Admin akan segera memproses permintaan Anda.');
    }

    /**
     * Display user's password reset requests history
     */
    public function index()
    {
        $requests = EmailPasswordResetRequest::where('user_id', Auth::id())
            ->with('processedBy')
            ->latest()
            ->paginate(10);

        return view('user.email-password-reset.index', compact('requests'));
    }

    /**
     * Show specific request detail
     */
    public function show($id)
    {
        $request = EmailPasswordResetRequest::where('user_id', Auth::id())
            ->with('processedBy')
            ->findOrFail($id);

        return view('user.email-password-reset.show', compact('request'));
    }
}
