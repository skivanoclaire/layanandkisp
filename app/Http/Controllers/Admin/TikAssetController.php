<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TikAsset;
use App\Models\TikCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\DB;

class TikAssetController extends Controller
{
    public function index(Request $r) {
        $q = TikAsset::with('category')->orderBy('name');
        if ($r->filled('category')) $q->where('tik_category_id', $r->category);
        if ($r->filled('active'))   $q->where('is_active', (bool)$r->active);
        $items = $q->paginate(25)->withQueryString();
        $cats  = TikCategory::orderBy('name')->get();
        return view('admin.tik.assets.index', compact('items','cats'));
    }

    public function create() {
        $cats = TikCategory::orderBy('name')->get();
        return view('admin.tik.assets.create', compact('cats'));
    }

    public function store(Request $r) {
        $data = $r->validate([
            'tik_category_id' => 'required|exists:tik_categories,id',
            'name'            => 'required|string|max:150',
            'serial_number'   => 'nullable|string|max:100',
            'quantity'        => 'required|integer|min:1',
            'condition'       => 'nullable|string|max:50',
            'location'        => 'nullable|string|max:100',
            'photo'           => 'nullable|image|max:2048',
            'notes'           => 'nullable|string',
            'is_active'       => 'sometimes|boolean',
        ]);

            // === Generate Kode Otomatis ===
        $category = TikCategory::findOrFail($data['tik_category_id']);
        if (empty($category->code)) {
            return back()->withErrors(['tik_category_id' => 'Kategori belum punya Kode Kategori. Edit kategori & isi kolom kode.'])->withInput();
        }
        $prefix = strtoupper(trim($category->code));
        $len    = strlen($prefix);

         DB::transaction(function () use (&$data, $prefix, $len) {
        // Ambil nomor urut terbesar per kategori (mengunci baris untuk hindari duplikasi)
        $maxRow = TikAsset::where('code','like', $prefix.'%')
            ->selectRaw('CAST(SUBSTRING(code, '.($len+1).') AS UNSIGNED) AS seq')
            ->orderByDesc('seq')
            ->lockForUpdate()
            ->first();

        $next = ($maxRow->seq ?? 0) + 1;
        $data['code'] = $prefix . str_pad((string)$next, 3, '0', STR_PAD_LEFT);
        });


        if ($r->hasFile('photo')) {
            $data['photo_path'] = $r->file('photo')->store('tik_assets', 'public');
        }
        $data['is_active'] = $r->boolean('is_active', true);

        // 1) simpan dulu untuk dapatkan $asset->id
        $asset = TikAsset::create($data);

        // 2) siapkan payload QR (teks mudah dipindai; aman walau offline)
        $payload = "ASSET|{$asset->id}|CODE:".($asset->code ?? '-')."|SN:".($asset->serial_number ?? '-')."|NamaBarang:{$asset->name}"."|Kondisi:{$asset->condition}";

        // 3) generate PNG & simpan ke storage publik
        $png  = QrCode::format('png')->errorCorrection('M')->margin(1)->size(480)->generate($payload);
        $path = "qr/asset-{$asset->id}.png";
        Storage::disk('public')->put($path, $png);

        // 4) simpan metadata QR
        $asset->update([
            'qr_text' => $payload,
            'qr_path' => $path,
        ]);

        return redirect()->route('admin.tik.assets.index')->with('status','Aset ditambahkan & QR dibuat.');
    }

    public function edit(TikAsset $asset) {
        $cats = TikCategory::orderBy('name')->get();
        return view('admin.tik.assets.edit', compact('asset','cats'));
    }

    public function update(Request $r, TikAsset $asset) {
        $data = $r->validate([
            'tik_category_id' => 'required|exists:tik_categories,id',
            'name'            => 'required|string|max:150',
            'code'            => 'nullable|string|max:50|unique:tik_assets,code,'.$asset->id,
            'serial_number'   => 'nullable|string|max:100',
            'quantity'        => 'required|integer|min:1',
            'condition'       => 'nullable|string|max:50',
            'location'        => 'nullable|string|max:100',
            'photo'           => 'nullable|image|max:2048',
            'notes'           => 'nullable|string',
            'is_active'       => 'sometimes|boolean',
        ]);

        if ($r->hasFile('photo')) {
            if ($asset->photo_path) Storage::disk('public')->delete($asset->photo_path);
            $data['photo_path'] = $r->file('photo')->store('tik_assets', 'public');
        }
        $data['is_active'] = $r->boolean('is_active', true);

        // track perubahan untuk tentukan perlu regen QR atau tidak
        $asset->fill($data);
        $dirtyForQr = $asset->isDirty(['name','code','serial_number','condition']); // field yang memengaruhi QR

        $asset->save();

        if ($dirtyForQr) {
            $payload = "ASSET|{$asset->id}|CODE:".($asset->code ?? '-')."|SN:".($asset->serial_number ?? '-')."|NamaBarang:{$asset->name}"."|Kondisi:{$asset->condition}";
            $png     = QrCode::format('png')->errorCorrection('M')->margin(1)->size(480)->generate($payload);
            $path    = $asset->qr_path ?: "qr/asset-{$asset->id}.png";
            Storage::disk('public')->put($path, $png);

            $asset->forceFill(['qr_text' => $payload, 'qr_path' => $path])->save();
        }

        return redirect()->route('admin.tik.assets.index')->with('status','Aset diperbarui.'.($dirtyForQr?' (QR diperbarui)':'')); 
    }

    public function destroy(TikAsset $asset) {
        if ($asset->photo_path) Storage::disk('public')->delete($asset->photo_path);
        if ($asset->qr_path)    Storage::disk('public')->delete($asset->qr_path);
        $asset->delete();
        return back()->with('status','Aset dihapus.');
    }
}
