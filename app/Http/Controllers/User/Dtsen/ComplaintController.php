<?php

namespace App\Http\Controllers\User\Dtsen;

use App\Http\Controllers\Controller;
use App\Models\DtsenComplaint;
use App\Models\DtsenRequestLog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Form G.1 - Pengaduan, saran & masukan layanan DTSEN (Bab IX).
 */
class ComplaintController extends Controller
{
    public function index()
    {
        $items = DtsenComplaint::where('user_id', auth()->id())
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('user.dtsen.pengaduan.index', compact('items'));
    }

    public function create()
    {
        return view('user.dtsen.pengaduan.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'kategori' => ['required', Rule::in(array_keys(DtsenComplaint::kategoriLabels()))],
            'uraian' => ['required', 'string'],
            'is_anonim' => ['nullable', 'boolean'],
            'nama_pelapor' => ['nullable', 'string', 'max:150'],
            'kontak_pelapor' => ['nullable', 'string', 'max:200'],
            'lampiran' => ['nullable', 'file', 'mimes:pdf,doc,docx,png,jpg,jpeg,zip', 'max:10240'],
        ]);

        $user = auth()->user();
        $isAnonim = $request->boolean('is_anonim');

        $item = new DtsenComplaint([
            'kategori' => $data['kategori'],
            'uraian' => $data['uraian'],
            'is_anonim' => $isAnonim,
            // Identitas tetap disimpan untuk keperluan tindak lanjut Prosesor,
            // namun tidak ditampilkan pada rekap bila pelapor memilih anonim.
            'nama_pelapor' => $data['nama_pelapor'] ?? $user->name,
            'kontak_pelapor' => $data['kontak_pelapor'] ?? $user->email,
            'status' => DtsenComplaint::STATUS_BARU,
        ]);
        $item->user_id = $user->id;

        if ($request->hasFile('lampiran')) {
            $item->file_path = $request->file('lampiran')->storeAs(
                'dtsen-docs/pengaduan',
                'ADU_' . now()->format('YmdHis') . '.' . $request->file('lampiran')->extension(),
                'public'
            );
        }

        $item->save();

        DtsenRequestLog::record(
            DtsenRequestLog::TYPE_PENGADUAN,
            $item->id,
            'dikirim',
            $item->kategori_label . ' disampaikan'
        );

        return redirect()->route('user.dtsen.pengaduan.index')
            ->with('status', "{$item->kategori_label} {$item->ticket_no} terkirim. Identitas Anda dijaga kerahasiaannya.");
    }

    public function show($id)
    {
        $item = DtsenComplaint::where('id', $id)->where('user_id', auth()->id())->firstOrFail();

        return view('user.dtsen.pengaduan.show', compact('item'));
    }
}
