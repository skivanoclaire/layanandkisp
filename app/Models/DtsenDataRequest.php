<?php

namespace App\Models;

use App\Models\Concerns\HasDtsenTicket;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Berkas permintaan data DTSEN — Tahap 2 s.d. Tahap 4 Juknis.
 *
 * Kelengkapan dokumen ditentukan level hak akses tertinggi yang dimohonkan (Bab IV):
 *   Level 2 (kustomisasi) : Surat Permohonan
 *   Level 3 (mikro)       : Surat Permohonan + KAK
 *   Level 4 (BNBA)        : Surat Permohonan + KAK + Dokumen Pendukung + BAST
 */
class DtsenDataRequest extends Model
{
    use HasDtsenTicket;

    /** Masa aktif token/tautan unduh (hari kalender), Fitur 4.3. */
    public const TOKEN_ACTIVE_DAYS = 30;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_DIAJUKAN = 'diajukan';
    public const STATUS_VERIF_ADMIN = 'verifikasi_administrasi';
    public const STATUS_PERLU_PERBAIKAN = 'perlu_perbaikan';
    public const STATUS_VERIF_SUBSTANSI = 'verifikasi_substansi';
    public const STATUS_KLARIFIKASI = 'klarifikasi';
    public const STATUS_DITOLAK = 'ditolak';
    public const STATUS_DITERIMA = 'diterima';
    public const STATUS_PEMROSESAN_QA = 'pemrosesan_qa';
    public const STATUS_MENUNGGU_BAST = 'menunggu_bast';
    public const STATUS_DATA_TERSEDIA = 'data_tersedia';
    public const STATUS_SELESAI = 'selesai';
    public const STATUS_KEDALUWARSA = 'kedaluwarsa';

    protected $fillable = [
        'ticket_no', 'user_id', 'unit_kerja_id', 'dtsen_account_request_id', 'dtsen_release_id',
        'pemohon_nama', 'pemohon_nip', 'pemohon_jabatan', 'pemohon_telepon',
        'nomor_surat', 'sifat_surat', 'jumlah_lampiran', 'tanggal_surat',
        'nama_program', 'jenis_permintaan', 'cakupan_wilayah_ids', 'cakupan_wilayah_catatan',
        'tujuan_penggunaan', 'surat_permohonan_path', 'level_akses',
        'metode_akses', 'metode_enkripsi', 'kapasitas_sdm',
        'kak_latar_belakang', 'kak_dasar_hukum', 'kak_maksud_tujuan', 'kak_metodologi',
        'kak_keluaran', 'kak_unit_akses', 'kak_jangka_mulai', 'kak_jangka_akhir',
        'kak_infrastruktur_penyimpanan', 'kak_personel_akses', 'kak_teknik_pelindungan',
        'kak_retensi_batas_waktu', 'kak_metode_pemusnahan', 'kak_pernyataan', 'kak_file_path',
        'status', 'submitted_at', 'verif_admin_at', 'dikembalikan_at', 'verif_substansi_at',
        'klarifikasi_at', 'diterima_at', 'ditolak_at', 'pemrosesan_at', 'bast_at',
        'akses_at', 'selesai_at',
        'adm_check_surat', 'adm_check_kak', 'adm_check_dokumen_pendukung',
        'adm_check_metode_akses', 'adm_check_enkripsi', 'adm_catatan', 'adm_verified_by',
        'sub_hasil', 'sub_catatan', 'sub_alasan_penolakan', 'sub_verified_by',
        'qa_check_pemilahan', 'qa_check_agregasi', 'qa_check_mutu', 'qa_check_kesesuaian',
        'qa_catatan', 'qa_by',
        'bast_nomor', 'bast_tanggal', 'bast_file_path', 'bast_uploaded_at',
        'bast_verified', 'bast_verified_by', 'bast_catatan',
        'infra_final', 'infra_parameter',
        'parent_request_id', 'ada_perubahan_signifikan', 'consent_true',
    ];

