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

        try {
            // === Generate Kode Otomatis ===
            $category = TikCategory::findOrFail($data['tik_category_id']);

            // Auto-generate category code if missing
            if (empty($category->code)) {
                $category->code = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $category->name), 0, 3));
                if (strlen($category->code) < 2) {
                    $category->code = 'CAT';
                }

                // Ensure uniqueness
                $baseCode = $category->code;
                $counter = 1;
                while (TikCategory::where('code', $category->code)->where('id', '!=', $category->id)->exists()) {
                    $category->code = $baseCode . $counter;
                    $counter++;
                }

                $category->save();
                \Log::info('Auto-generated category code', ['category' => $category->name, 'code' => $category->code]);
            }

            $prefix = strtoupper(trim($category->code));
            $len    = strlen($prefix);

            // Wrap everything in a single transaction for atomicity
            $asset = DB::transaction(function () use (&$data, $prefix, $len, $r) {
                // Generate code with row lock to prevent race conditions
                $maxRow = TikAsset::where('code','like', $prefix.'%')
                    ->selectRaw('CAST(SUBSTRING(code, '.($len+1).') AS UNSIGNED) AS seq')
                    ->orderByDesc('seq')
                    ->lockForUpdate()
                    ->first();

                $next = ($maxRow->seq ?? 0) + 1;
                $data['code'] = $prefix . str_pad((string)$next, 3, '0', STR_PAD_LEFT);

                // Handle file upload inside transaction
                if ($r->hasFile('photo')) {
                    try {
                        $data['photo_path'] = $r->file('photo')->store('tik_assets', 'public');
                    } catch (\Exception $e) {
                        \Log::error('Photo upload failed', ['error' => $e->getMessage()]);
                        throw new \Exception('Gagal upload foto: ' . $e->getMessage());
                    }
                }

                $data['is_active'] = $r->boolean('is_active', true);

                // Create asset record
                $asset = TikAsset::create($data);

                // Generate QR code inside transaction
                try {
                    // Compact JSON payload - only ID and code needed for scanning
                    $payload = json_encode([
                        'id' => $asset->id,
                        'code' => $asset->code,
                    ]);

                    $png  = QrCode::format('png')->errorCorrection('M')->margin(1)->size(480)->generate($payload);
                    $path = "qr/asset-{$asset->id}.png";
                    Storage::disk('public')->put($path, $png);

                    // Update with QR metadata
                    $asset->qr_text = $payload;
                    $asset->qr_path = $path;
                    $asset->save();
                } catch (\Exception $e) {
                    \Log::warning('QR code generation failed (asset created successfully)', [
                        'asset_id' => $asset->id,
                        'error' => $e->getMessage()
                    ]);
                    // Continue - asset is created even if QR fails
                }

                return $asset;
            });

            return redirect()->route('admin.tik.assets.index')->with('status','Aset ditambahkan & QR dibuat.');

        } catch (\Exception $e) {
            \Log::error('TikAsset creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $r->except(['photo']),
                'user_id' => auth()->id(),
            ]);

            return back()
                ->withErrors(['error' => 'Gagal menyimpan data: ' . $e->getMessage()])
                ->withInput();
        }
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

        try {
            // Wrap update in transaction for atomicity
            DB::transaction(function () use ($r, $asset, &$data) {
                // Handle file upload
                if ($r->hasFile('photo')) {
                    try {
                        if ($asset->photo_path) {
                            Storage::disk('public')->delete($asset->photo_path);
                        }
                        $data['photo_path'] = $r->file('photo')->store('tik_assets', 'public');
                    } catch (\Exception $e) {
                        \Log::error('Photo update failed', ['asset_id' => $asset->id, 'error' => $e->getMessage()]);
                        throw new \Exception('Gagal upload foto: ' . $e->getMessage());
                    }
                }

                $data['is_active'] = $r->boolean('is_active', true);

                // Track changes to determine if QR needs regeneration
                $asset->fill($data);
                $dirtyForQr = $asset->isDirty(['name','code','serial_number','condition']);

                $asset->save();

                // Regenerate QR code if relevant fields changed
                if ($dirtyForQr) {
                    try {
                        // Compact JSON payload - only ID and code needed for scanning
                        $payload = json_encode([
                            'id' => $asset->id,
                            'code' => $asset->code,
                        ]);

                        $png     = QrCode::format('png')->errorCorrection('M')->margin(1)->size(480)->generate($payload);
                        $path    = $asset->qr_path ?: "qr/asset-{$asset->id}.png";
                        Storage::disk('public')->put($path, $png);

                        $asset->forceFill(['qr_text' => $payload, 'qr_path' => $path])->save();
                    } catch (\Exception $e) {
                        \Log::warning('QR code regeneration failed (asset updated successfully)', [
                            'asset_id' => $asset->id,
                            'error' => $e->getMessage()
                        ]);
                        // Continue - asset is updated even if QR regeneration fails
                    }
                }
            });

            $qrStatus = $asset->isDirty(['qr_text', 'qr_path']) ? ' (QR diperbarui)' : '';
            return redirect()->route('admin.tik.assets.index')->with('status','Aset diperbarui.'.$qrStatus);

        } catch (\Exception $e) {
            \Log::error('TikAsset update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'asset_id' => $asset->id,
                'data' => $r->except(['photo']),
                'user_id' => auth()->id(),
            ]);

            return back()
                ->withErrors(['error' => 'Gagal memperbarui data: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function destroy(TikAsset $asset) {
        if ($asset->photo_path) Storage::disk('public')->delete($asset->photo_path);
        if ($asset->qr_path)    Storage::disk('public')->delete($asset->qr_path);
        $asset->delete();
        return back()->with('status','Aset dihapus.');
    }
}
