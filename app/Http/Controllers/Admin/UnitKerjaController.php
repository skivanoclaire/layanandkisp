<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UnitKerja;
use Illuminate\Http\Request;

class UnitKerjaController extends Controller
{
    public function index()
    {
        $unitKerjas = UnitKerja::orderBy('tipe')->orderBy('nama')->get();
        return view('admin.unit-kerja.index', compact('unitKerjas'));
    }

    public function create()
    {
        $tipeOptions = UnitKerja::tipeOptions();
        return view('admin.unit-kerja.create', compact('tipeOptions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255|unique:unit_kerjas,nama',
            'tipe' => 'required|in:' . implode(',', UnitKerja::tipeOptions()),
        ]);

        UnitKerja::create([
            'nama' => $request->nama,
            'tipe' => $request->tipe,
            'is_active' => true,
        ]);

        return redirect()->route('admin.unit-kerja.index')
            ->with('success', 'Instansi berhasil ditambahkan');
    }

    public function edit(UnitKerja $unitKerja)
    {
        $tipeOptions = UnitKerja::tipeOptions();
        return view('admin.unit-kerja.edit', compact('unitKerja', 'tipeOptions'));
    }

    public function update(Request $request, UnitKerja $unitKerja)
    {
        $request->validate([
            'nama' => 'required|string|max:255|unique:unit_kerjas,nama,' . $unitKerja->id,
            'tipe' => 'required|in:' . implode(',', UnitKerja::tipeOptions()),
            'is_active' => 'boolean',
        ]);

        $unitKerja->update([
            'nama' => $request->nama,
            'tipe' => $request->tipe,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.unit-kerja.index')
            ->with('success', 'Instansi berhasil diperbarui');
    }

    public function destroy(UnitKerja $unitKerja)
    {
        $unitKerja->delete();

        return redirect()->route('admin.unit-kerja.index')
            ->with('success', 'Instansi berhasil dihapus');
    }
}
