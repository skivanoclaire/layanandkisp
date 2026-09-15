<?php

namespace App\Http\Controllers\Admin\Dtsen;

use App\Http\Controllers\Controller;
use App\Models\DtsenRelease;
use App\Models\DtsenVariable;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Master Data - Katalog variabel/indikator DTSEN (dikelola Bapperida).
 */
class VariableController extends Controller
{
    public function index(Request $request)
    {
        $query = DtsenVariable::with('release')->ordered();

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }
        if ($request->filled('level')) {
            $query->where('level_minimal', $request->level);
        }
        if ($request->filled('release')) {
            $query->where('dtsen_release_id', $request->release);
        }
        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->where(fn ($sub) => $sub->where('kode', 'like', "%{$q}%")->orWhere('nama', 'like', "%{$q}%"));
        }

        $items = $query->paginate(50)->withQueryString();
        $kategoriList = DtsenVariable::whereNotNull('kategori')->distinct()->orderBy('kategori')->pluck('kategori');
        $releases = DtsenRelease::orderByDesc('tanggal_rilis')->get();

        return view('admin.dtsen.variables.index', compact('items', 'kategoriList', 'releases'));
    }

    public function create()
    {
        $releases = DtsenRelease::orderByDesc('tanggal_rilis')->get();

        return view('admin.dtsen.variables.create', compact('releases'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        DtsenVariable::create($data);

        return redirect()->route('admin.dtsen.variables.index')->with('status', 'Variabel DTSEN ditambahkan.');
    }

    public function edit($id)
    {
        $item = DtsenVariable::findOrFail($id);
        $releases = DtsenRelease::orderByDesc('tanggal_rilis')->get();

        return view('admin.dtsen.variables.edit', compact('item', 'releases'));
    }

    public function update(Request $request, $id)
    {
        $item = DtsenVariable::findOrFail($id);
        $item->update($this->validateData($request, $item->id));

        return redirect()->route('admin.dtsen.variables.index')->with('status', 'Variabel DTSEN diperbarui.');
    }

    public function destroy($id)
    {
        $item = DtsenVariable::withCount('requestVariables')->findOrFail($id);

        // Variabel yang sudah pernah dimohonkan tidak dihapus agar jejak permohonan
        // lama tetap utuh — cukup dinonaktifkan.
        // Nama atribut withCount adalah snake_case dari nama relasi.
        if ($item->request_variables_count > 0) {
            $item->update(['is_active' => false]);

            return back()->with('status', 'Variabel sudah pernah dimohonkan, sehingga dinonaktifkan (bukan dihapus).');
        }

        $item->delete();

        return back()->with('status', 'Variabel DTSEN dihapus.');
    }

    public function toggle($id)
    {
        $item = DtsenVariable::findOrFail($id);
        $item->is_active = ! $item->is_active;
        $item->save();

        return back()->with('status', 'Status variabel diperbarui.');
    }

    private function validateData(Request $request, ?int $ignoreId = null): array
    {
        $validated = $request->validate([
            'kode' => ['required', 'string', 'max:50', Rule::unique('dtsen_variables', 'kode')->ignore($ignoreId)],
            'nama' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'kategori' => ['nullable', 'string', 'max:100'],
            'satuan' => ['nullable', 'string', 'max:50'],
            'level_minimal' => ['required', 'integer', 'min:1', 'max:4'],
            'dtsen_release_id' => ['nullable', 'exists:dtsen_releases,id'],
            'is_active' => ['nullable', 'boolean'],
            'urutan' => ['nullable', 'integer', 'min:0'],
        ]);

        // Checkbox yang tidak dicentang tidak ikut terkirim, jadi nilainya
        // ditentukan eksplisit agar penonaktifan lewat form ikut tersimpan.
        $validated['is_active'] = $request->boolean('is_active');
        $validated['urutan'] = $validated['urutan'] ?? 0;

        return $validated;
    }
}
