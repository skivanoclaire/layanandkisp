<?php

namespace App\Http\Controllers\Admin\Dtsen;

use App\Http\Controllers\Controller;
use App\Models\DtsenDestructionReport;
use App\Models\DtsenIncidentReport;
use App\Models\DtsenRequestLog;
use App\Models\DtsenUtilizationReport;
use App\Models\UnitKerja;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Tahap 5 - Rekapitulasi laporan pemanfaatan, berita acara pemusnahan, dan
 * penanganan insiden keamanan data (Bapperida + DKISP).
 */
class ReportAdminController extends Controller
{
    // ------------------------------------------------- Form 5.1 Pemanfaatan

    public function pemanfaatan(Request $request)
    {
        $query = DtsenUtilizationReport::with(['dataRequest.unitKerja', 'user', 'reviewer'])
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('unit_kerja_id')) {
            $query->whereHas('dataRequest', fn ($q) => $q->where('unit_kerja_id', $request->unit_kerja_id));
        }
        if ($request->filled('dari_tanggal')) {
            $query->whereDate('periode_akhir', '>=', $request->dari_tanggal);
        }
        if ($request->filled('sampai_tanggal')) {
            $query->whereDate('periode_akhir', '<=', $request->sampai_tanggal);
        }

        $items = $query->paginate(25)->withQueryString();
        $unitKerjaList = UnitKerja::forLayananDigital()->active()->orderBy('nama')->get();

        $ringkasan = [
            'total' => DtsenUtilizationReport::count(),
            'terkirim' => DtsenUtilizationReport::where('status', DtsenUtilizationReport::STATUS_TERKIRIM)->count(),
            'diverifikasi' => DtsenUtilizationReport::where('status', DtsenUtilizationReport::STATUS_DIVERIFIKASI)->count(),
        ];

        return view('admin.dtsen.laporan.pemanfaatan', compact('items', 'unitKerjaList', 'ringkasan'));
    }

    public function reviewPemanfaatan(Request $request, $id)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in([
                DtsenUtilizationReport::STATUS_DIVERIFIKASI,
                DtsenUtilizationReport::STATUS_PERLU_PERBAIKAN,
            ])],
            'catatan_review' => [
                'required_if:status,' . DtsenUtilizationReport::STATUS_PERLU_PERBAIKAN,
                'nullable', 'string', 'max:2000',
            ],
        ], [
            'catatan_review.required_if' => 'Catatan wajib diisi bila laporan dikembalikan untuk perbaikan.',
        ]);

        $item = DtsenUtilizationReport::findOrFail($id);
        $item->fill($data);
        $item->reviewed_by = auth()->id();
        $item->reviewed_at = now();
        $item->save();

        DtsenRequestLog::record(
            DtsenRequestLog::TYPE_PEMANFAATAN,
            $item->id,
            'review:' . $item->status,
            $item->catatan_review
        );

        return back()->with('status', 'Hasil telaah laporan pemanfaatan disimpan.');
    }

    // ------------------------------------------------- Form 5.2 Pemusnahan

    public function pemusnahan(Request $request)
    {
        $query = DtsenDestructionReport::with(['dataRequest.unitKerja', 'user', 'verifier'])
            ->orderByDesc('waktu_pelaksanaan');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $items = $query->paginate(25)->withQueryString();

        // Berita acara yang belum disampaikan padahal batas 14 hari sudah lewat.
        $terlambat = DtsenDestructionReport::with('dataRequest')
            ->where('status', DtsenDestructionReport::STATUS_DRAFT)
            ->whereDate('batas_penyampaian', '<', now()->toDateString())
            ->get();

        return view('admin.dtsen.laporan.pemusnahan', compact('items', 'terlambat'));
    }

    public function verifyPemusnahan(Request $request, $id)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in([
                DtsenDestructionReport::STATUS_DIVERIFIKASI,
                DtsenDestructionReport::STATUS_PERLU_PERBAIKAN,
            ])],
            'catatan_verifikasi' => [
                'required_if:status,' . DtsenDestructionReport::STATUS_PERLU_PERBAIKAN,
                'nullable', 'string', 'max:2000',
            ],
        ], [
            'catatan_verifikasi.required_if' => 'Catatan wajib diisi bila berita acara dikembalikan.',
        ]);

        $item = DtsenDestructionReport::findOrFail($id);
        $item->fill($data);
        $item->verified_by = auth()->id();
        $item->verified_at = now();
        $item->save();

        DtsenRequestLog::record(
            DtsenRequestLog::TYPE_PEMUSNAHAN,
            $item->id,
            'verifikasi:' . $item->status,
            $item->catatan_verifikasi
        );

        return back()->with('status', 'Hasil verifikasi berita acara pemusnahan disimpan.');
    }

    // ------------------------------------------------- Form 5.3 Insiden

    public function insiden(Request $request)
    {
        $query = DtsenIncidentReport::with(['dataRequest', 'user', 'unitKerja', 'handledBy'])
            ->orderByDesc('waktu_diketahui');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->boolean('terlambat')) {
            $query->where('terlambat', true);
        }

        $items = $query->paginate(25)->withQueryString();

        $ringkasan = [
            'total' => DtsenIncidentReport::count(),
            'belum_selesai' => DtsenIncidentReport::where('status', '!=', DtsenIncidentReport::STATUS_SELESAI)->count(),
            'terlambat' => DtsenIncidentReport::where('terlambat', true)->count(),
        ];

        return view('admin.dtsen.laporan.insiden', compact('items', 'ringkasan'));
    }

    public function handleInsiden(Request $request, $id)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(DtsenIncidentReport::statusLabels()))],
            'tindak_lanjut' => ['required', 'string', 'max:5000'],
        ]);

        $item = DtsenIncidentReport::findOrFail($id);
        $item->fill($data);
        $item->handled_by = auth()->id();
        $item->handled_at = now();
        $item->save();

        DtsenRequestLog::record(
            DtsenRequestLog::TYPE_INSIDEN,
            $item->id,
            'penanganan:' . $item->status,
            $item->tindak_lanjut
        );

        return back()->with('status', 'Penanganan insiden diperbarui.');
    }
}