    protected $casts = [
        'tanggal_surat' => 'date',
        'cakupan_wilayah_ids' => 'array',
        'level_akses' => 'integer',
        'kak_dasar_hukum' => 'array',
        'kak_personel_akses' => 'array',
        'kak_teknik_pelindungan' => 'array',
        'kak_jangka_mulai' => 'date',
        'kak_jangka_akhir' => 'date',
        'kak_retensi_batas_waktu' => 'date',
        'kak_pernyataan' => 'boolean',
        'bast_tanggal' => 'date',
        'bast_uploaded_at' => 'datetime',
        'bast_verified' => 'boolean',
        'submitted_at' => 'datetime',
        'verif_admin_at' => 'datetime',
        'dikembalikan_at' => 'datetime',
        'verif_substansi_at' => 'datetime',
        'klarifikasi_at' => 'datetime',
        'diterima_at' => 'datetime',
        'ditolak_at' => 'datetime',
        'pemrosesan_at' => 'datetime',
        'bast_at' => 'datetime',
        'akses_at' => 'datetime',
        'selesai_at' => 'datetime',
        'adm_check_surat' => 'boolean',
        'adm_check_kak' => 'boolean',
        'adm_check_dokumen_pendukung' => 'boolean',
        'adm_check_metode_akses' => 'boolean',
        'adm_check_enkripsi' => 'boolean',
        'qa_check_pemilahan' => 'boolean',
        'qa_check_agregasi' => 'boolean',
        'qa_check_mutu' => 'boolean',
        'qa_check_kesesuaian' => 'boolean',
        'ada_perubahan_signifikan' => 'boolean',
        'consent_true' => 'boolean',
    ];

    public static function ticketPrefix(): string
    {
        return 'DTSEN-REQ';
    }

    // ---------------------------------------------------------------- Relasi

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function unitKerja(): BelongsTo { return $this->belongsTo(UnitKerja::class); }
    public function release(): BelongsTo { return $this->belongsTo(DtsenRelease::class, 'dtsen_release_id'); }
    public function admVerifier(): BelongsTo { return $this->belongsTo(User::class, 'adm_verified_by'); }
    public function subVerifier(): BelongsTo { return $this->belongsTo(User::class, 'sub_verified_by'); }
    public function qaBy(): BelongsTo { return $this->belongsTo(User::class, 'qa_by'); }
    public function bastVerifiedBy(): BelongsTo { return $this->belongsTo(User::class, 'bast_verified_by'); }

    public function accountRequest(): BelongsTo
    {
        return $this->belongsTo(DtsenAccountRequest::class, 'dtsen_account_request_id');
    }

    public function parentRequest(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_request_id');
    }

    public function followUpRequests(): HasMany
    {
        return $this->hasMany(self::class, 'parent_request_id');
    }

