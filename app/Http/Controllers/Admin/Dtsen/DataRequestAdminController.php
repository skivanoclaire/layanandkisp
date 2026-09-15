<?php

namespace App\Http\Controllers\Admin\Dtsen;

use App\Http\Controllers\Controller;
use App\Models\DtsenAccessToken;
use App\Models\DtsenDataRequest;
use App\Models\DtsenRequestLog;
use App\Models\UnitKerja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/**
 * Sisi DKISP (prosesor) untuk permintaan data DTSEN:
 * Form 3.1 verifikasi administrasi, Form 3.4 pemrosesan & QA,
 * Form 4.1 verifikasi BAST, Form 4.2 kesepakatan infrastruktur,
 * dan Fitur 4.3 penerbitan token/tautan unduh.
 */
class DataRequestAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = DtsenDataRequest::with(['user', 'unitKerja'])
            ->withCount('requestVariables')
            ->where('status', '!=', DtsenDataRequest::STATUS_DRAFT)
            ->orderByDesc('submitted_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('level')) {
            $query->where('level_akses', $request->level);
        }
        if ($request->filled('unit_kerja_id')) {
            $query->where('unit_kerja_id', $request->unit_kerja_id);
        }
        if ($request->filled('dari_tanggal')) {
            $query->whereDate('submitted_at', '>=', $request->dari_tanggal);
        }
        if ($request->filled('sampai_tanggal')) {
            $query->whereDate('submitted_at', '<=', $request->sampai_tanggal);
        }
        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->where(function ($sub) use ($q) {
                $sub->where('ticket_no', 'like', "%{$q}%")
                    ->orWhere('nama_program', 'like', "%{$q}%")
                    ->orWhere('nomor_surat', 'like', "%{$q}%");
            });
        }

        $items = $query->paginate(25)->withQueryString();
        $unitKerjaList = UnitKerja::forLayananDigital()->active()->orderBy('nama')->get();

        return view('admin.dtsen.permohonan.index', compact('items', 'unitKerjaList'));
    }

    public function show($id)
    {
        $item = $this->loadRequest($id);

        $logs = DtsenRequestLog::forRequest(DtsenRequestLog::TYPE_PERMOHONAN, $item->id);
        $wilayahLabels = $item->cakupanWilayahLabels();

        return view('admin.dtsen.permohonan.show', compact('item', 'logs', 'wilayahLabels'));
    }

    /**
     * Form 3.1 - Verifikasi administrasi (SLA 1 hari kerja).
     * Lengkap/sesuai lanjut ke verifikasi substansi; selain itu dikembalikan ke pemohon.
     */
    public function verifyAdministrasi(Request $request, $id)
    {
        $data = $request->validate([
            'hasil' => ['required', Rule::in(['lengkap', 'dikembalikan'])],
            'adm_catatan' => ['required_if:hasil,dikembalikan', 'nullable', 'string', 'max:2000'],
            'adm_check_surat' => ['nullable', 'boolean'],
            'adm_check_kak' => ['nullable', 'boolean'],
            'adm_check_dokumen_pendukung' => ['nullable', 'boolean'],
            'adm_check_metode_akses' => ['nullable', 'boolean'],
            'adm_check_enkripsi' => ['nullable', 'boolean'],
        ], [
            'adm_catatan.required_if' => 'Catatan perbaikan wajib diisi bila permohonan dikembalikan.',
        ]);

        $item = DtsenDataRequest::findOrFail($id);

        abort_if(
            $item->isFinal(),
            403,
            'Permohonan sudah berstatus final dan tidak dapat diverifikasi ulang.'
        );

        $old = $item->status;

        DB::transaction(function () use ($request, $item, $data) {
            $item->adm_check_surat = $request->boolean('adm_check_surat');
            $item->adm_check_kak = $request->boolean('adm_check_kak');
            $item->adm_check_dokumen_pendukung = $request->boolean('adm_check_dokumen_pendukung');
            $item->adm_check_metode_akses = $request->boolean('adm_check_metode_akses');
            $item->adm_check_enkripsi = $request->boolean('adm_check_enkripsi');
            $item->adm_catatan = $data['adm_catatan'] ?? null;
            $item->adm_verified_by = auth()->id();
            $item->verif_admin_at = now();

            if ($data['hasil'] === 'lengkap') {
                $item->status = DtsenDataRequest::STATUS_VERIF_SUBSTANSI;
            } else {
                $item->status = DtsenDataRequest::STATUS_PERLU_PERBAIKAN;
                $item->dikembalikan_at = now();
            }

            $item->save();
        });

        DtsenRequestLog::record(
            DtsenRequestLog::TYPE_PERMOHONAN,
            $item->id,
            "verif_administrasi:{$old}->{$item->status}",
            $item->adm_catatan ?: 'Dokumen dinyatakan lengkap & sesuai'
        );

        return back()->with('status', "Verifikasi administrasi disimpan. Status kini {$item->status_label}.");
    }

    /**
     * Form 3.4 - Pemrosesan data & QA (SLA 2 hari kerja).
     */
    public function processQa(Request $request, $id)
    {
        $request->validate([
            'qa_check_pemilahan' => ['nullable', 'boolean'],
            'qa_check_agregasi' => ['nullable', 'boolean'],
            'qa_check_mutu' => ['nullable', 'boolean'],
            'qa_check_kesesuaian' => ['nullable', 'boolean'],
            'qa_catatan' => ['nullable', 'string', 'max:2000'],
            'selesaikan' => ['nullable', 'boolean'],
        ]);

        $item = DtsenDataRequest::findOrFail($id);

        abort_unless(
            in_array($item->status, [
                DtsenDataRequest::STATUS_DITERIMA,
                DtsenDataRequest::STATUS_PEMROSESAN_QA,
            ], true),
            403,
            'Pemrosesan & QA hanya dapat dilakukan setelah permohonan diterima pada verifikasi substansi.'
        );

        $old = $item->status;

        DB::transaction(function () use ($request, $item) {
            $item->qa_check_pemilahan = $request->boolean('qa_check_pemilahan');
            $item->qa_check_agregasi = $request->boolean('qa_check_agregasi');
            $item->qa_check_mutu = $request->boolean('qa_check_mutu');
            $item->qa_check_kesesuaian = $request->boolean('qa_check_kesesuaian');
            $item->qa_catatan = $request->input('qa_catatan');
            $item->qa_by = auth()->id();
            $item->status = DtsenDataRequest::STATUS_PEMROSESAN_QA;
            $item->pemrosesan_at = $item->pemrosesan_at ?: now();

            if ($request->boolean('selesaikan')) {
                // Level 4 (BNBA) wajib BAST sebelum hak akses diberikan;
                // level lain langsung siap diterbitkan token.
                $item->status = $item->requiresBast()
                    ? DtsenDataRequest::STATUS_MENUNGGU_BAST
                    : DtsenDataRequest::STATUS_PEMROSESAN_QA;
                $item->pemrosesan_at = now();
            }

            $item->save();
        });

        DtsenRequestLog::record(
            DtsenRequestLog::TYPE_PERMOHONAN,
            $item->id,
            "pemrosesan_qa:{$old}->{$item->status}",
            $item->qa_catatan
        );

        return back()->with('status', "Pemrosesan & QA disimpan. Status kini {$item->status_label}.");
    }

    /**
     * Form 4.1 - Verifikasi BAST yang diunggah pemohon.
     */
    public function verifyBast(Request $request, $id)
    {
        $data = $request->validate([
            'hasil' => ['required', Rule::in(['sah', 'tolak'])],
            'bast_catatan' => ['required_if:hasil,tolak', 'nullable', 'string', 'max:2000'],
        ], [
            'bast_catatan.required_if' => 'Catatan wajib diisi bila BAST dikembalikan.',
        ]);

        $item = DtsenDataRequest::findOrFail($id);

        abort_if($item->bast_file_path === null, 400, 'Pemohon belum mengunggah BAST.');

        $item->bast_verified = $data['hasil'] === 'sah';
        $item->bast_catatan = $data['bast_catatan'] ?? null;
        $item->bast_verified_by = auth()->id();
        $item->bast_at = $item->bast_verified ? now() : null;
        $item->save();

        DtsenRequestLog::record(
            DtsenRequestLog::TYPE_PERMOHONAN,
            $item->id,
            $item->bast_verified ? 'bast_disahkan' : 'bast_ditolak',
            $item->bast_catatan
        );

        return back()->with('status', $item->bast_verified
            ? 'BAST disahkan. Token akses sudah dapat diterbitkan.'
            : 'BAST dikembalikan ke pemohon untuk diperbaiki.');
    }

    /**
     * Form 4.2 - Kesepakatan infrastruktur pengiriman antara DKISP dan OPD.
     */
    public function updateInfrastruktur(Request $request, $id)
    {
        $data = $request->validate([
            'infra_final' => ['required', Rule::in(array_keys(DtsenDataRequest::metodeAksesLabels()))],
            'infra_parameter' => ['nullable', 'string', 'max:2000'],
        ]);

        $item = DtsenDataRequest::findOrFail($id);
        $item->fill($data)->save();

        DtsenRequestLog::record(
            DtsenRequestLog::TYPE_PERMOHONAN,
            $item->id,
            'infrastruktur_disepakati',
            DtsenDataRequest::metodeAksesLabels()[$item->infra_final] ?? $item->infra_final
        );

        return back()->with('status', 'Kesepakatan infrastruktur pengiriman disimpan.');
    }

    /**
     * Fitur 4.3 - Penerbitan token/tautan unduh, masa aktif 30 hari kalender.
     */
    public function issueToken(Request $request, $id)
    {
        $item = DtsenDataRequest::findOrFail($id);

        $data = $request->validate([
            'metode' => ['required', Rule::in(array_keys(DtsenDataRequest::metodeAksesLabels()))],
            'berkas' => ['required_if:metode,excel_terenkripsi', 'nullable', 'file', 'max:51200'],
            'parameter' => ['required_unless:metode,excel_terenkripsi', 'nullable', 'string', 'max:2000'],
            'masa_aktif_hari' => ['nullable', 'integer', 'min:1', 'max:365'],
            'max_download' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ], [
            'berkas.required_if' => 'Berkas data wajib diunggah untuk metode Excel terenkripsi.',
            'parameter.required_unless' => 'Parameter teknis (endpoint/kredensial/kanal) wajib diisi.',
        ]);

        abort_unless(
            $this->readyForToken($item),
            403,
            'Token hanya dapat diterbitkan setelah pemrosesan & QA selesai, dan BAST disahkan untuk permohonan level 4.'
        );

        $token = DB::transaction(function () use ($request, $item, $data) {
            // Token sebelumnya dicabut agar hanya ada satu kanal akses aktif.
            $item->tokens()->whereNull('revoked_at')->update(['revoked_at' => now()]);

            $token = new DtsenAccessToken([
                'dtsen_data_request_id' => $item->id,
                'token' => DtsenAccessToken::generateToken(),
                'metode' => $data['metode'],
                'parameter' => $data['parameter'] ?? null,
                'catatan' => $data['catatan'] ?? null,
                'issued_by' => auth()->id(),
                'issued_at' => now(),
                'expires_at' => now()->addDays((int) ($data['masa_aktif_hari'] ?? DtsenDataRequest::TOKEN_ACTIVE_DAYS)),
                'max_download' => $data['max_download'] ?? null,
            ]);

            if ($request->hasFile('berkas')) {
                $file = $request->file('berkas');
                $token->nama_berkas = $file->getClientOriginalName();
                $token->file_path = $file->storeAs(
                    'dtsen-data',
                    'DATA_' . $item->ticket_no . '_' . time() . '.' . $file->extension(),
                    'public'
                );
            }

            $token->save();

            $item->status = DtsenDataRequest::STATUS_DATA_TERSEDIA;
            $item->akses_at = now();
            $item->infra_final = $item->infra_final ?: $data['metode'];
            $item->save();

            return $token;
        });

        DtsenRequestLog::record(
            DtsenRequestLog::TYPE_PERMOHONAN,
            $item->id,
            'token_diterbitkan',
            'Token akses terbit, berlaku sampai ' . $token->expires_at->format('d/m/Y H:i')
        );

        return back()->with('status', 'Token/tautan unduh diterbitkan. Pemohon dapat mengunduh data sampai '
            . $token->expires_at->format('d/m/Y') . '.');
    }

    public function revokeToken(Request $request, $id, $tokenId)
    {
        $item = DtsenDataRequest::findOrFail($id);
        $token = $item->tokens()->findOrFail($tokenId);

        $request->validate(['alasan' => ['nullable', 'string', 'max:500']]);

        abort_if($token->isRevoked(), 400, 'Token sudah dicabut sebelumnya.');

        $token->revoked_at = now();
        $token->save();

        DtsenRequestLog::record(
            DtsenRequestLog::TYPE_PERMOHONAN,
            $item->id,
            'token_dicabut',
            $request->input('alasan')
        );

        return back()->with('status', 'Token akses dicabut.');
    }

    /** Tutup berkas permohonan setelah pemanfaatan data dinyatakan selesai. */
    public function complete(Request $request, $id)
    {
        $request->validate(['catatan' => ['nullable', 'string', 'max:1000']]);

        $item = DtsenDataRequest::findOrFail($id);

        abort_unless(
            $item->status === DtsenDataRequest::STATUS_DATA_TERSEDIA,
            403,
            'Permohonan hanya dapat diselesaikan setelah data tersedia bagi pemohon.'
        );

        $item->status = DtsenDataRequest::STATUS_SELESAI;
        $item->selesai_at = now();
        $item->save();

        DtsenRequestLog::record(
            DtsenRequestLog::TYPE_PERMOHONAN,
            $item->id,
            'selesai',
            $request->input('catatan')
        );

        return back()->with('status', "Permohonan {$item->ticket_no} ditandai selesai.");
    }

    /** Unduhan berkas lampiran oleh verifikator. */
    public function downloadDocument($id, $documentId)
    {
        $item = DtsenDataRequest::findOrFail($id);
        $doc = $item->documents()->findOrFail($documentId);

        abort_unless(Storage::disk('public')->exists($doc->file_path), 404, 'Berkas tidak ditemukan.');

        return Storage::disk('public')->download($doc->file_path, $doc->nama_dokumen);
    }

    // ------------------------------------------------------------ Helper

    private function loadRequest($id): DtsenDataRequest
    {
        return DtsenDataRequest::with([
            'user', 'unitKerja', 'release', 'accountRequest',
            'requestVariables.variable', 'documents', 'clarifications.createdBy',
            'tokens.issuedBy', 'tokens.downloadLogs.user', 'extensionRequests',
            'admVerifier', 'subVerifier', 'qaBy', 'parentRequest',
        ])->findOrFail($id);
    }

    /**
     * Syarat penerbitan token: pemrosesan & QA sudah dijalankan, dan untuk
     * permohonan level 4 BAST sudah diunggah serta disahkan.
     */
    private function readyForToken(DtsenDataRequest $item): bool
    {
        $qaDone = $item->pemrosesan_at !== null;

        $bastOk = ! $item->requiresBast() || $item->bast_verified;

        $statusOk = in_array($item->status, [
            DtsenDataRequest::STATUS_PEMROSESAN_QA,
            DtsenDataRequest::STATUS_MENUNGGU_BAST,
            DtsenDataRequest::STATUS_DATA_TERSEDIA,
        ], true);

        return $qaDone && $bastOk && $statusOk;
    }
}
