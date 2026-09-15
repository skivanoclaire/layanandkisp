<?php

namespace App\Http\Controllers\User\Dtsen;

use App\Http\Controllers\Controller;
use App\Models\DtsenDataRequest;
use App\Models\DtsenDestructionReport;
use App\Models\DtsenRequestLog;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

/**
 * Form 5.2 - Berita Acara Pemusnahan Data DTSEN (Bab VII huruf C).
 */
class DestructionReportController extends Controller
{
    public function index()
    {
        $items = DtsenDestructionReport::with('dataRequest')
            ->where('user_id', auth()->id())
            ->orderByDesc('waktu_pelaksanaan')
            ->paginate(10);

        $reportable = $this->reportableRequests();

        // Dihitung lintas halaman (bukan dari $items yang sudah dipaginasi) supaya
        // peringatan tetap muncul meski berita acara yang telat ada di halaman lain.
        $jumlahTerlambat = DtsenDestructionReport::where('user_id', auth()->id())
            ->where('status', DtsenDestructionReport::STATUS_DRAFT)
            ->whereDate('batas_penyampaian', '<', now()->toDateString())
            ->count();

        return view('user.dtsen.pemusnahan.index', compact('items', 'reportable', 'jumlahTerlambat'));
    }

    public function create(Request $request)
    {
        $reportable = $this->reportableRequests();

        abort_if($reportable->isEmpty(), 403, 'Belum ada data DTSEN yang berada di bawah penguasaan OPD Anda.');

        $selected = $reportable->firstWhere('id', (int) $request->input('permohonan')) ?? $reportable->first();

        return view('user.dtsen.pemusnahan.create', compact('reportable', 'selected'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $item = $this->ownedRequest($data['dtsen_data_request_id']);
        $isLapor = $request->input('action') === 'lapor';

        $report = new DtsenDestructionReport($data);
        $report->user_id = auth()->id();
        $report->batas_penyampaian = Carbon::parse($data['waktu_pelaksanaan'])
            ->addDays(DtsenDestructionReport::BATAS_PENYAMPAIAN_HARI)
            ->toDateString();
        $report->file_path = $this->storeAttachment($request, $item->ticket_no);
        $report->status = $isLapor
            ? DtsenDestructionReport::STATUS_DILAPORKAN
            : DtsenDestructionReport::STATUS_DRAFT;
        $report->reported_at = $isLapor ? now() : null;
        $report->save();

        DtsenRequestLog::record(
            DtsenRequestLog::TYPE_PEMUSNAHAN,
            $report->id,
            $isLapor ? 'dilaporkan' : 'draft',
            "Berita acara pemusnahan data {$item->ticket_no}"
        );

        return redirect()->route('user.dtsen.pemusnahan.index')->with(
            'status',
            $isLapor
                ? 'Berita acara pemusnahan disampaikan ke DKISP.'
                : 'Draft berita acara pemusnahan disimpan. Salinan wajib disampaikan ke DKISP maksimal '
                    . DtsenDestructionReport::BATAS_PENYAMPAIAN_HARI . ' hari kalender sejak pemusnahan.'
        );
    }

    public function edit($id)
    {
        $report = $this->ownedReport($id);

        abort_unless($this->isEditable($report), 403, 'Berita acara sudah diverifikasi dan tidak dapat diubah.');

        $reportable = $this->reportableRequests();
        $selected = $report->dataRequest;

        return view('user.dtsen.pemusnahan.edit', compact('report', 'reportable', 'selected'));
    }

    public function update(Request $request, $id)
    {
        $report = $this->ownedReport($id);

        abort_unless($this->isEditable($report), 403, 'Berita acara sudah diverifikasi dan tidak dapat diubah.');

        $data = $this->validateData($request);
        $item = $this->ownedRequest($data['dtsen_data_request_id']);
        $isLapor = $request->input('action') === 'lapor';

        $report->fill($data);
        $report->batas_penyampaian = Carbon::parse($data['waktu_pelaksanaan'])
            ->addDays(DtsenDestructionReport::BATAS_PENYAMPAIAN_HARI)
            ->toDateString();
        if ($path = $this->storeAttachment($request, $item->ticket_no)) {
            $report->file_path = $path;
        }
        if ($isLapor) {
            $report->status = DtsenDestructionReport::STATUS_DILAPORKAN;
            $report->reported_at = $report->reported_at ?: now();
        }
        $report->save();

        DtsenRequestLog::record(
            DtsenRequestLog::TYPE_PEMUSNAHAN,
            $report->id,
            $isLapor ? 'dilaporkan' : 'diperbarui',
            'Berita acara pemusnahan diperbarui'
        );

        return redirect()->route('user.dtsen.pemusnahan.index')
            ->with('status', $isLapor ? 'Berita acara pemusnahan disampaikan ke DKISP.' : 'Draft berita acara diperbarui.');
    }

    // ------------------------------------------------------------ Helper

    /** Permohonan yang datanya sudah diterima OPD, sehingga bisa dimusnahkan. */
    private function reportableRequests()
    {
        return DtsenDataRequest::with('requestVariables.variable')
            ->where('user_id', auth()->id())
            ->whereIn('status', [
                DtsenDataRequest::STATUS_DATA_TERSEDIA,
                DtsenDataRequest::STATUS_SELESAI,
                DtsenDataRequest::STATUS_KEDALUWARSA,
            ])
            ->orderByDesc('akses_at')
            ->get();
    }

    private function isEditable(DtsenDestructionReport $report): bool
    {
        return in_array($report->status, [
            DtsenDestructionReport::STATUS_DRAFT,
            DtsenDestructionReport::STATUS_PERLU_PERBAIKAN,
        ], true);
    }

    private function ownedRequest($id): DtsenDataRequest
    {
        return DtsenDataRequest::where('id', $id)->where('user_id', auth()->id())->firstOrFail();
    }

    private function ownedReport($id): DtsenDestructionReport
    {
        return DtsenDestructionReport::with('dataRequest')
            ->where('id', $id)->where('user_id', auth()->id())->firstOrFail();
    }

    private function validateData(Request $request): array
    {
        $validated = $request->validate([
            'dtsen_data_request_id' => ['required', 'exists:dtsen_data_requests,id'],
            'dasar_pemusnahan' => ['required', Rule::in(array_keys(DtsenDestructionReport::dasarLabels()))],
            'metode_pemusnahan' => ['required', 'string'],
            'waktu_pelaksanaan' => ['required', 'date', 'before_or_equal:now'],
            'petugas_nama' => ['required', 'string', 'max:150'],
            'petugas_nip' => ['nullable', 'string', 'max:30'],
            'saksi_nama' => ['nullable', 'string', 'max:150'],
            'saksi_unit' => ['nullable', 'string', 'max:200'],
            'berita_acara' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
        ], [
            'waktu_pelaksanaan.before_or_equal' => 'Waktu pemusnahan tidak boleh berada di masa depan.',
        ]);

        return collect($validated)->except('berita_acara')->all();
    }

    private function storeAttachment(Request $request, string $ticket): ?string
    {
        if (! $request->hasFile('berita_acara')) {
            return null;
        }

        return $request->file('berita_acara')->storeAs(
            'dtsen-docs/pemusnahan',
            'BAP_' . $ticket . '_' . time() . '.pdf',
            'public'
        );
    }
}
