<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\JipPdnsRequest;
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
        return view('user.vpn.jip-pdns.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'uraian_permohonan' => 'required|string',
            'keterangan' => 'nullable|string',
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
