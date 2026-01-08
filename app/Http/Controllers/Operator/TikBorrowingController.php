<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\TikAsset;
use App\Models\TikBorrowing;
use App\Models\TikBorrowingItem;
use App\Models\TikBorrowingPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class TikBorrowingController extends Controller
{
    public function index(Request $r)
    {
        $items = TikBorrowing::with('items.asset')
            ->where('operator_id', auth()->id())
            ->orderByDesc('created_at')
            ->paginate(20);
        return view('operator.tik.borrowings.index', compact('items'));
    }

    public function create()
    {
        // daftar aset + ketersediaan berdasarkan borrowing ongoing
        $assets = TikAsset::with('category')->orderBy('name')->get()
            ->map(function($a){
                $borrowed = DB::table('tik_borrowing_items as bi')
                    ->join('tik_borrowings as b','b.id','=','bi.tik_borrowing_id')
                    ->where('b.status','ongoing')
                    ->where('bi.tik_asset_id',$a->id)
                    ->sum('bi.qty');
                $a->available = max(0, $a->quantity - $borrowed);
                return $a;
            });
        return view('operator.tik.borrowings.create', compact('assets'));
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'notes' => 'nullable|string|max:500',
            'asset_id' => 'required|array|min:1',
            'asset_id.*' => 'required|exists:tik_assets,id',
            'qty' => 'required|array',
            'qty.*' => 'required|integer|min:1',
            'photos.*' => 'nullable|image|max:4096',
        ]);

        DB::transaction(function () use ($r, $data) {
            $borrowing = TikBorrowing::create([
                'operator_id' => auth()->id(),
                'code'        => TikBorrowing::generateCode(),
                'status'      => 'ongoing',
                'started_at'  => now(),
                'notes'       => $data['notes'] ?? null,
            ]);

            // simpan items
            foreach ($data['asset_id'] as $i => $assetId) {
                $qty = (int)$data['qty'][$i];
                TikBorrowingItem::create([
                    'tik_borrowing_id' => $borrowing->id,
                    'tik_asset_id'     => $assetId,
                    'qty'              => $qty,
                ]);
            }

            // simpan foto (checkout)
            if ($r->hasFile('photos')) {
                foreach ($r->file('photos') as $file) {
                    $path = $file->store('tik_borrowings', 'public');
                    TikBorrowingPhoto::create([
                        'tik_borrowing_id' => $borrowing->id,
                        'phase' => 'checkout',
                        'path'  => $path,
                    ]);
                }
            }
        });

        return redirect()->route('op.tik.borrow.index')->with('status','Peminjaman dibuat.');
    }

    public function show(TikBorrowing $borrowing)
    {
        $this->authorizeOwner($borrowing);
        $borrowing->load(['items.asset.category','photos']);
        return view('operator.tik.borrowings.show', compact('borrowing'));
    }

    public function returnForm(TikBorrowing $borrowing)
    {
        $this->authorizeOwner($borrowing);
        abort_unless($borrowing->status === 'ongoing', 403);
        $borrowing->load(['items.asset']);
        return view('operator.tik.borrowings.return', compact('borrowing'));
    }

    public function doReturn(Request $r, TikBorrowing $borrowing)
    {
        $this->authorizeOwner($borrowing);
        abort_unless($borrowing->status === 'ongoing', 403);

        $r->validate([
            'photos.*' => 'nullable|image|max:4096',
            'notes'    => 'nullable|string|max:500',
            'all_scanned' => 'required|in:1',  // Validate all items were scanned
        ]);

        if ($r->input('all_scanned') !== '1') {
            return back()->withErrors([
                'all_scanned' => 'Semua barang harus di-scan terlebih dahulu.'
            ])->withInput();
        }

        DB::transaction(function () use ($r, $borrowing) {
            // foto pengembalian
            if ($r->hasFile('photos')) {
                foreach ($r->file('photos') as $file) {
                    $path = $file->store('tik_borrowings', 'public');
                    \App\Models\TikBorrowingPhoto::create([
                        'tik_borrowing_id' => $borrowing->id,
                        'phase' => 'return',
                        'path'  => $path,
                    ]);
                }
            }

            $borrowing->update([
                'status'      => 'returned',
                'finished_at' => now(),
                'notes'       => $r->input('notes') ?: $borrowing->notes,
            ]);
        });

        return redirect()->route('op.tik.borrow.index')->with('status','Pengembalian dicatat.');
    }

    private function authorizeOwner(TikBorrowing $b)
    {
        if ($b->operator_id !== auth()->id()) abort(403);
    }

    /**
     * Lookup asset by code for barcode scanner
     */
    public function lookupAssetByCode(Request $request)
    {
        $code = $request->input('code');

        if (empty($code)) {
            return response()->json(['success' => false, 'message' => 'Kode tidak boleh kosong'], 400);
        }

        $asset = TikAsset::with('category')
            ->where('code', $code)
            ->where('is_active', true)
            ->first();

        if (!$asset) {
            return response()->json(['success' => false, 'message' => 'Aset tidak ditemukan'], 404);
        }

        // Calculate availability (same as create method)
        $borrowed = DB::table('tik_borrowing_items as bi')
            ->join('tik_borrowings as b', 'b.id', '=', 'bi.tik_borrowing_id')
            ->where('b.status', 'ongoing')
            ->where('bi.tik_asset_id', $asset->id)
            ->sum('bi.qty');

        $asset->available = max(0, $asset->quantity - $borrowed);

        return response()->json([
            'success' => true,
            'asset' => [
                'id' => $asset->id,
                'name' => $asset->name,
                'code' => $asset->code,
                'available' => $asset->available,
            ]
        ]);
    }

    public function edit(TikBorrowing $borrowing)
    {
        $this->authorizeOwner($borrowing);
        // opsional: jangan izinkan edit jika sudah dikembalikan
        if ($borrowing->status === 'returned') {
            abort(403, 'Peminjaman sudah selesai, tidak dapat diedit.');
        }

        // daftar aset (kalau perlu tampilkan ketersediaan juga)
        $assets = TikAsset::with('category')->orderBy('name')->get();

        $borrowing->load('items'); // untuk pre-check di form
        return view('operator.tik.borrowings.edit', compact('borrowing','assets'));
    }

    public function update(Request $r, TikBorrowing $borrowing)
    {
        $this->authorizeOwner($borrowing);
        if ($borrowing->status === 'returned') {
            abort(403, 'Peminjaman sudah selesai, tidak dapat diedit.');
        }

        $data = $r->validate([
            'notes'         => 'nullable|string|max:500',
            'asset_id'      => 'required|array|min:1',
            'asset_id.*'    => 'exists:tik_assets,id',
            'qty'           => 'required|array',
            'qty.*'         => 'required|integer|min:1',
            'photos.*'      => 'nullable|image|max:4096',
        ]);

        try {
            DB::transaction(function () use ($borrowing, $r, $data) {
                // validasi stok aktual (optional, tapi aman)
                $assetIds = array_map('intval', $data['asset_id']);
                $assets = \App\Models\TikAsset::whereIn('id', $assetIds)->lockForUpdate()->get()->keyBy('id');

                foreach ($assetIds as $i => $assetId) {
                    $qtyReq = (int)$data['qty'][$i];
                    $borrowed = DB::table('tik_borrowing_items as bi')
                        ->join('tik_borrowings as b','b.id','=','bi.tik_borrowing_id')
                        ->where('b.status','ongoing')
                        ->where('bi.tik_asset_id',$assetId)
                        ->where('b.id','<>',$borrowing->id) // exclude transaksi ini
                        ->sum('bi.qty');

                    $available = max(0, ($assets[$assetId]->quantity ?? 0) - $borrowed);
                    if ($qtyReq > $available) {
                        throw new \RuntimeException("Stok tidak cukup untuk {$assets[$assetId]->name}. Sisa: {$available}");
                    }
                }

                // update notes
                $borrowing->update([
                    'notes' => $data['notes'] ?? $borrowing->notes,
                ]);

                // replace items
                $borrowing->items()->delete();
                foreach ($data['asset_id'] as $i => $assetId) {
                    $borrowing->items()->create([
                        'tik_asset_id' => (int)$assetId,
                        'qty'          => (int)$data['qty'][$i],
                    ]);
                }

                // foto tambahan (opsional)
                if ($r->hasFile('photos')) {
                    foreach ($r->file('photos') as $file) {
                        $path = $file->store('tik_borrowings', 'public');
                        $borrowing->photos()->create([
                            'phase' => 'checkout', // atau 'return' sesuai konteks edit
                            'path'  => $path,
                        ]);
                    }
                }
            });
        } catch (\Throwable $e) {
            return back()->withErrors(['qty' => $e->getMessage()])->withInput();
        }

        return redirect()->route('op.tik.borrow.show', $borrowing->id)
            ->with('status','Peminjaman berhasil diperbarui.');
    }

}
