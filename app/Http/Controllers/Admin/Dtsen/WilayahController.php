<?php

namespace App\Http\Controllers\Admin\Dtsen;

use App\Http\Controllers\Controller;
use App\Models\DtsenWilayah;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Master Data - Wilayah berjenjang untuk cakupan permintaan data DTSEN.
 */
class WilayahController extends Controller
{
    public function index(Request $request)
    {
        // Navigasi berjenjang: tanpa parent menampilkan tingkat provinsi.
        $parent = $request->filled('parent')
            ? DtsenWilayah::with('parent.parent')->findOrFail($request->input('parent'))
            : null;

        $items = DtsenWilayah::withCount('children')
            ->when($parent, fn ($q) => $q->where('parent_id', $parent->id))
            ->when(! $parent, fn ($q) => $q->whereNull('parent_id'))
            ->orderBy('nama')
            ->get();

        return view('admin.dtsen.wilayah.index', compact('items', 'parent'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        DtsenWilayah::create($data);

        return back()->with('status', 'Wilayah ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $item = DtsenWilayah::findOrFail($id);
        $item->update($this->validateData($request, $item->id));

        return back()->with('status', 'Wilayah diperbarui.');
    }

    public function destroy($id)
    {
        $item = DtsenWilayah::withCount('children')->findOrFail($id);

        if ($item->children_count > 0) {
            return back()->withErrors(['wilayah' => 'Hapus atau pindahkan wilayah di bawahnya terlebih dahulu.']);
        }

        $item->delete();

        return back()->with('status', 'Wilayah dihapus.');
    }

    private function validateData(Request $request, ?int $ignoreId = null): array
    {
        $validated = $request->validate([
            'parent_id' => ['nullable', 'exists:dtsen_wilayahs,id'],
            'tingkat' => ['required', Rule::in(DtsenWilayah::TINGKAT)],
            'kode' => ['nullable', 'string', 'max:20'],
            'nama' => ['required', 'string', 'max:150'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        // Cegah wilayah menjadi induk bagi dirinya sendiri.
        if ($ignoreId && (int) ($validated['parent_id'] ?? 0) === $ignoreId) {
            $validated['parent_id'] = null;
        }

        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }
}
