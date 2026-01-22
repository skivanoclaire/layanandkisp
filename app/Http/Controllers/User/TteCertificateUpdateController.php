<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\TteCertificateUpdateRequest;
use App\Models\EmailAccount;
use App\Models\UnitKerja;
use Illuminate\Http\Request;

class TteCertificateUpdateController extends Controller
{
    public function index()
    {
        $requests = TteCertificateUpdateRequest::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('user.tte.certificate-update.index', compact('requests'));
    }

    public function create()
    {
        $user = auth()->user();

        // Check if user NIP exists in Master Data Email (email_accounts)
        $emailAccount = EmailAccount::where('requester_nip', $user->nip)
            ->where('suspended', 0)
            ->first();

        if (!$emailAccount) {
            return redirect()->route('user.tte.certificate-update.index')
                ->with('error', 'NIP Anda belum terdaftar di Master Data Email atau email dalam status suspended. Silakan hubungi administrator.');
        }

        $unitKerjas = UnitKerja::forLayananDigital()->active()->orderBy('nama')->get();

        return view('user.tte.certificate-update.create', compact('user', 'emailAccount', 'unitKerjas'));
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

        TteCertificateUpdateRequest::create([
            'user_id' => $user->id,
            'nama' => $user->name,
            'nip' => $user->nip,
            'email_resmi' => $emailAccount->email,
            'instansi' => $validated['instansi'],
            'jabatan' => $validated['jabatan'],
            'no_hp' => $validated['no_hp'],
            'status' => 'menunggu',
        ]);

        return redirect()->route('user.tte.certificate-update.index')
            ->with('success', 'Permohonan pembaruan sertifikat TTE berhasil diajukan!');
    }

    public function show(TteCertificateUpdateRequest $tteCertificateUpdate)
    {
        $this->authorize('view', $tteCertificateUpdate);
        return view('user.tte.certificate-update.show', compact('tteCertificateUpdate'));
    }
}
