<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\TteRegistrationRequest;
use App\Models\EmailRequest;
use App\Models\EmailAccount;
use App\Models\UnitKerja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TteRegistrationController extends Controller
{
    public function index()
    {
        $requests = TteRegistrationRequest::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('user.tte.registration.index', compact('requests'));
    }

    public function create()
    {
        $user = auth()->user();

        // Check if user NIP exists in Master Data Email (email_accounts)
        $emailAccount = EmailAccount::where('requester_nip', $user->nip)
            ->where('suspended', 0)
            ->first();

        if (!$emailAccount) {
            return redirect()->route('user.tte.registration.index')
                ->with('error', 'NIP Anda belum terdaftar di Master Data Email atau email dalam status suspended. Silakan hubungi administrator.');
        }

        $unitKerjas = UnitKerja::active()->orderBy('nama')->get();

        return view('user.tte.registration.create', compact('user', 'emailAccount', 'unitKerjas'));
    }

    public function store(Request $request)
    {
        // Check if user NIP exists in Master Data Email (email_accounts)
        $emailAccount = EmailAccount::where('requester_nip', auth()->user()->nip)
            ->where('suspended', 0)
            ->first();

        if (!$emailAccount) {
            return back()->with('error', 'NIP Anda belum terdaftar di Master Data Email atau email dalam status suspended. Silakan hubungi administrator.');
        }

        $validated = $request->validate([
            'instansi' => 'required|string|max:255',
            'jabatan' => 'required|string|max:255',
            'no_hp' => 'required|string|max:20',
        ]);

        $user = auth()->user();

        TteRegistrationRequest::create([
            'user_id' => $user->id,
            'nama' => $user->name,
            'nip' => $user->nip,
            'email_resmi' => $emailAccount->email,
            'instansi' => $validated['instansi'],
            'jabatan' => $validated['jabatan'],
            'no_hp' => $validated['no_hp'],
            'status' => 'menunggu',
        ]);

        return redirect()->route('user.tte.registration.index')
            ->with('success', 'Permohonan pendaftaran akun TTE berhasil diajukan!');
    }

    public function show(TteRegistrationRequest $tteRegistration)
    {
        $this->authorize('view', $tteRegistration);
        return view('user.tte.registration.show', compact('tteRegistration'));
    }
}
