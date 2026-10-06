<?php

namespace App\Http\Controllers\User\Dtsen;

use App\Http\Controllers\Controller;
use App\Models\DtsenAccountRequest;
use App\Models\DtsenDataRequest;
use App\Models\DtsenRelease;
use App\Models\DtsenRequestDocument;
use App\Models\DtsenRequestLog;
use App\Models\DtsenVariable;
use App\Models\DtsenWilayah;
use App\Services\Dtsen\ProfilPemohon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/**
 * Tahap 2 - Pengajuan permintaan data DTSEN (Form 2.1 s.d. 2.6) beserta
 * unggah BAST pada Tahap 4 (Form 4.1) dan permintaan ulang (Form 4.5).
 */
class DataRequestController extends Controller
{
    public function index()
    {
        $items = DtsenDataRequest::with('unitKerja')
            ->where('user_id', auth()->id())
            ->withCount('requestVariables')
            ->orderByDesc('created_at')
            ->paginate(10);

        $account = DtsenAccountRequest::forUser(auth()->user());

        return view('user.dtsen.permohonan.index', compact('items', 'account'));
    }

    public function create(Request $request)
    {
        $account = $this->requireActiveAccount();

        // Form 4.5 - permintaan ulang merujuk permohonan sebelumnya.
        $parent = null;
        if ($request->filled('ulang_dari')) {
            $parent = DtsenDataRequest::with('requestVariables')
                ->where('id', $request->input('ulang_dari'))
                ->where('user_id', auth()->id())
                ->firstOrFail();
        }

        return view('user.dtsen.permohonan.create', array_merge(
            $this->formData(),
            compact('account', 'parent')
        ));
    }

    public function store(Request $request)
    {
        $account = $this->requireActiveAccount();

        $data = $this->validateData($request);
        $isSubmit = $request->input('action') === 'submit';

        $variables = $this->normalizeVariables($request);
        $level = DtsenDataRequest::highestLevelFor(array_keys($variables));

        if ($isSubmit) {
            $this->assertCompleteForSubmission($request, $level, null);
        }

        $item = DB::transaction(function () use ($request, $data, $isSubmit, $variables, $level, $account) {
            $user = auth()->user();

            $item = new DtsenDataRequest($data);
            $item->user_id = $user->id;
            $item->dtsen_account_request_id = $account->id;
            $item->unit_kerja_id = $account->unit_kerja_id ?: $user->unit_kerja_id;
            $item->dtsen_release_id = DtsenRelease::current()?->id;
            $item->level_akses = $level;
            $item->status = $isSubmit ? DtsenDataRequest::STATUS_DIAJUKAN : DtsenDataRequest::STATUS_DRAFT;
            $item->submitted_at = $isSubmit ? now() : null;
            $item->save();

            $this->handleUploads($request, $item);
            $item->save();

            $this->syncVariables($item, $variables);

            return $item;
        });

        $account->touchUsage();

        DtsenRequestLog::record(
            DtsenRequestLog::TYPE_PERMOHONAN,
            $item->id,
            $isSubmit ? 'created_submitted' : 'created_draft',
            $isSubmit
                ? "Permintaan data diajukan pada {$item->level_label}"
                : 'Draft permintaan data dibuat'
        );

        if (! $isSubmit) {
            return redirect()->route('user.dtsen.permohonan.edit', $item->id)
                ->with('status', 'Draft disimpan. Anda dapat melengkapi & mengajukan kapan saja.');
        }

        return redirect()->route('user.dtsen.permohonan.show', $item->id)
            ->with('status', "Permintaan data {$item->ticket_no} berhasil diajukan.");
    }

