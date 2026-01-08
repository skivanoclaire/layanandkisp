<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\TtePassphraseResetRequest;
use App\Models\EmailAccount;
use Illuminate\Http\Request;

class TtePassphraseResetController extends Controller
{
    public function index()
    {
        $requests = TtePassphraseResetRequest::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('user.tte.passphrase-reset.index', compact('requests'));
    }

    public function create()
    {
        $user = auth()->user();

        // Check if user NIP exists in Master Data Email (email_accounts)
        $emailAccount = EmailAccount::where('requester_nip', $user->nip)
            ->where('suspended', 0)
            ->first();

        if (!$emailAccount) {
            return redirect()->route('user.tte.passphrase-reset.index')
                ->with('error', 'NIP Anda belum terdaftar di Master Data Email atau email dalam status suspended. Silakan hubungi administrator.');
        }

        // Get active unit kerjas for dropdown
        $unitKerjas = \App\Models\UnitKerja::where('is_active', true)->orderBy('nama')->get();

        return view('user.tte.passphrase-reset.create', compact('user', 'emailAccount', 'unitKerjas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'instansi' => 'required|string|max:255',
            'jabatan' => 'required|string|max:255',
        ], [
            'instansi.required' => 'Instansi wajib dipilih.',
            'jabatan.required' => 'Jabatan wajib diisi.',
        ]);

        // Check if user NIP exists in Master Data Email (email_accounts)
        $emailAccount = EmailAccount::where('requester_nip', auth()->user()->nip)
            ->where('suspended', 0)
            ->first();

        if (!$emailAccount) {
            return back()->with('error', 'NIP Anda belum terdaftar di Master Data Email atau email dalam status suspended. Silakan hubungi administrator.');
        }

        $user = auth()->user();

        TtePassphraseResetRequest::create([
            'user_id' => $user->id,
            'nama' => $user->name,
            'nip' => $user->nip,
            'email_resmi' => $emailAccount->email,
            'no_hp' => $user->phone,
            'instansi' => $request->instansi,
            'jabatan' => $request->jabatan,
            'status' => 'menunggu',
        ]);

        return redirect()->route('user.tte.passphrase-reset.index')
            ->with('success', 'Permohonan reset passphrase TTE berhasil diajukan!');
    }

    public function show(TtePassphraseResetRequest $ttePassphraseReset)
    {
        $this->authorize('view', $ttePassphraseReset);
        return view('user.tte.passphrase-reset.show', compact('ttePassphraseReset'));
    }
}
