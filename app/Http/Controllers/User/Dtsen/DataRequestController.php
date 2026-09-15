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
            $this->storeSupportingDocuments($request, $item);

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
            $this->storeSupportingDocuments($request, $item);
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
            'variables' => DtsenVariable::active()->ordered()->get()->groupBy('kategori'),
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

            // Form 2.6
            'metode_akses' => ['nullable', Rule::in(array_keys(DtsenDataRequest::metodeAksesLabels()))],
            'metode_enkripsi' => ['nullable', 'string', 'max:255'],
            'kapasitas_sdm' => ['nullable', 'string'],

            // Form 2.4 (KAK) - kewajiban isian dicek terpisah sesuai level akses
            'kak_latar_belakang' => ['nullable', 'string'],
            'kak_dasar_hukum' => ['nullable', 'array'],
            'kak_dasar_hukum.*' => ['nullable', 'string', 'max:500'],
            'kak_maksud_tujuan' => ['nullable', 'string'],
            'kak_metodologi' => ['nullable', 'string'],
            'kak_keluaran' => ['nullable', 'string'],
            'kak_unit_akses' => ['nullable', 'string', 'max:255'],
            'kak_jangka_mulai' => ['nullable', 'date'],
            'kak_jangka_akhir' => ['nullable', 'date', 'after_or_equal:kak_jangka_mulai'],
            'kak_infrastruktur_penyimpanan' => ['nullable', 'string'],
            'kak_personel_akses' => ['nullable', 'array'],
            'kak_personel_akses.*.nama' => ['nullable', 'string', 'max:150'],
            'kak_personel_akses.*.nip' => ['nullable', 'string', 'max:30'],
            'kak_personel_akses.*.jabatan' => ['nullable', 'string', 'max:150'],
            'kak_teknik_pelindungan' => ['nullable', 'array'],
            'kak_teknik_pelindungan.*' => [Rule::in(array_keys(DtsenDataRequest::teknikPelindunganOptions()))],
            'kak_retensi_batas_waktu' => ['nullable', 'date'],
            'kak_metode_pemusnahan' => ['nullable', 'string'],
            'kak_pernyataan' => ['nullable', 'boolean'],
            'kak_file' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],

            // Form 2.5
            'dokumen_pendukung' => ['nullable', 'array', 'max:10'],
            'dokumen_pendukung.*' => ['file', 'mimes:pdf,doc,docx,xls,xlsx,zip', 'max:10240'],
            'dokumen_keterangan' => ['nullable', 'array'],
            'dokumen_keterangan.*' => ['nullable', 'string', 'max:500'],

            'consent_true' => ['accepted'],
        ], [
            'kak_jangka_akhir.after_or_equal' => 'Tanggal akhir pemanfaatan tidak boleh mendahului tanggal mulai.',
            'consent_true.accepted' => 'Anda harus menyatakan kebenaran data dan menyetujui ketentuan layanan.',
        ]);

        return collect($validated)
            ->except(['surat_permohonan', 'kak_file', 'dokumen_pendukung', 'dokumen_keterangan'])
            ->put('kak_dasar_hukum', $this->cleanList($request->input('kak_dasar_hukum', [])))
            ->put('kak_personel_akses', $this->cleanPersonnel($request->input('kak_personel_akses', [])))
            ->put('kak_teknik_pelindungan', array_values($request->input('kak_teknik_pelindungan', [])))
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

        if ($level >= 3) {
            $wajibKak = [
                'kak_latar_belakang' => 'Latar belakang',
                'kak_maksud_tujuan' => 'Maksud dan tujuan',
                'kak_metodologi' => 'Rencana pemanfaatan data/metodologi',
                'kak_infrastruktur_penyimpanan' => 'Infrastruktur/media penyimpanan data',
                'kak_metode_pemusnahan' => 'Metode pemusnahan',
            ];
            foreach ($wajibKak as $field => $label) {
                if (blank($request->input($field))) {
                    $errors[$field] = "{$label} pada KAK wajib diisi untuk permohonan level {$level}.";
                }
            }

            if (blank($request->input('kak_jangka_mulai')) || blank($request->input('kak_jangka_akhir'))) {
                $errors['kak_jangka_akhir'] = 'Jangka waktu pemanfaatan data wajib diisi untuk permohonan level ' . $level . '.';
            }
            if (blank($request->input('kak_retensi_batas_waktu'))) {
                $errors['kak_retensi_batas_waktu'] = 'Batas waktu pemusnahan data wajib diisi untuk permohonan level ' . $level . '.';
            }
            if (empty($this->cleanList($request->input('kak_dasar_hukum', [])))) {
                $errors['kak_dasar_hukum'] = 'Cantumkan minimal satu dasar hukum yang melandasi tugas dan fungsi OPD.';
            }
            if (empty($this->cleanPersonnel($request->input('kak_personel_akses', [])))) {
                $errors['kak_personel_akses'] = 'Cantumkan minimal satu personel/unit yang diberi akses data.';
            }
            if (empty($request->input('kak_teknik_pelindungan', []))) {
                $errors['kak_teknik_pelindungan'] = 'Pilih minimal satu teknik pelindungan data yang diterapkan.';
            }
            if (! $request->boolean('kak_pernyataan')) {
                $errors['kak_pernyataan'] = 'Pernyataan tanggung jawab pada KAK wajib disetujui.';
            }
        }

        if ($level >= 4) {
            $adaDokumen = ! empty($request->file('dokumen_pendukung'))
                || ($existing && $existing->documents()->where('jenis', 'pendukung')->exists());

            if (! $adaDokumen) {
                $errors['dokumen_pendukung'] = 'Permohonan level 4 (BNBA) wajib melampirkan dokumen pendukung (dokumen perencanaan program/proposal kegiatan).';
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

    private function storeSupportingDocuments(Request $request, DtsenDataRequest $item): void
    {
        $files = $request->file('dokumen_pendukung', []);
        $keterangan = (array) $request->input('dokumen_keterangan', []);

        foreach ($files as $i => $file) {
            if (! $file) {
                continue;
            }

            $path = $file->storeAs(
                'dtsen-docs/pendukung',
                'DOK_' . $item->ticket_no . '_' . time() . '_' . $i . '.' . $file->extension(),
                'public'
            );

            DtsenRequestDocument::create([
                'dtsen_data_request_id' => $item->id,
                'jenis' => 'pendukung',
                'nama_dokumen' => $file->getClientOriginalName(),
                'keterangan' => $keterangan[$i] ?? null,
                'file_path' => $path,
                'uploaded_by' => auth()->id(),
            ]);
        }
    }

    /** Buang baris repeatable yang kosong. */
    private function cleanList(array $items): array
    {
        return array_values(array_filter(array_map(
            fn ($v) => is_string($v) ? trim($v) : $v,
            $items
        ), fn ($v) => $v !== null && $v !== ''));
    }

    private function cleanPersonnel(array $items): array
    {
        $clean = [];
        foreach ($items as $row) {
            if (! is_array($row) || blank($row['nama'] ?? null)) {
                continue;
            }
            $clean[] = [
                'nama' => trim($row['nama']),
                'nip' => trim((string) ($row['nip'] ?? '')),
                'jabatan' => trim((string) ($row['jabatan'] ?? '')),
            ];
        }

        return $clean;
    }
}