    public function requestVariables(): HasMany
    {
        return $this->hasMany(DtsenRequestVariable::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(DtsenRequestDocument::class);
    }

    public function clarifications(): HasMany
    {
        return $this->hasMany(DtsenClarification::class)->orderByDesc('tanggal');
    }

    public function tokens(): HasMany
    {
        return $this->hasMany(DtsenAccessToken::class)->orderByDesc('issued_at');
    }

    public function extensionRequests(): HasMany
    {
        return $this->hasMany(DtsenExtensionRequest::class)->orderByDesc('created_at');
    }

    public function utilizationReports(): HasMany
    {
        return $this->hasMany(DtsenUtilizationReport::class)->orderByDesc('periode_akhir');
    }

    public function destructionReports(): HasMany
    {
        return $this->hasMany(DtsenDestructionReport::class)->orderByDesc('waktu_pelaksanaan');
    }

    public function incidentReports(): HasMany
    {
        return $this->hasMany(DtsenIncidentReport::class)->orderByDesc('waktu_diketahui');
    }

    /** Token yang masih berlaku (belum dicabut & belum kedaluwarsa). */
    public function activeToken(): ?DtsenAccessToken
    {
        return $this->tokens()
            ->whereNull('revoked_at')
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->first();
    }

    // ------------------------------------------------------- Level hak akses

    public static function levelLabels(): array
    {
        return [
            1 => 'Level 1 — Agregat (Terbuka)',
            2 => 'Level 2 — Kustomisasi (Terbatas)',
            3 => 'Level 3 — Mikro tanpa Nama & Alamat (Terbatas)',
            4 => 'Level 4 — By Name By Address / BNBA (Terbatas)',
        ];
    }

    /** Level yang dilayani lewat alur permohonan (level 1 bersifat publik). */
    public static function selectableLevels(): array
    {
        return [2, 3, 4];
    }

    /** Dokumen wajib per level (Bab IV Juknis). */
    public static function levelRequirements(): array
    {
        return [
            2 => ['Surat Permohonan Data'],
            3 => ['Surat Permohonan Data', 'Kerangka Acuan Kerja (KAK)'],
            4 => ['Surat Permohonan Data', 'Kerangka Acuan Kerja (KAK)', 'Dokumen Pendukung', 'Berita Acara Serah Terima (BAST)'],
        ];
    }

    public function getLevelLabelAttribute(): string
    {
        return self::levelLabels()[$this->level_akses] ?? ('Level ' . $this->level_akses);
    }

    public function requiresKak(): bool
    {
        return $this->level_akses >= 3;
    }

    public function requiresDokumenPendukung(): bool
    {
        return $this->level_akses >= 4;
    }

    public function requiresBast(): bool
    {
        return $this->level_akses >= 4;
    }

    /**
     * Level tertinggi dari variabel yang dipilih. Bila permohonan mencakup lebih
     * dari satu level, sistem memberlakukan persyaratan level tertinggi.
     *
     * @param  array<int>  $variableIds
     */
    public static function highestLevelFor(array $variableIds): int
    {
        if (empty($variableIds)) {
            return 2;
        }

        $max = (int) DtsenVariable::whereIn('id', $variableIds)->max('level_minimal');

        return max(2, min(4, $max));
    }

    // ------------------------------------------------------------ Status

    public static function statusLabels(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_DIAJUKAN => 'Diajukan',
            self::STATUS_VERIF_ADMIN => 'Verifikasi Administrasi',
            self::STATUS_PERLU_PERBAIKAN => 'Dikembalikan untuk Perbaikan',
            self::STATUS_VERIF_SUBSTANSI => 'Verifikasi Substansi',
            self::STATUS_KLARIFIKASI => 'Klarifikasi',
            self::STATUS_DITOLAK => 'Ditolak',
            self::STATUS_DITERIMA => 'Diterima',
            self::STATUS_PEMROSESAN_QA => 'Pemrosesan Data & QA',
            self::STATUS_MENUNGGU_BAST => 'Menunggu BAST',
            self::STATUS_DATA_TERSEDIA => 'Data Tersedia',
            self::STATUS_SELESAI => 'Selesai',
            self::STATUS_KEDALUWARSA => 'Kedaluwarsa',
        ];
    }

    public static function statusBadgeClasses(): array
    {
        return [
            self::STATUS_DRAFT => 'bg-gray-100 text-gray-700',
            self::STATUS_DIAJUKAN => 'bg-yellow-100 text-yellow-800',
            self::STATUS_VERIF_ADMIN => 'bg-blue-100 text-blue-800',
            self::STATUS_PERLU_PERBAIKAN => 'bg-orange-100 text-orange-800',
            self::STATUS_VERIF_SUBSTANSI => 'bg-indigo-100 text-indigo-800',
            self::STATUS_KLARIFIKASI => 'bg-amber-100 text-amber-800',
            self::STATUS_DITOLAK => 'bg-red-100 text-red-800',
            self::STATUS_DITERIMA => 'bg-teal-100 text-teal-800',
            self::STATUS_PEMROSESAN_QA => 'bg-cyan-100 text-cyan-800',
            self::STATUS_MENUNGGU_BAST => 'bg-purple-100 text-purple-800',
            self::STATUS_DATA_TERSEDIA => 'bg-green-100 text-green-800',
            self::STATUS_SELESAI => 'bg-green-100 text-green-800',
            self::STATUS_KEDALUWARSA => 'bg-gray-200 text-gray-600',
        ];
    }

    public function getStatusLabelAttribute(): string
    {
        return self::statusLabels()[$this->status] ?? ucfirst((string) $this->status);
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return self::statusBadgeClasses()[$this->status] ?? 'bg-gray-100 text-gray-700';
    }

