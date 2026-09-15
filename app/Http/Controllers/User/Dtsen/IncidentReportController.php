<?php

namespace App\Http\Controllers\User\Dtsen;

use App\Http\Controllers\Controller;
use App\Models\DtsenDataRequest;
use App\Models\DtsenIncidentReport;
use App\Models\DtsenRequestLog;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

/**
 * Form 5.3 - Laporan insiden keamanan data DTSEN (Bab VI huruf C).
 * Wajib dilaporkan maks. 3x24 jam hari kerja sejak insiden diketahui.
 */
class IncidentReportController extends Controller
{
    public function index()
    {
        $items = DtsenIncidentReport::with(['dataRequest', 'handledBy'])
            ->where('user_id', auth()->id())
            ->orderByDesc('waktu_diketahui')
            ->paginate(10);

        return view('user.dtsen.insiden.index', compact('items'));
    }

    public function create()
    {
        $requests = DtsenDataRequest::where('user_id', auth()->id())
            ->whereNotIn('status', [DtsenDataRequest::STATUS_DRAFT])
            ->orderByDesc('created_at')
            ->get();

        return view('user.dtsen.insiden.create', compact('requests'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'dtsen_data_request_id' => ['nullable', 'exists:dtsen_data_requests,id'],
            'jenis_insiden' => ['required', Rule::in(array_keys(DtsenIncidentReport::jenisLabels()))],
            'waktu_diketahui' => ['required', 'date', 'before_or_equal:now'],
            'kronologi' => ['required', 'string'],
            'dampak' => ['required', 'string'],
            'tindakan_awal' => ['required', 'string'],
            'lampiran' => ['nullable', 'file', 'mimes:pdf,doc,docx,png,jpg,jpeg,zip', 'max:10240'],
        ], [
            'waktu_diketahui.before_or_equal' => 'Waktu diketahuinya insiden tidak boleh berada di masa depan.',
        ]);

        // Permohonan yang dirujuk (bila ada) harus milik pelapor sendiri.
        if (! empty($data['dtsen_data_request_id'])) {
            DtsenDataRequest::where('id', $data['dtsen_data_request_id'])
                ->where('user_id', auth()->id())
                ->firstOrFail();
        }

        $item = new DtsenIncidentReport(collect($data)->except('lampiran')->all());
        $item->user_id = auth()->id();
        $item->unit_kerja_id = auth()->user()->unit_kerja_id;
        $item->status = DtsenIncidentReport::STATUS_DILAPORKAN;
        $item->waktu_diketahui = Carbon::parse($data['waktu_diketahui']);
        $item->applyDeadline(now());

        if ($request->hasFile('lampiran')) {
            $item->file_path = $request->file('lampiran')->storeAs(
                'dtsen-docs/insiden',
                'INS_' . now()->format('YmdHis') . '.' . $request->file('lampiran')->extension(),
                'public'
            );
        }

        // Laporan yang melewati batas 3 hari kerja langsung dieskalasi ke
        // Petugas Pelindung DTSEN (Kepala DKISP) dan Prosesor.
        if ($item->terlambat) {
            $item->escalated_at = now();
        }

        $item->save();

        DtsenRequestLog::record(
            DtsenRequestLog::TYPE_INSIDEN,
            $item->id,
            $item->terlambat ? 'dilaporkan_terlambat' : 'dilaporkan',
            $item->jenis_label . ' dilaporkan'
        );

        return redirect()->route('user.dtsen.insiden.index')->with(
            'status',
            $item->terlambat
                ? "Laporan insiden {$item->ticket_no} tercatat MELEWATI batas 3x24 jam hari kerja dan dieskalasi ke Petugas Pelindung DTSEN."
                : "Laporan insiden {$item->ticket_no} terkirim ke Prosesor DTSEN (DKISP)."
        );
    }

    public function show($id)
    {
        $item = DtsenIncidentReport::with(['dataRequest', 'handledBy'])
            ->where('id', $id)->where('user_id', auth()->id())->firstOrFail();

        $logs = DtsenRequestLog::forRequest(DtsenRequestLog::TYPE_INSIDEN, $item->id);

        return view('user.dtsen.insiden.show', compact('item', 'logs'));
    }
}
