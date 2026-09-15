<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Form 5.1 - Laporan pemanfaatan DTSEN, minimal satu kali per enam bulan
 * (Bab VIII huruf A). Direkap oleh Bapperida & DKISP.
 */
class DtsenUtilizationReport extends Model
{
    /** Interval pelaporan wajib (bulan). */
    public const PERIODE_BULAN = 6;

    public const STATUS_TERKIRIM = 'terkirim';
    public const STATUS_DIVERIFIKASI = 'diverifikasi';
    public const STATUS_PERLU_PERBAIKAN = 'perlu_perbaikan';

    protected $fillable = [
        'dtsen_data_request_id', 'user_id', 'periode_mulai', 'periode_akhir',
        'nama_program', 'variabel_ids', 'hasil_pemanfaatan', 'kendala', 'file_path',
        'status', 'catatan_review', 'reviewed_by', 'reviewed_at',
    ];

    protected $casts = [
        'periode_mulai' => 'date',
        'periode_akhir' => 'date',
        'variabel_ids' => 'array',
        'reviewed_at' => 'datetime',
    ];

    public function dataRequest(): BelongsTo
    {
        return $this->belongsTo(DtsenDataRequest::class, 'dtsen_data_request_id');
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function reviewer(): BelongsTo { return $this->belongsTo(User::class, 'reviewed_by'); }

    public static function statusLabels(): array
    {
        return [
            self::STATUS_TERKIRIM => 'Terkirim',
            self::STATUS_DIVERIFIKASI => 'Diverifikasi',
            self::STATUS_PERLU_PERBAIKAN => 'Perlu Perbaikan',
        ];
    }

    public static function statusBadgeClasses(): array
    {
        return [
            self::STATUS_TERKIRIM => 'bg-yellow-100 text-yellow-800',
            self::STATUS_DIVERIFIKASI => 'bg-green-100 text-green-800',
            self::STATUS_PERLU_PERBAIKAN => 'bg-orange-100 text-orange-800',
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

    /** Nama variabel yang dilaporkan dimanfaatkan. */
    public function variabelNames(): array
    {
        if (empty($this->variabel_ids)) {
            return [];
        }

        return DtsenVariable::whereIn('id', $this->variabel_ids)
            ->orderBy('kode')
            ->pluck('nama', 'kode')
            ->map(fn ($nama, $kode) => "{$kode} — {$nama}")
            ->values()
            ->all();
    }

    /**
     * Batas pelaporan berikutnya untuk sebuah permohonan: 6 bulan sejak laporan
     * terakhir, atau sejak data tersedia bila belum pernah melapor.
     */
    public static function nextDueFor(DtsenDataRequest $request): ?\Illuminate\Support\Carbon
    {
        $anchor = $request->utilizationReports()->max('periode_akhir');
        $anchor = $anchor ? \Illuminate\Support\Carbon::parse($anchor) : $request->akses_at;

        return $anchor?->copy()->addMonths(self::PERIODE_BULAN);
    }
}
