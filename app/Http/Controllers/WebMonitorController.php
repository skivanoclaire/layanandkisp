<?php

namespace App\Http\Controllers;

use App\Models\WebMonitor;
use Illuminate\Http\Request;

class WebMonitorController extends Controller
{
    public function index()
    {
        $data = WebMonitor::all();
        return view('web-monitor.index', compact('data'));
    }

    public function create()
    {
        return view('web-monitor.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_instansi' => 'required',
            'subdomain' => 'nullable',
            'domain' => 'nullable',
            'status' => 'required|in:Aktif,Tidak Aktif',
            'keterangan' => 'nullable',
        ]);

        WebMonitor::create($request->all());
        return redirect()->route('web-monitor.index')->with('success', 'Data berhasil ditambahkan');
    }

    public function edit(WebMonitor $webMonitor)
    {
        return view('web-monitor.edit', compact('webMonitor'));
    }

    public function update(Request $request, WebMonitor $webMonitor)
    {
        $request->validate([
            'nama_instansi' => 'required',
            'subdomain' => 'nullable',
            'domain' => 'nullable',
            'status' => 'required|in:Aktif,Tidak Aktif',
            'keterangan' => 'nullable',
        ]);

        $webMonitor->update($request->all());
        return redirect()->route('web-monitor.index')->with('success', 'Data berhasil diperbarui');
    }

    public function destroy(WebMonitor $webMonitor)
    {
        $webMonitor->delete();
        return redirect()->route('web-monitor.index')->with('success', 'Data berhasil dihapus');
    }
}
