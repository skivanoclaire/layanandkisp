<?php

namespace App\Http\Controllers\User\Dtsen;

use App\Http\Controllers\Controller;
use App\Models\DtsenDataRequest;
use App\Models\DtsenRequestLog;
use App\Models\DtsenUtilizationReport;
use Illuminate\Http\Request;

/**
 * Form 5.1 - Laporan pemanfaatan DTSEN oleh OPD (minimal 1x per 6 bulan).
 */
class UtilizationReportController extends Controller
{
    public function index()
    {
        $items = DtsenUtilizationReport::with('dataRequest')
            ->where('user_id', auth()->id())
            ->orderByDesc('periode_akhir')
            ->paginate(10);

        // Permohonan yang sudah menerima data dan karenanya wajib dilaporkan.
        $reportable = $this->reportableRequests();

        return view('user.dtsen.pemanfaatan.index', compact('items', 'reportable'));
    }

    public function create(Request $request)
    {
        $reportable = $this->reportableRequests();

        abort_if(
            $reportable->isEmpty(),
            403,
            'Belum ada permohonan yang datanya telah diterima, sehingga belum ada yang perlu dilaporkan.'
        );

        $selected = $reportable->firstWhere('id', (int) $request->input('permohonan')) ?? $reportable->first();

        return view('user.dtsen.pemanfaatan.create', compact('reportable', 'selected'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        $item = $this->ownedRequest($data['dtsen_data_request_id']);

        abort_unless($item->canReport(), 403, 'Permohonan ini belum berada pada tahap pemanfaatan data.');

        $report = new DtsenUtilizationReport($data);
        $report->user_id = auth()->id();
        $report->status = DtsenUtilizationReport::STATUS_TERKIRIM;
        $report->file_path = $this->storeAttachment($request, $item->ticket_no);
        $report->save();

        $item->accountRequest?->touchUsage();

        DtsenRequestLog::record(
            DtsenRequestLog::TYPE_PEMANFAATAN,
            $report->id,
            'dilaporkan',
            "Laporan pemanfaatan {$item->ticket_no} periode "
                . $report->periode_mulai->format('d/m/Y') . ' - ' . $report->periode_akhir->format('d/m/Y')
        );

        return redirect()->route('user.dtsen.pemanfaatan.index')
            ->with('status', 'Laporan pemanfaatan terkirim ke Bapperida dan DKISP untuk direkapitulasi.');
    }

    public function edit($id)
    {
        $report = $this->ownedReport($id);

        abort_unless(
            $report->status === DtsenUtilizationReport::STATUS_PERLU_PERBAIKAN,
            403,
            'Laporan hanya dapat diubah bila dikembalikan untuk perbaikan.'
        );

        $reportable = $this->reportableRequests();
        $selected = $report->dataRequest;

        return view('user.dtsen.pemanfaatan.edit', compact('report', 'reportable', 'selected'));
    }

    public function update(Request $request, $id)
    {
        $report = $this->ownedReport($id);

        abort_unless(
            $report->status === DtsenUtilizationReport::STATUS_PERLU_PERBAIKAN,
            403,
            'Laporan hanya dapat diubah bila dikembalikan untuk perbaikan.'
        );

        $data = $this->validateData($request);
        $item = $this->ownedRequest($data['dtsen_data_request_id']);

        $report->fill($data);
        $report->status = DtsenUtilizationReport::STATUS_TERKIRIM;
        if ($path = $this->storeAttachment($request, $item->ticket_no)) {
            $report->file_path = $path;
        }
        $report->save();

        DtsenRequestLog::record(
            DtsenRequestLog::TYPE_PEMANFAATAN,
            $report->id,
            'diperbaiki',
            'Laporan pemanfaatan diperbaiki & dikirim ulang'
        );

        return redirect()->route('user.dtsen.pemanfaatan.index')
            ->with('status', 'Laporan pemanfaatan diperbarui dan dikirim ulang.');
    }

    // ------------------------------------------------------------ Helper

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

    private function ownedRequest($id): DtsenDataRequest
    {
        return DtsenDataRequest::where('id', $id)->where('user_id', auth()->id())->firstOrFail();
    }

    private function ownedReport($id): DtsenUtilizationReport
    {
        return DtsenUtilizationReport::with('dataRequest')
            ->where('id', $id)->where('user_id', auth()->id())->firstOrFail();
    }

    private function validateData(Request $request): array
    {
        $validated = $request->validate([
            'dtsen_data_request_id' => ['required', 'exists:dtsen_data_requests,id'],
            'periode_mulai' => ['required', 'date'],
            'periode_akhir' => ['required', 'date', 'after_or_equal:periode_mulai'],
            'nama_program' => ['required', 'string', 'max:255'],
            'variabel_ids' => ['nullable', 'array'],
            'variabel_ids.*' => ['integer', 'exists:dtsen_variables,id'],
            'hasil_pemanfaatan' => ['required', 'string'],
            'kendala' => ['nullable', 'string'],
            'lampiran' => ['nullable', 'file', 'mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg,zip', 'max:10240'],
        ], [
            'periode_akhir.after_or_equal' => 'Akhir periode pelaporan tidak boleh mendahului awal periode.',
        ]);

        return collect($validated)->except('lampiran')->all();
    }

    private function storeAttachment(Request $request, string $ticket): ?string
    {
        if (! $request->hasFile('lampiran')) {
            return null;
        }

        return $request->file('lampiran')->storeAs(
            'dtsen-docs/pemanfaatan',
            'LAP_' . $ticket . '_' . time() . '.' . $request->file('lampiran')->extension(),
            'public'
        );
    }
}
