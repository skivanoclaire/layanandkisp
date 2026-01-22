<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\TteAssistanceRequest;
use App\Models\EmailRequest;
use App\Models\EmailAccount;
use App\Models\UnitKerja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TteAssistanceController extends Controller
{
    public function index()
    {
        $requests = TteAssistanceRequest::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('user.tte.assistance.index', compact('requests'));
    }

    public function create()
    {
        $user = auth()->user();

        // Check if user NIP exists in Master Data Email (email_accounts)
        $emailAccount = EmailAccount::where('requester_nip', $user->nip)
            ->where('suspended', 0)
            ->first();

        if (!$emailAccount) {
            return redirect()->route('user.tte.assistance.index')
                ->with('error', 'NIP Anda belum terdaftar di Master Data Email atau email dalam status suspended. Silakan hubungi administrator.');
        }

        $unitKerjas = UnitKerja::forLayananDigital()->active()->orderBy('nama')->get();

        return view('user.tte.assistance.create', compact('user', 'emailAccount', 'unitKerjas'));
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
            'waktu_pendampingan' => 'required|date|after:now',
            'surat_permohonan' => 'required|file|mimes:pdf|max:2048',
        ]);

        $user = auth()->user();

        // Upload surat permohonan
        $suratPath = $request->file('surat_permohonan')->store('tte/assistance/surat', 'public');

        TteAssistanceRequest::create([
            'user_id' => $user->id,
            'nama' => $user->name,
            'nip' => $user->nip,
            'email_resmi' => $emailAccount->email,
            'instansi' => $validated['instansi'],
            'jabatan' => $validated['jabatan'],
            'no_hp' => $validated['no_hp'],
            'waktu_pendampingan' => $validated['waktu_pendampingan'],
            'surat_permohonan_path' => $suratPath,
            'status' => 'menunggu',
        ]);

        return redirect()->route('user.tte.assistance.index')
            ->with('success', 'Permohonan pendampingan TTE berhasil diajukan!');
    }

    public function show(TteAssistanceRequest $tteAssistance)
    {
        $this->authorize('view', $tteAssistance);
        return view('user.tte.assistance.show', compact('tteAssistance'));
    }
}
