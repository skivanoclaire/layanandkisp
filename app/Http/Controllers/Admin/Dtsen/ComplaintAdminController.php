<?php

namespace App\Http\Controllers\Admin\Dtsen;

use App\Http\Controllers\Controller;
use App\Models\DtsenComplaint;
use App\Models\DtsenRequestLog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Form G.1 - Tindak lanjut pengaduan, saran & masukan oleh Prosesor DTSEN.
 */
class ComplaintAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = DtsenComplaint::with(['user', 'handledBy'])->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        $items = $query->paginate(25)->withQueryString();

        return view('admin.dtsen.pengaduan.index', compact('items'));
    }

    public function show($id)
    {
        $item = DtsenComplaint::with(['user', 'handledBy'])->findOrFail($id);

        $logs = DtsenRequestLog::forRequest(DtsenRequestLog::TYPE_PENGADUAN, $item->id);

        return view('admin.dtsen.pengaduan.show', compact('item', 'logs'));
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(DtsenComplaint::statusLabels()))],
            'tindak_lanjut' => ['required', 'string', 'max:5000'],
        ]);

        $item = DtsenComplaint::findOrFail($id);
        $item->fill($data);
        $item->handled_by = auth()->id();
        $item->handled_at = now();
        $item->save();

        DtsenRequestLog::record(
            DtsenRequestLog::TYPE_PENGADUAN,
            $item->id,
            'tindak_lanjut:' . $item->status,
            $item->tindak_lanjut
        );

        return back()->with('status', 'Tindak lanjut pengaduan disimpan.');
    }
}
