<?php

namespace App\Http\Controllers\Admin\Dtsen;

use App\Http\Controllers\Controller;
use App\Models\DtsenDataRequest;
use App\Models\DtsenRelease;
use App\Models\DtsenRequestLog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Master Data - Rilis DTSEN. Penerbitan rilis baru memicu pemberitahuan kepada
 * OPD pemegang data rilis sebelumnya agar salinan lama dimusnahkan (Bab VII).
 */
class ReleaseController extends Controller
{
    public function index()
    {
        $items = DtsenRelease::withCount('variables')
            ->orderByDesc('tanggal_rilis')
            ->paginate(25);

        return view('admin.dtsen.releases.index', compact('items'));
    }

    public function create()
    {
        return view('admin.dtsen.releases.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        $item = new DtsenRelease($data);
        $item->created_by = auth()->id();
        $item->save();

        return redirect()->route('admin.dtsen.releases.index')->with('status', 'Rilis DTSEN ditambahkan.');
    }

    public function edit($id)
    {
        $item = DtsenRelease::findOrFail($id);

        return view('admin.dtsen.releases.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = DtsenRelease::findOrFail($id);
        $item->update($this->validateData($request, $item->id));

        return redirect()->route('admin.dtsen.releases.index')->with('status', 'Rilis DTSEN diperbarui.');
    }

    public function destroy($id)
    {
        $item = DtsenRelease::withCount('variables')->findOrFail($id);

        if ($item->variables_count > 0) {
            return back()->withErrors([
                'rilis' => 'Rilis masih memuat variabel. Pindahkan atau hapus variabelnya terlebih dahulu.',
            ]);
        }

        $item->delete();

        return back()->with('status', 'Rilis DTSEN dihapus.');
    }

    /**
     * Tandai rilis sebagai telah diumumkan: seluruh OPD pemegang data dari rilis
     * sebelumnya dicatat wajib memusnahkan salinan lama.
     */
    public function notify($id)
    {
        $item = DtsenRelease::findOrFail($id);

        $affected = DtsenDataRequest::whereIn('status', [
            DtsenDataRequest::STATUS_DATA_TERSEDIA,
            DtsenDataRequest::STATUS_SELESAI,
        ])
            ->where(function ($q) use ($item) {
                $q->whereNull('dtsen_release_id')->orWhere('dtsen_release_id', '!=', $item->id);
            })
            ->get();

        foreach ($affected as $request) {
            DtsenRequestLog::record(
                DtsenRequestLog::TYPE_PERMOHONAN,
                $request->id,
                'rilis_baru',
                "Rilis {$item->nomor_rilis} terbit. Salinan DTSEN rilis sebelumnya wajib dimusnahkan dan berita acaranya disampaikan ke DKISP."
            );
        }

        $item->notified_at = now();
        $item->save();

        return back()->with('status', "Pemberitahuan rilis {$item->nomor_rilis} dicatat pada {$affected->count()} permohonan pemegang data rilis sebelumnya.");
    }

    private function validateData(Request $request, ?int $ignoreId = null): array
    {
        $validated = $request->validate([
            'nomor_rilis' => ['required', 'string', 'max:50', Rule::unique('dtsen_releases', 'nomor_rilis')->ignore($ignoreId)],
            'tanggal_rilis' => ['required', 'date'],
            'keterangan' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }
}
