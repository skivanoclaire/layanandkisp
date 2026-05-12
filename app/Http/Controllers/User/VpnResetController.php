<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\VpnReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VpnResetController extends Controller
{
    public function index()
    {
        $requests = VpnReset::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('user.vpn.reset.index', compact('requests'));
    }

    public function create()
    {
        return view('user.vpn.reset.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'username_vpn_lama' => 'nullable|string|max:255',
            'alasan' => 'required|string',
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

        VpnReset::create($validated);

        return redirect()->route('user.vpn.reset.index')
            ->with('success', 'Permohonan reset akun VPN berhasil diajukan.');
    }

    public function show(VpnReset $vpnReset)
    {
        if ($vpnReset->user_id !== Auth::id()) {
            abort(403);
        }

        return view('user.vpn.reset.show', compact('vpnReset'));
    }
}
