<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UnitKerja;
use App\Models\UnitOrganisasi;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UnitOrganisasiController extends Controller
{
    // GET /admin/unit-organisasi
    public function index(Request $r)
    {
        $items = UnitOrganisasi::with('unitKerja')
            ->when($r->filled('unit_kerja_id'), fn ($q) => $q->where('unit_kerja_id', $r->unit_kerja_id))
            ->orderBy('nama')
            ->paginate(50)
            ->withQueryString();

        $unitKerjaList = UnitKerja::forLayananDigital()->orderBy('nama')->get();
        $tipeOptions = UnitOrganisasi::tipeOptions();

        return view('admin.unit-organisasi.index', compact('items', 'unitKerjaList', 'tipeOptions'));
    }

    // POST /admin/unit-organisasi
    public function store(Request $r)
    {
        $data = $this->validated($r);
        UnitOrganisasi::create($data);

        return back()->with('status', 'Unit organisasi berhasil ditambahkan.');
    }

    // PUT /admin/unit-organisasi/{unitOrganisasi}
    public function update(Request $r, UnitOrganisasi $unitOrganisasi)
    {
        $data = $this->validated($r);
        $unitOrganisasi->update($data);

        return back()->with('status', 'Unit organisasi berhasil diperbarui.');
    }

    // POST /admin/unit-organisasi/{unitOrganisasi}/toggle
    public function toggle(UnitOrganisasi $unitOrganisasi)
    {
        $unitOrganisasi->update(['is_active' => !$unitOrganisasi->is_active]);

        return back()->with('status', 'Status unit organisasi diubah.');
    }

    private function validated(Request $r): array
    {
        $data = $r->validate([
            'nama'          => ['required', 'string', 'max:255'],
            'tipe'          => ['required', Rule::in(UnitOrganisasi::tipeOptions())],
            'unit_kerja_id' => ['required', Rule::exists('unit_kerjas', 'id')],
            'is_active'     => ['nullable', 'boolean'],
        ], [
            'unit_kerja_id.required' => 'Perangkat daerah induk wajib dipilih.',
        ]);

        $data['is_active'] = $r->boolean('is_active');

        return $data;
    }
}
