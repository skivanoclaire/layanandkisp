<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\VpnRegistration;
use App\Models\UnitKerja;
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
        $unitKerjas = UnitKerja::orderBy('nama')->get();
        return view('user.vpn.registration.create', compact('unitKerjas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'nip' => 'required|string|max:255',
            'unit_kerja_id' => 'required|exists:unit_kerjas,id',
            'uraian_kebutuhan' => 'required|string',
            'tipe' => 'required|in:VPN PPTP,VPN IPSec/L2TP,SDWAN,Metro-E',
            'bandwidth' => 'nullable|string|max:255',
        ]);

        $validated['user_id'] = Auth::id();
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
