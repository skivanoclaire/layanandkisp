<?php

namespace App\Models;

use App\Models\Concerns\HasDtsenTicket;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Tahap 1 — Pembuatan Akun Layanan DTSEN (Form 1.1 & 1.2, Lampiran I Juknis).
 *
 * Akun yang tidak digunakan selama 30 hari kalender dinonaktifkan otomatis oleh
 * command `dtsen:check-accounts`; OPD dapat mengajukan aktivasi ulang.
 */
class DtsenAccountRequest extends Model
{
    use HasDtsenTicket;

    /** Batas hari kalender tanpa pemakaian sebelum akun dinonaktifkan. */
    public const IDLE_DAYS = 30;

    /** Peringatan dikirim sekian hari sebelum penonaktifan. */
    public const WARN_BEFORE_DAYS = 5;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_DIAJUKAN = 'diajukan';
    public const STATUS_DISETUJUI = 'disetujui';
    public const STATUS_DIKEMBALIKAN = 'dikembalikan';
    public const STATUS_DITOLAK = 'ditolak';

    protected $fillable = [
        'ticket_no', 'user_id', 'unit_kerja_id',
        'nomor_surat', 'sifat_surat', 'jumlah_lampiran', 'tanggal_surat', 'surat_path',
        'narahubung_nama', 'narahubung_kontak', 'narahubung_email',
        'status', 'submitted_at', 'verified_at', 'verified_by',
        'check_surat_lengkap', 'check_ttd_kepala_opd', 'check_data_personel', 'catatan_perbaikan',
        'is_active', 'activated_at', 'last_used_at', 'deactivation_warned_at', 'deactivated_at',
        'consent_true',
    ];

    protected $casts = [
        'tanggal_surat' => 'date',
        'submitted_at' => 'datetime',
        'verified_at' => 'datetime',
        'activated_at' => 'datetime',
        'last_used_at' => 'datetime',
        'deactivation_warned_at' => 'datetime',
        'deactivated_at' => 'datetime',
        'check_surat_lengkap' => 'boolean',
        'check_ttd_kepala_opd' => 'boolean',
        'check_data_personel' => 'boolean',
        'is_active' => 'boolean',
        'consent_true' => 'boolean',
    ];

    public static function ticketPrefix(): string
    {
        return 'DTSEN-AKN';
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function unitKerja(): BelongsTo { return $this->belongsTo(UnitKerja::class); }
    public function verifier(): BelongsTo { return $this->belongsTo(User::class, 'verified_by'); }

    public function members(): HasMany
    {
        return $this->hasMany(DtsenAccountMember::class);
    }

    public function reactivations(): HasMany
    {
        return $this->hasMany(DtsenAccountReactivation::class)->orderByDesc('created_at');
    }

    public function dataRequests(): HasMany
    {
        return $this->hasMany(DtsenDataRequest::class);
    }

    public static function statusLabels(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_DIAJUKAN => 'Diajukan',
            self::STATUS_DISETUJUI => 'Disetujui',
            self::STATUS_DIKEMBALIKAN => 'Dikembalikan untuk Perbaikan',
            self::STATUS_DITOLAK => 'Ditolak',
        ];
    }

    public static function statusBadgeClasses(): array
    {
        return [
            self::STATUS_DRAFT => 'bg-gray-100 text-gray-700',
            self::STATUS_DIAJUKAN => 'bg-yellow-100 text-yellow-800',
            self::STATUS_DISETUJUI => 'bg-green-100 text-green-800',
            self::STATUS_DIKEMBALIKAN => 'bg-orange-100 text-orange-800',
            self::STATUS_DITOLAK => 'bg-red-100 text-red-800',
        ];
    }

    public static function sifatSuratLabels(): array
    {
        return [
            'biasa' => 'Biasa',
            'segera' => 'Segera',
            'sangat_segera' => 'Sangat Segera',
            'rahasia' => 'Rahasia',
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

    public function isEditableByOwner(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_DIKEMBALIKAN], true);
    }

    /** Akun siap dipakai untuk mengajukan permintaan data. */
    public function isUsable(): bool
    {
        return $this->status === self::STATUS_DISETUJUI && $this->is_active;
    }

    /** Tanggal akun akan dinonaktifkan bila tidak dipakai lagi. */
    public function idleDeadline(): ?\Illuminate\Support\Carbon
    {
        $anchor = $this->last_used_at ?? $this->activated_at;

        return $anchor?->copy()->addDays(self::IDLE_DAYS);
    }

    public function idleDaysLeft(): ?int
    {
        $deadline = $this->idleDeadline();

        return $deadline ? (int) now()->startOfDay()->diffInDays($deadline->copy()->startOfDay(), false) : null;
    }

    /** Tandai akun baru saja dipakai (menahan penonaktifan otomatis). */
    public function touchUsage(): void
    {
        if (! $this->isUsable()) {
            return;
        }

        $this->forceFill([
            'last_used_at' => now(),
            'deactivation_warned_at' => null,
        ])->saveQuietly();
    }

    public function scopeActiveAccount($query)
    {
        return $query->where('status', self::STATUS_DISETUJUI)->where('is_active', true);
    }

    /** Akun DTSEN aktif milik seorang pengguna (sebagai pengaju atau personel terdaftar). */
    public static function forUser(?User $user): ?self
    {
        if (! $user) {
            return null;
        }

        $own = static::activeAccount()->where('user_id', $user->id)->latest('activated_at')->first();
        if ($own) {
            return $own;
        }

        return static::activeAccount()
            ->whereHas('members', function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->orWhere('email', $user->email);
            })
            ->latest('activated_at')
            ->first();
    }
}
