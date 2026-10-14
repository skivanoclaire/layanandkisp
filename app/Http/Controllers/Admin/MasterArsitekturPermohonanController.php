<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MasterArsitektur\Item;
use App\Models\MasterArsitektur\Permohonan;
use App\Services\MasterArsitektur\IsianItem;
use App\Services\MasterArsitektur\Skema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Kelola Permohonan: persetujuan usulan pembaruan Master Data Arsitektur
 * dari pengguna instansi. Usulan diterapkan ke entri saat disetujui.
 */
class MasterArsitekturPermohonanController extends Controller
{
    public function index(Request $request)
    {
        $request->validate(['status' => ['nullable', Rule::in([...array_keys(Permohonan::LABEL_STATUS), 'semua'])]]);
        $status = $request->query('status', Permohonan::STATUS_MENUNGGU);

        return view('admin.master-arsitektur.permohonan.index', [
            'status' => $status,
            'jumlah' => Permohonan::selectRaw('status, count(*) as jumlah')->groupBy('status')->pluck('jumlah', 'status'),
            'daftar' => Permohonan::with(['user', 'unitKerja'])
                ->when($status !== 'semua', fn ($q) => $q->where('status', $status))
                ->latest()->paginate(20)->withQueryString(),
        ]);
    }

    public function show(Permohonan $permohonan)
    {
        return view('admin.master-arsitektur.permohonan.show', [
            'permohonan' => $permohonan->load(['user', 'unitKerja', 'item', 'pemroses']),
        ]);
    }

    public function setujui(Request $request, Permohonan $permohonan)
    {
        $data = $request->validate(['catatan_admin' => ['nullable', 'string', 'max:1000']], [], ['catatan_admin' => 'catatan']);

        $galat = DB::transaction(function () use ($permohonan, $data, $request) {
            $permohonan = Permohonan::lockForUpdate()->find($permohonan->id);

            if (! $permohonan->menunggu()) {
                return 'Permohonan ini sudah diproses.';
            }
            if (! Skema::adaJenis($permohonan->jenis)) {
                return 'Jenis data pada permohonan ini sudah tidak dikenal.';
            }

            if ($permohonan->aksi === Permohonan::AKSI_UBAH) {
                $item = $permohonan->item;
                if (! $item) {
                    return 'Entri yang diusulkan berubah sudah tidak ada. Tolak permohonan ini.';
                }

                // Hanya kolom yang diusulkan berubah yang ditimpa, agar perubahan
                // lain pada entri sejak usulan dibuat tidak ikut hilang.
                $nilai = IsianItem::dariItem($item);
                foreach ($permohonan->perubahan() as $kunci => $ubah) {
                    $nilai[$kunci] = $ubah['baru'];
                }
            } else {
                $item = new Item(['jenis' => $permohonan->jenis]);
                $item->unit_kerja_id = $permohonan->unit_kerja_id;
                $item->unit_kerja_teks = $permohonan->unitKerja?->nama;
                $nilai = $permohonan->data;
            }

            IsianItem::isi($item, $nilai);
            $item->save();

            $permohonan->update([
                'item_id' => $item->id,
                'status' => Permohonan::STATUS_DISETUJUI,
                'catatan_admin' => $data['catatan_admin'] ?? null,
                'diproses_oleh' => $request->user()->id,
                'diproses_pada' => now(),
            ]);

            return null;
        });

        if ($galat) {
            return back()->with('error', $galat);
        }

        return redirect()->route('admin.arsitektur-permohonan.show', $permohonan)
            ->with('success', 'Permohonan disetujui dan data arsitektur sudah diperbarui.');
    }

    public function tolak(Request $request, Permohonan $permohonan)
    {
        $data = $request->validate(['catatan_admin' => ['required', 'string', 'max:1000']], [], ['catatan_admin' => 'alasan penolakan']);

        $ditolak = Permohonan::whereKey($permohonan->id)->where('status', Permohonan::STATUS_MENUNGGU)->update([
            'status' => Permohonan::STATUS_DITOLAK,
            'catatan_admin' => $data['catatan_admin'],
            'diproses_oleh' => $request->user()->id,
            'diproses_pada' => now(),
        ]);

        if (! $ditolak) {
            return back()->with('error', 'Permohonan ini sudah diproses.');
        }

        return redirect()->route('admin.arsitektur-permohonan.show', $permohonan)
            ->with('success', 'Permohonan ditolak.');
    }
}