    /**
     * Urutan tahapan untuk timeline tracking (Modul Pendukung, Fitur Lintas-Tahapan).
     * `perlu_perbaikan`, `klarifikasi`, `ditolak`, dan `kedaluwarsa` adalah cabang,
     * bukan langkah linier, sehingga tidak masuk daftar ini.
     */
    public static function timelineSteps(): array
    {
        return [
            self::STATUS_DIAJUKAN => 'Diajukan',
            self::STATUS_VERIF_ADMIN => 'Verifikasi Administrasi',
            self::STATUS_VERIF_SUBSTANSI => 'Verifikasi Substansi',
            self::STATUS_DITERIMA => 'Diterima',
            self::STATUS_PEMROSESAN_QA => 'Pemrosesan & QA',
            self::STATUS_MENUNGGU_BAST => 'BAST',
            self::STATUS_DATA_TERSEDIA => 'Data Tersedia',
            self::STATUS_SELESAI => 'Selesai',
        ];
    }

    /** Timestamp yang menandai selesainya tiap langkah timeline. */
    public function timelineTimestamps(): array
    {
        return [
            self::STATUS_DIAJUKAN => $this->submitted_at,
            self::STATUS_VERIF_ADMIN => $this->verif_admin_at,
            self::STATUS_VERIF_SUBSTANSI => $this->verif_substansi_at,
            self::STATUS_DITERIMA => $this->diterima_at,
            self::STATUS_PEMROSESAN_QA => $this->pemrosesan_at,
            self::STATUS_MENUNGGU_BAST => $this->bast_at,
            self::STATUS_DATA_TERSEDIA => $this->akses_at,
            self::STATUS_SELESAI => $this->selesai_at,
        ];
    }

    public function isEditableByOwner(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_PERLU_PERBAIKAN], true);
    }

    /** Penolakan substansi bersifat final — proses tidak dapat dilanjutkan. */
    public function isFinal(): bool
    {
        return in_array($this->status, [self::STATUS_DITOLAK, self::STATUS_SELESAI, self::STATUS_KEDALUWARSA], true);
    }

    /** Pemohon dapat mengunggah BAST setelah data lolos pemrosesan & QA. */
    public function canUploadBast(): bool
    {
        return $this->requiresBast() && $this->status === self::STATUS_MENUNGGU_BAST;
    }

    /** Pelaporan pemanfaatan dibuka sejak data diterima OPD. */
    public function canReport(): bool
    {
        return in_array($this->status, [
            self::STATUS_DATA_TERSEDIA, self::STATUS_SELESAI, self::STATUS_KEDALUWARSA,
        ], true);
    }

    // ------------------------------------------------------------ Scope

    public function scopePending($query)
    {
        return $query->whereNotIn('status', [
            self::STATUS_DRAFT, self::STATUS_SELESAI, self::STATUS_DITOLAK, self::STATUS_KEDALUWARSA,
        ]);
    }

    public function scopeAwaitingAdmin($query)
    {
        return $query->whereIn('status', [self::STATUS_DIAJUKAN, self::STATUS_VERIF_ADMIN]);
    }

    public function scopeAwaitingSubstansi($query)
    {
        return $query->whereIn('status', [self::STATUS_VERIF_SUBSTANSI, self::STATUS_KLARIFIKASI]);
    }

    // ------------------------------------------------------------ Bantu tampilan

    /** Daftar nama wilayah cakupan siap tampil. */
    public function cakupanWilayahLabels(): array
    {
        return DtsenWilayah::labelsFor($this->cakupan_wilayah_ids);
    }

    public static function metodeAksesLabels(): array
    {
        return [
            'api' => 'API (JSON/SQL)',
            'excel_terenkripsi' => 'Excel Terenkripsi',
            'vpn' => 'VPN',
        ];
    }

    public static function jenisPermintaanLabels(): array
    {
        return [
            'bnba' => 'Data Lengkap BNBA',
            'pemadanan' => 'Pemadanan Data',
            'keduanya' => 'BNBA & Pemadanan Data',
        ];
    }

    /** Pilihan teknik pelindungan data pada KAK butir 9 (Lampiran IV). */
    public static function teknikPelindunganOptions(): array
    {
        return [
            'anonimisasi' => 'Anonimisasi',
            'pseudonim' => 'Pseudonimisasi',
            'masking' => 'Masking',
            'minimasi' => 'Minimasi Data',
            'access_control' => 'Access Control',
            'enkripsi_penyimpanan' => 'Enkripsi Penyimpanan',
            'lainnya' => 'Lainnya',
        ];
    }
}
