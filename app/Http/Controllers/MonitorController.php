<?php

namespace App\Http\Controllers;

use App\Models\WebMonitor;
use Illuminate\Http\Request;

class MonitorController extends Controller
{
    public function monitorweb()
    {
        $data = WebMonitor::all();
        return view('admin.monitorweb', compact('data'));
    }

    public function monitorwebCreate()
{
    return view('admin.createmonitorweb');
}


    public function monitorwebStore(Request $request)
    {
        $validated = $request->validate([
            'nama_instansi' => 'required',
            'subdomain' => 'required',
            'status' => 'required|in:Aktif,Tidak Aktif',
            'keterangan' => 'nullable',
            'jenis' => 'required|in:Induk,Cabang',
        ]);

        WebMonitor::create($validated);
        return redirect()->route('admin.monitorweb')->with('success', 'Data berhasil ditambahkan!');
    }

    public function monitorwebEdit($id)
    {
        $formData = WebMonitor::findOrFail($id);
        return view('admin.editmonitorweb', compact('formData'));
    }      

    public function monitorwebUpdate(Request $request, $id)
    {
        $validated = $request->validate([
            'nama_instansi' => 'required',
            'subdomain' => 'required',
            'status' => 'required|in:Aktif,Tidak Aktif',
            'keterangan' => 'nullable',
            'jenis' => 'required|in:Induk,Cabang',
        ]);

        $item = WebMonitor::findOrFail($id);
        $item->update($validated);

        return redirect()->route('admin.monitorweb')->with('success', 'Data berhasil diupdate!');
    }

    public function monitorwebDelete($id)
    {
        $item = WebMonitor::findOrFail($id);
        $item->delete();

        return redirect()->route('admin.monitorweb')->with('success', 'Data berhasil dihapus!');
    }
}
