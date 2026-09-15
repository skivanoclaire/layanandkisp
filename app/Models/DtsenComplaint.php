<?php

namespace App\Models;

use App\Models\Concerns\HasDtsenTicket;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Form G.1 - Pengaduan, saran & masukan layanan DTSEN (Bab IX).
 *
 * Identitas pelapor dijamin kerahasiaannya: bila laporan ditandai anonim,
 * identitas tidak ditampilkan pada rekap dan hanya dibuka kepada Prosesor DTSEN.
 */
class DtsenComplaint extends Model
{
    use HasDtsenTicket;

    public const STATUS_BARU = 'baru';
    public const STATUS_DIPROSES = 'diproses';
    public const STATUS_SELESAI = 'selesai';

    protected $fillable = [
        'ticket_no', 'user_id', 'kategori', 'nama_pelapor', 'kontak_pelapor',
        'is_anonim', 'uraian', 'file_path', 'status', 'tindak_lanjut', 'handled_by', 'handled_at',
    ];

    protected $casts = [
        'is_anonim' => 'boolean',
        'handled_at' => 'datetime',
    ];

    public static function ticketPrefix(): string
    {
        return 'DTSEN-ADU';
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function handledBy(): BelongsTo { return $this->belongsTo(User::class, 'handled_by'); }

    public static function kategoriLabels(): array
    {
        return [
            'pengaduan' => 'Pengaduan',
            'saran' => 'Saran',
            'masukan' => 'Masukan',
        ];
    }

    public static function statusLabels(): array
    {
        return [
            self::STATUS_BARU => 'Baru',
            self::STATUS_DIPROSES => 'Diproses',
            self::STATUS_SELESAI => 'Selesai',
        ];
    }

    public static function statusBadgeClasses(): array
    {
        return [
            self::STATUS_BARU => 'bg-yellow-100 text-yellow-800',
            self::STATUS_DIPROSES => 'bg-blue-100 text-blue-800',
            self::STATUS_SELESAI => 'bg-green-100 text-green-800',
        ];
    }

    public function getKategoriLabelAttribute(): string
    {
        return self::kategoriLabels()[$this->kategori] ?? ucfirst((string) $this->kategori);
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
     * Nama pelapor untuk ditampilkan. Laporan anonim ditutup identitasnya kecuali
     * bagi petugas yang berwenang menanganinya.
     */
    public function displayName(bool $revealAnonymous = false): string
    {
        if ($this->is_anonim && ! $revealAnonymous) {
            return 'Anonim';
        }

        return $this->nama_pelapor ?: ($this->user?->name ?? 'Anonim');
    }
}
