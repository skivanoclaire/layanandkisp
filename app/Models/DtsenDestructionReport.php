<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Form 5.2 - Berita Acara Pemusnahan Data (Bab VII huruf C).
 * Salinan BA wajib sampai ke DKISP maks. 14 hari kalender sejak pemusnahan.
 */
class DtsenDestructionReport extends Model
{
    /** Batas penyampaian salinan BA ke DKISP (hari kalender). */
    public const BATAS_PENYAMPAIAN_HARI = 14;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_DILAPORKAN = 'dilaporkan';
    public const STATUS_DIVERIFIKASI = 'diverifikasi';
    public const STATUS_PERLU_PERBAIKAN = 'perlu_perbaikan';

    protected $fillable = [
        'dtsen_data_request_id', 'user_id', 'dasar_pemusnahan', 'metode_pemusnahan',
        'waktu_pelaksanaan', 'batas_penyampaian', 'petugas_nama', 'petugas_nip',
        'saksi_nama', 'saksi_unit', 'file_path', 'status', 'reported_at',
        'catatan_verifikasi', 'verified_by', 'verified_at',
    ];

    protected $casts = [
        'waktu_pelaksanaan' => 'datetime',
        'batas_penyampaian' => 'date',
        'reported_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    public function dataRequest(): BelongsTo
    {
        return $this->belongsTo(DtsenDataRequest::class, 'dtsen_data_request_id');
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function verifier(): BelongsTo { return $this->belongsTo(User::class, 'verified_by'); }

    public static function dasarLabels(): array
    {
        return [
            'habis_retensi' => 'Habis masa retensi',
            'permintaan_pengendali' => 'Permintaan Pengendali Data',
            'permintaan_subjek_data' => 'Permintaan Subjek Data',
            'digantikan_rilis_terbaru' => 'Digantikan rilis DTSEN terbaru',
            'pemanfaatan_selesai' => 'Pemanfaatan telah selesai',
        ];
    }

    public static function statusLabels(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_DILAPORKAN => 'Dilaporkan',
            self::STATUS_DIVERIFIKASI => 'Diverifikasi',
            self::STATUS_PERLU_PERBAIKAN => 'Perlu Perbaikan',
        ];
    }

    public static function statusBadgeClasses(): array
    {
        return [
            self::STATUS_DRAFT => 'bg-gray-100 text-gray-700',
            self::STATUS_DILAPORKAN => 'bg-yellow-100 text-yellow-800',
            self::STATUS_DIVERIFIKASI => 'bg-green-100 text-green-800',
            self::STATUS_PERLU_PERBAIKAN => 'bg-orange-100 text-orange-800',
        ];
    }

    public function getDasarLabelAttribute(): string
    {
        return self::dasarLabels()[$this->dasar_pemusnahan] ?? (string) $this->dasar_pemusnahan;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::statusLabels()[$this->status] ?? ucfirst((string) $this->status);
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return self::statusBadgeClasses()[$this->status] ?? 'bg-gray-100 text-gray-700';
    }

    /** Sisa hari sebelum batas penyampaian salinan BA ke DKISP. */
    public function daysUntilDeadline(): ?int
    {
        if (! $this->batas_penyampaian) {
            return null;
        }

        return (int) now()->startOfDay()->diffInDays($this->batas_penyampaian->copy()->startOfDay(), false);
    }

    public function isOverdue(): bool
    {
        return $this->status === self::STATUS_DRAFT
            && $this->batas_penyampaian !== null
            && $this->batas_penyampaian->isPast();
    }
}
