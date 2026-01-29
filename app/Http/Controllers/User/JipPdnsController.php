<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\JipPdnsRequest;
use App\Models\UnitKerja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JipPdnsController extends Controller
{
    public function index()
    {
        $requests = JipPdnsRequest::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('user.vpn.jip-pdns.index', compact('requests'));
    }

    public function create()
    {
        $unitKerjas = UnitKerja::forLayananDigital()->orderBy('nama')->get();
        return view('user.vpn.jip-pdns.create', compact('unitKerjas'));
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
            'uraian_permohonan' => 'required|string',
            'keterangan' => 'nullable|string',
        ]);

        // Validate conditional fields
        if ($validated['is_kabupaten_kota']) {
            $request->validate([
                'kabupaten_kota' => 'required|in:Bulungan,Malinau,Tana Tidung,Tarakan,Nunukan',
                'unit_kerja_manual' => 'required|string|max:255',
            ]);
        } else {
            $request->validate([
                'unit_kerja_id' => 'required|exists:unit_kerjas,id',
            ]);
        }

        $validated['user_id'] = Auth::id();
        $validated['status'] = 'menunggu';

        JipPdnsRequest::create($validated);

        return redirect()->route('user.vpn.jip-pdns.index')
            ->with('success', 'Permohonan akses JIP PDNS berhasil diajukan.');
    }

    public function show(JipPdnsRequest $jipPdnsRequest)
    {
        if ($jipPdnsRequest->user_id !== Auth::id()) {
            abort(403);
        }

        return view('user.vpn.jip-pdns.show', compact('jipPdnsRequest'));
    }
}