    public function show($id)
    {
        $item = $this->ownedRequest($id, [
            'unitKerja', 'release', 'requestVariables.variable', 'documents',
            'clarifications', 'tokens', 'extensionRequests', 'utilizationReports',
            'destructionReports', 'parentRequest',
        ]);

        $logs = DtsenRequestLog::forRequest(DtsenRequestLog::TYPE_PERMOHONAN, $item->id);
        $activeToken = $item->activeToken();
        $wilayahLabels = $item->cakupanWilayahLabels();

        return view('user.dtsen.permohonan.show', compact('item', 'logs', 'activeToken', 'wilayahLabels'));
    }

    public function edit($id)
    {
        $item = $this->ownedRequest($id, ['requestVariables', 'documents']);

        abort_unless($item->isEditableByOwner(), 403, 'Permohonan tidak bisa diubah karena sudah diproses.');

        $account = $this->requireActiveAccount();

        return view('user.dtsen.permohonan.edit', array_merge(
            $this->formData(),
            compact('item', 'account')
        ));
    }

    public function update(Request $request, $id)
    {
        $item = $this->ownedRequest($id);

        abort_unless($item->isEditableByOwner(), 403, 'Permohonan tidak bisa diubah karena sudah diproses.');

        $data = $this->validateData($request);
        $isSubmit = $request->input('action') === 'submit';

        $variables = $this->normalizeVariables($request);
        $level = DtsenDataRequest::highestLevelFor(array_keys($variables));

        if ($isSubmit) {
            $this->assertCompleteForSubmission($request, $level, $item);
        }

        DB::transaction(function () use ($request, $item, $data, $isSubmit, $variables, $level) {
            $item->fill($data);
            $item->level_akses = $level;

            if ($isSubmit) {
                $item->status = DtsenDataRequest::STATUS_DIAJUKAN;
                $item->submitted_at = $item->submitted_at ?: now();
                // Catatan perbaikan dari verifikasi administrasi sebelumnya di-reset
                // agar tidak tercampur dengan siklus verifikasi berikutnya.
                $item->adm_catatan = null;
            }

            $this->handleUploads($request, $item);
            $item->save();

            $this->syncVariables($item, $variables);
        });

        DtsenRequestLog::record(
            DtsenRequestLog::TYPE_PERMOHONAN,
            $item->id,
            $isSubmit ? 'updated_submitted' : 'updated_draft',
            $isSubmit ? 'Permohonan dilengkapi & diajukan ulang' : 'Draft permohonan diperbarui'
        );

        if ($isSubmit) {
            return redirect()->route('user.dtsen.permohonan.show', $item->id)
                ->with('status', 'Permohonan berhasil diajukan.');
        }

        return redirect()->route('user.dtsen.permohonan.index')->with('status', 'Draft berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $item = $this->ownedRequest($id);

        abort_unless($item->status === DtsenDataRequest::STATUS_DRAFT, 403, 'Hanya draft yang dapat dihapus.');

        $item->delete();

        return redirect()->route('user.dtsen.permohonan.index')->with('status', 'Draft permohonan dihapus.');
    }

    /**
     * Form 4.1 - Pemohon mengunggah BAST sebelum memperoleh akses ke data.
     */
    public function uploadBast(Request $request, $id)
    {
        $item = $this->ownedRequest($id);

        abort_unless($item->canUploadBast(), 403, 'BAST hanya dapat diunggah saat permohonan berstatus Menunggu BAST.');

        $data = $request->validate([
            'bast_nomor' => ['required', 'string', 'max:150'],
            'bast_tanggal' => ['required', 'date'],
            'bast_file' => ['required', 'file', 'mimes:pdf', 'max:10240'],
        ], [
            'bast_file.mimes' => 'BAST harus berkas PDF (mendukung dokumen bertanda tangan elektronik).',
        ]);

        $item->bast_nomor = $data['bast_nomor'];
        $item->bast_tanggal = $data['bast_tanggal'];
        $item->bast_file_path = $request->file('bast_file')->storeAs(
            'dtsen-docs/bast',
            'BAST_' . $item->ticket_no . '_' . time() . '.pdf',
            'public'
        );
        $item->bast_uploaded_at = now();
        $item->bast_verified = false;
        $item->save();

        DtsenRequestLog::record(
            DtsenRequestLog::TYPE_PERMOHONAN,
            $item->id,
            'bast_diunggah',
            "BAST {$item->bast_nomor} diunggah pemohon"
        );

        return back()->with('status', 'BAST berhasil diunggah. Menunggu verifikasi DKISP sebelum data dapat diakses.');
    }

    public function destroyDocument($id, $documentId)
    {
        $item = $this->ownedRequest($id);

        abort_unless($item->isEditableByOwner(), 403, 'Dokumen tidak bisa diubah karena permohonan sudah diproses.');

        $doc = $item->documents()->findOrFail($documentId);

        if ($doc->file_path) {
            Storage::disk('public')->delete($doc->file_path);
        }
        $doc->delete();

        return back()->with('status', 'Dokumen pendukung dihapus.');
    }

    // ------------------------------------------------------------ Helper

    private function ownedRequest($id, array $with = []): DtsenDataRequest
    {
        return DtsenDataRequest::with($with)
            ->where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();
    }

    /**
     * Permintaan data hanya dilayani selama akun DTSEN OPD aktif (Bab III huruf C).
     */
    private function requireActiveAccount(): DtsenAccountRequest
    {
        $account = DtsenAccountRequest::forUser(auth()->user());

        abort_if(
            $account === null,
            403,
            'Anda belum memiliki akun layanan DTSEN yang aktif. Ajukan pembuatan akun terlebih dahulu pada menu Akun Layanan DTSEN.'
        );

        return $account;
    }

    /** Data acuan yang dipakai bersama oleh form create & edit. */
    private function formData(): array
    {
        return [
            // Katalog dikelompokkan per set data (Keluarga/Anggota), lalu per kategori.
            'variables' => DtsenVariable::active()->ordered()->get()
                ->groupBy(fn ($v) => $v->set_data ?: 'lainnya')
                ->map(fn ($daftar) => $daftar->groupBy(fn ($v) => $v->kategori ?: 'Lainnya')),
            'wilayahTree' => DtsenWilayah::active()->with(['children' => fn ($q) => $q->active()->orderBy('nama')])
                ->whereIn('tingkat', ['provinsi', 'kabupaten_kota'])
                ->orderBy('tingkat')
                ->orderBy('nama')
                ->get(),
            'release' => DtsenRelease::current(),
        ];
    }

    private function validateData(Request $request): array
    {
        // Identitas pemohon mengikuti profil. Kolom terkunci di form hanya `readonly`,
        // jadi nilai profil ditegakkan di sini agar tidak bisa dilewati dari browser.
        $request->merge(ProfilPemohon::untuk($request->user())->untukRequest([
            'pemohon_nama' => 'nama',
            'pemohon_nip' => 'nip',
            'pemohon_jabatan' => 'jabatan',
            'pemohon_telepon' => 'telepon',
        ]));

        $validated = $request->validate([
            // Form 2.1
            'pemohon_nama' => ['required', 'string', 'max:150'],
            'pemohon_nip' => ['nullable', 'string', 'max:30'],
            'pemohon_jabatan' => ['nullable', 'string', 'max:150'],
            'pemohon_telepon' => ['required', 'string', 'max:30'],

            // Form 2.2
            'nomor_surat' => ['nullable', 'string', 'max:150'],
            'sifat_surat' => ['required', Rule::in(array_keys(DtsenAccountRequest::sifatSuratLabels()))],
            'jumlah_lampiran' => ['nullable', 'string', 'max:50'],
            'tanggal_surat' => ['nullable', 'date'],
            'nama_program' => ['required', 'string', 'max:255'],
            'jenis_permintaan' => ['required', Rule::in(array_keys(DtsenDataRequest::jenisPermintaanLabels()))],
            'cakupan_wilayah_ids' => ['nullable', 'array'],
            'cakupan_wilayah_ids.*' => ['integer', 'exists:dtsen_wilayahs,id'],
            'cakupan_wilayah_catatan' => ['nullable', 'string', 'max:1000'],
            'tujuan_penggunaan' => ['required', 'string'],
            'surat_permohonan' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],

            // Form 2.4 - KAK cukup diunggah sebagai PDF bertanda tangan Kepala OPD
            'kak_pernyataan' => ['nullable', 'boolean'],
            'kak_file' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],

            'consent_true' => ['accepted'],
        ], [
            'consent_true.accepted' => 'Anda harus menyatakan kebenaran data dan menyetujui ketentuan layanan.',
        ]);

