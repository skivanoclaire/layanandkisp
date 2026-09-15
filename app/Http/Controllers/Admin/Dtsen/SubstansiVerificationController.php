<?php

namespace App\Http\Controllers\Admin\Dtsen;

use App\Http\Controllers\Controller;
use App\Models\DtsenClarification;
use App\Models\DtsenDataRequest;
use App\Models\DtsenRequestLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Form 3.2 - Verifikasi substansi oleh Koordinator Forum Satu Data Daerah
 * (Bapperida), SLA 2 hari kerja. Pada tahap ini pemohon TIDAK dapat memperbaiki
 * dokumen; penolakan bersifat final.
 */
class SubstansiVerificationController extends Controller
{
    public function index(Request $request)
    {
        $query = DtsenDataRequest::with(['user', 'unitKerja'])
            ->withCount('requestVariables')
            ->whereIn('status', [
                DtsenDataRequest::STATUS_VERIF_SUBSTANSI,
                DtsenDataRequest::STATUS_KLARIFIKASI,
                DtsenDataRequest::STATUS_DITERIMA,
                DtsenDataRequest::STATUS_DITOLAK,
            ])
            ->orderByDesc('verif_admin_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('level')) {
            $query->where('level_akses', $request->level);
        }

        $items = $query->paginate(25)->withQueryString();

        return view('admin.dtsen.substansi.index', compact('items'));
    }

    public function show($id)
    {
        $item = DtsenDataRequest::with([
            'user', 'unitKerja', 'release', 'requestVariables.variable',
            'documents', 'clarifications.createdBy', 'subVerifier', 'parentRequest',
        ])->findOrFail($id);

        $logs = DtsenRequestLog::forRequest(DtsenRequestLog::TYPE_PERMOHONAN, $item->id);
        $wilayahLabels = $item->cakupanWilayahLabels();

        return view('admin.dtsen.substansi.show', compact('item', 'logs', 'wilayahLabels'));
    }

    /**
     * Keputusan substansi: diterima, ditolak (final), atau perlu klarifikasi.
     */
    public function decide(Request $request, $id)
    {
        $data = $request->validate([
            'sub_hasil' => ['required', Rule::in(['diterima', 'ditolak', 'klarifikasi'])],
            'sub_catatan' => ['nullable', 'string', 'max:2000'],
            'sub_alasan_penolakan' => ['required_if:sub_hasil,ditolak', 'nullable', 'string', 'max:2000'],
            'variabel_ditolak' => ['nullable', 'array'],
            'variabel_ditolak.*' => ['integer'],
            'catatan_variabel' => ['nullable', 'array'],
            'catatan_variabel.*' => ['nullable', 'string', 'max:500'],
        ], [
            'sub_alasan_penolakan.required_if' => 'Alasan penolakan wajib diisi. Penolakan bersifat final dan proses tidak dapat dilanjutkan.',
        ]);

        $item = DtsenDataRequest::with('requestVariables')->findOrFail($id);

        abort_unless(
            in_array($item->status, [
                DtsenDataRequest::STATUS_VERIF_SUBSTANSI,
                DtsenDataRequest::STATUS_KLARIFIKASI,
            ], true),
            403,
            'Permohonan ini tidak sedang menunggu verifikasi substansi.'
        );

        $old = $item->status;

        DB::transaction(function () use ($request, $item, $data) {
            $ditolak = array_map('intval', $request->input('variabel_ditolak', []));
            $catatanVariabel = (array) $request->input('catatan_variabel', []);

            foreach ($item->requestVariables as $rv) {
                $rv->disetujui = ! in_array($rv->dtsen_variable_id, $ditolak, true);
                $rv->catatan_verifikator = $catatanVariabel[$rv->dtsen_variable_id] ?? null;
                $rv->save();
            }

            $item->sub_hasil = $data['sub_hasil'];
            $item->sub_catatan = $data['sub_catatan'] ?? null;
            $item->sub_verified_by = auth()->id();
            $item->verif_substansi_at = now();

            match ($data['sub_hasil']) {
                'diterima' => tap($item, function ($m) {
                    $m->status = DtsenDataRequest::STATUS_DITERIMA;
                    $m->diterima_at = now();
                }),
                'ditolak' => tap($item, function ($m) use ($data) {
                    $m->status = DtsenDataRequest::STATUS_DITOLAK;
                    $m->ditolak_at = now();
                    $m->sub_alasan_penolakan = $data['sub_alasan_penolakan'];
                }),
                'klarifikasi' => tap($item, function ($m) {
                    $m->status = DtsenDataRequest::STATUS_KLARIFIKASI;
                    $m->klarifikasi_at = now();
                }),
            };

            $item->save();
        });

        DtsenRequestLog::record(
            DtsenRequestLog::TYPE_PERMOHONAN,
            $item->id,
            "verif_substansi:{$old}->{$item->status}",
            $item->sub_alasan_penolakan ?: $item->sub_catatan
        );

        return back()->with('status', "Hasil verifikasi substansi disimpan. Status kini {$item->status_label}.");
    }

    /**
     * Form 3.3 - Berita Acara Klarifikasi.
     */
    public function storeClarification(Request $request, $id)
    {
        $data = $request->validate([
            'tanggal' => ['required', 'date'],
            'tempat_media' => ['required', 'string', 'max:255'],
            'peserta' => ['required', 'string'],
            'pokok_klarifikasi' => ['required', 'string'],
            'hasil' => ['nullable', 'string'],
            'berita_acara' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
        ]);

        $item = DtsenDataRequest::findOrFail($id);

        $clarification = new DtsenClarification(collect($data)->except('berita_acara')->all());
        $clarification->dtsen_data_request_id = $item->id;
        $clarification->created_by = auth()->id();

        if ($request->hasFile('berita_acara')) {
            $clarification->file_path = $request->file('berita_acara')->storeAs(
                'dtsen-docs/klarifikasi',
                'BAK_' . $item->ticket_no . '_' . time() . '.pdf',
                'public'
            );
        }

        $clarification->save();

        DtsenRequestLog::record(
            DtsenRequestLog::TYPE_PERMOHONAN,
            $item->id,
            'klarifikasi_dicatat',
            'Berita acara klarifikasi ' . $clarification->tanggal->format('d/m/Y') . ' disimpan'
        );

        return back()->with('status', 'Berita acara klarifikasi tersimpan.');
    }
}
