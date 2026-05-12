<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\VpnRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VpnRegistrationController extends Controller
{
    public function index()
    {
        $requests = VpnRegistration::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('user.vpn.registration.index', compact('requests'));
    }

    public function create()
    {
        return view('user.vpn.registration.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'uraian_kebutuhan' => 'required|string',
            'tipe' => 'required|in:VPN PPTP,VPN IPSec/L2TP,SDWAN,Metro-E',
            'bandwidth' => 'nullable|string|max:255',
        ]);

        $user = Auth::user();
        $validated['nama'] = $user->name;
        $validated['nip'] = $user->nik;
        $validated['unit_kerja_id'] = $user->unit_kerja_id;
        $validated['is_kabupaten_kota'] = false;
        $validated['kabupaten_kota'] = null;
        $validated['unit_kerja_manual'] = null;
        $validated['user_id'] = $user->id;
        $validated['status'] = 'menunggu';

        VpnRegistration::create($validated);

        return redirect()->route('user.vpn.registration.index')
            ->with('success', 'Permohonan pendaftaran VPN berhasil diajukan.');
    }

    public function show(VpnRegistration $vpnRegistration)
    {
        if ($vpnRegistration->user_id !== Auth::id()) {
            abort(403);
        }

        return view('user.vpn.registration.show', compact('vpnRegistration'));
    }
}
