<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\VpnReset;
use App\Models\UnitKerja;
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
        $unitKerjas = UnitKerja::forLayananDigital()->orderBy('nama')->get();
        return view('user.vpn.reset.create', compact('unitKerjas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'nip' => 'required|string|max:255',
            'is_kabupaten_kota' => 'required|boolean',
            'kabupaten_kota' => 'nullable|in:Bulungan,Malinau,Tana Tidung,Tarakan,Nunukan',
            'unit_kerja_manual' => 'nullable|string|max:255',
            'unit_kerja_id' => 'nullable|exists:unit_kerjas,id',
            'username_vpn_lama' => 'nullable|string|max:255',
            'alasan' => 'required|string',
        ]);

        if ($validated['is_kabupaten_kota']) {
            $request->validate([
                'kabupaten_kota' => 'required|in:Bulungan,Malinau,Tana Tidung,Tarakan,Nunukan',
                'unit_kerja_manual' => 'required|string|max:255',
            ]);
            $validated['unit_kerja_id'] = null;
        } else {
            $request->validate([
                'unit_kerja_id' => 'required|exists:unit_kerjas,id',
            ]);
            $validated['kabupaten_kota'] = null;
            $validated['unit_kerja_manual'] = null;
        }

        $validated['user_id'] = Auth::id();
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