        return collect($validated)
            ->except(['surat_permohonan', 'kak_file'])
            ->put('kak_pernyataan', $request->boolean('kak_pernyataan'))
            ->put('consent_true', true)
            ->all();
    }

    /**
     * Kewajiban dokumen mengikuti level tertinggi yang dimohonkan (Bab IV Juknis).
     * Draft boleh belum lengkap; pengecekan hanya berlaku saat pengajuan.
     */
    private function assertCompleteForSubmission(Request $request, int $level, ?DtsenDataRequest $existing): void
    {
        $errors = [];

        if (! $request->hasFile('surat_permohonan') && ! $existing?->surat_permohonan_path) {
            $errors['surat_permohonan'] = 'Surat Permohonan Data wajib diunggah untuk seluruh level hak akses.';
        }

        if (empty($request->input('variabel', []))) {
            $errors['variabel'] = 'Pilih minimal satu variabel data yang dimohonkan.';
        }

        // KAK tidak lagi diisi di formulir; cukup unggah dokumen bertanda tangan Kepala OPD.
        if ($level >= 3) {
            if (! $request->hasFile('kak_file') && ! $existing?->kak_file_path) {
                $errors['kak_file'] = "KAK bertanda tangan Kepala OPD (PDF) wajib diunggah untuk permohonan level {$level}.";
            }
            if (! $request->boolean('kak_pernyataan')) {
                $errors['kak_pernyataan'] = 'Pernyataan tanggung jawab pada KAK wajib disetujui.';
            }
        }

        if ($errors) {
            throw \Illuminate\Validation\ValidationException::withMessages($errors);
        }
    }

    /**
     * @return array<int, string> id variabel => kegunaan
     */
    private function normalizeVariables(Request $request): array
    {
        $selected = (array) $request->input('variabel', []);
        $kegunaan = (array) $request->input('kegunaan', []);

        $valid = DtsenVariable::active()->whereIn('id', $selected)->pluck('id')->all();

        $result = [];
        foreach ($valid as $id) {
            $result[$id] = trim((string) ($kegunaan[$id] ?? ''));
        }

        return $result;
    }

    /**
     * @param  array<int, string>  $variables
     */
    private function syncVariables(DtsenDataRequest $item, array $variables): void
    {
        $item->requestVariables()->delete();

        foreach ($variables as $variableId => $kegunaan) {
            $item->requestVariables()->create([
                'dtsen_variable_id' => $variableId,
                'kegunaan' => $kegunaan !== '' ? $kegunaan : '-',
            ]);
        }
    }

    private function handleUploads(Request $request, DtsenDataRequest $item): void
    {
        if ($request->hasFile('surat_permohonan')) {
            $item->surat_permohonan_path = $request->file('surat_permohonan')->storeAs(
                'dtsen-docs/surat',
                'SURAT_' . $item->ticket_no . '_' . time() . '.pdf',
                'public'
            );
        }

        if ($request->hasFile('kak_file')) {
            $item->kak_file_path = $request->file('kak_file')->storeAs(
                'dtsen-docs/kak',
                'KAK_' . $item->ticket_no . '_' . time() . '.pdf',
                'public'
            );
        }
    }
}
