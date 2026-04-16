<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UnitKerja;
use Illuminate\Http\Request;

class UnitKerjaController extends Controller
{
    public function index(Request $request)
    {
        $tipeOptions = UnitKerja::tipeOptions();
        $selectedTipes = $request->input('tipe', []);

        $query = UnitKerja::orderBy('tipe')->orderBy('nama');

        if (!empty($selectedTipes)) {
            $query->whereIn('tipe', $selectedTipes);
        }

        $unitKerjas = $query->get();

        return view('admin.unit-kerja.index', compact('unitKerjas', 'tipeOptions', 'selectedTipes'));
    }

    public function exportPdf(Request $request)
    {
        $selectedTipes = $request->input('tipe', []);

        $query = UnitKerja::orderBy('tipe')->orderBy('nama');

        if (!empty($selectedTipes)) {
            $query->whereIn('tipe', $selectedTipes);
        }

        $data = $query->get();

        $filterLabels = [];
        if (!empty($selectedTipes)) {
            $filterLabels['Tipe'] = implode(', ', $selectedTipes);
        }

        $options = new \Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Arial');

        $dompdf = new \Dompdf\Dompdf($options);
        $html = view('admin.unit-kerja.export-pdf', [
            'data' => $data,
            'filterLabels' => $filterLabels,
            'tanggal' => now()->locale('id')->isoFormat('D MMMM YYYY, HH:mm') . ' WITA',
        ])->render();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'Master-Data-Instansi-' . now()->format('Ymd-His') . '.pdf';

        return $dompdf->stream($filename, ['Attachment' => false]);
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
