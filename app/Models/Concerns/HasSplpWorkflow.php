<?php

namespace App\Models\Concerns;

use Illuminate\Support\Facades\DB;

/**
 * Shared lifecycle for SPLP permohonan (V1–V5).
 *
 * Mengikuti tahapan SOP: ajukan → verifikasi administrasi → verifikasi teknis →
 * keputusan → provisioning (selesai). Karena satu role Admin menangani semua
 * tahap, transisi dilakukan via satu metode updateStatus di admin controller.
 *
 * Model pemakai wajib mendefinisikan: public static function ticketPrefix(): string
 */
trait HasSplpWorkflow
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_DIAJUKAN = 'diajukan';
    public const STATUS_VERIF_ADMIN = 'verifikasi_administrasi';
    public const STATUS_VERIF_TEKNIS = 'verifikasi_teknis';
    public const STATUS_MENUNGGU_KEPUTUSAN = 'menunggu_keputusan';
    public const STATUS_DISETUJUI = 'disetujui';
    public const STATUS_SELESAI = 'selesai';
    public const STATUS_DITOLAK = 'ditolak';
    public const STATUS_PERLU_PERBAIKAN = 'perlu_perbaikan';

    /**
     * Seluruh nilai status (dipakai untuk enum migrasi & rule validasi).
     */
    public static function statuses(): array
    {
        return [
            self::STATUS_DRAFT,
            self::STATUS_DIAJUKAN,
            self::STATUS_VERIF_ADMIN,
            self::STATUS_VERIF_TEKNIS,
            self::STATUS_MENUNGGU_KEPUTUSAN,
            self::STATUS_DISETUJUI,
            self::STATUS_SELESAI,
            self::STATUS_DITOLAK,
            self::STATUS_PERLU_PERBAIKAN,
        ];
    }

    /**
     * Status yang boleh dipilih admin saat memproses (tanpa draft).
     */
    public static function adminStatuses(): array
    {
        return [
            self::STATUS_DIAJUKAN,
            self::STATUS_VERIF_ADMIN,
            self::STATUS_VERIF_TEKNIS,
            self::STATUS_MENUNGGU_KEPUTUSAN,
            self::STATUS_DISETUJUI,
            self::STATUS_SELESAI,
            self::STATUS_DITOLAK,
            self::STATUS_PERLU_PERBAIKAN,
        ];
    }

    public static function statusLabels(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_DIAJUKAN => 'Diajukan',
            self::STATUS_VERIF_ADMIN => 'Verifikasi Administrasi',
            self::STATUS_VERIF_TEKNIS => 'Verifikasi Teknis',
            self::STATUS_MENUNGGU_KEPUTUSAN => 'Menunggu Keputusan',
            self::STATUS_DISETUJUI => 'Disetujui',
            self::STATUS_SELESAI => 'Selesai',
            self::STATUS_DITOLAK => 'Ditolak',
            self::STATUS_PERLU_PERBAIKAN => 'Perlu Perbaikan',
        ];
    }

    /**
     * Kelas Tailwind badge per status (untuk view index/show).
     */
    public static function statusBadgeClasses(): array
    {
        return [
            self::STATUS_DRAFT => 'bg-gray-100 text-gray-700',
            self::STATUS_DIAJUKAN => 'bg-yellow-100 text-yellow-800',
            self::STATUS_VERIF_ADMIN => 'bg-blue-100 text-blue-800',
            self::STATUS_VERIF_TEKNIS => 'bg-indigo-100 text-indigo-800',
            self::STATUS_MENUNGGU_KEPUTUSAN => 'bg-purple-100 text-purple-800',
            self::STATUS_DISETUJUI => 'bg-teal-100 text-teal-800',
            self::STATUS_SELESAI => 'bg-green-100 text-green-800',
            self::STATUS_DITOLAK => 'bg-red-100 text-red-800',
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

    /**
     * Pemohon hanya boleh edit/hapus saat draft atau perlu perbaikan.
     */
    public function isEditableByOwner(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_PERLU_PERBAIKAN], true);
    }

    // Scopes
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->whereNotIn('status', [
            self::STATUS_DRAFT,
            self::STATUS_SELESAI,
            self::STATUS_DITOLAK,
        ]);
    }

    /**
     * Generator nomor tiket unik per-tabel: PREFIX-YYMM-0001
     */
    public static function nextTicket(?string $prefix = null): string
    {
        $prefix = $prefix ?: static::ticketPrefix();
        $ym = now()->format('ym');
        $base = $prefix . '-' . $ym . '-';

        return DB::transaction(function () use ($base, $prefix, $ym) {
            $last = static::where('ticket_no', 'like', $prefix . '-' . $ym . '-%')
                ->orderByDesc('ticket_no')
                ->lockForUpdate()
                ->first();

            $lastNumber = $last ? intval(explode('-', $last->ticket_no)[2] ?? 0) : 0;

            return $base . str_pad((string) ($lastNumber + 1), 4, '0', STR_PAD_LEFT);
        }, 1);
    }

    protected static function bootHasSplpWorkflow(): void
    {
        static::creating(function ($model) {
            if (empty($model->ticket_no)) {
                $model->ticket_no = static::nextTicket();
            }
        });
    }
}
