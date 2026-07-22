<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class SubdomainDataUpdateRequest extends Model
{
    /**
     * Field WebMonitor yang boleh diperbarui pengguna lewat fitur ini
     * (Informasi Aplikasi + Teknologi + Informasi Server). Dipakai sebagai
     * whitelist saat menyimpan usulan & saat menerapkan ke WebMonitor.
     */
    public const EDITABLE_FIELDS = [
        // Informasi Aplikasi
        'nama_aplikasi',
        'tahun_pembuatan',
        'description',
        'latar_belakang',
        'manfaat_aplikasi',
        'developer',
        'contact_person',
        'contact_phone',
        // Teknologi
        'programming_language_id',
        'programming_language_version',
        'framework_id',
        'framework_version',
        'database_id',
        'database_version',
        'frontend_tech',
        // Informasi Server
        'server_ownership',
        'server_owner_name',
        'server_location_id',
    ];

    protected $fillable = [
        'user_id',
        'ticket_number',
        'web_monitor_id',
        'proposed_data',
        'proposed_decommission',
        'original_data',
        'reason',
        'status',
        'admin_notes',
        'processed_by',
        'processed_at',
        'applied_at',
        'file_berita_acara',
        'berita_acara_uploaded_at',
    ];

    protected $casts = [
        'proposed_data' => 'array',
        'original_data' => 'array',
        'processed_at' => 'datetime',
        'applied_at' => 'datetime',
        'berita_acara_uploaded_at' => 'datetime',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function webMonitor(): BelongsTo
    {
        return $this->belongsTo(WebMonitor::class);
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    // Scopes
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeRevisi($query)
    {
        return $query->where('status', 'revisi');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'disetujui');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'ditolak');
    }

    // Status helpers
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isRevisi(): bool
    {
        return $this->status === 'revisi';
    }

    public function isApproved(): bool
    {
        return $this->status === 'disetujui';
    }

    public function isRejected(): bool
    {
        return $this->status === 'ditolak';
    }

    /** Pengguna boleh mengedit/ajukan ulang saat pending atau diminta revisi. */
    public function isEditable(): bool
    {
        return in_array($this->status, ['pending', 'revisi'], true);
    }

    /** Permohonan dianggap selesai bila sudah disetujui. */
    public function isCompleted(): bool
    {
        return $this->status === 'disetujui';
    }

    /** Sudah ada berkas berita acara terunggah? */
    public function hasBeritaAcara(): bool
    {
        return !empty($this->file_berita_acara);
    }

    /** Ada usulan perubahan status pensiun/non-aktif? */
    public function hasStatusProposal(): bool
    {
        return $this->proposed_decommission !== null;
    }

    /** Label usulan status untuk ditampilkan. */
    public function statusProposalLabel(): ?string
    {
        if ($this->proposed_decommission === null) {
            return null;
        }
        return (int) $this->proposed_decommission === 1
            ? 'Usul non-aktifkan (pensiunkan) subdomain'
            : 'Usul aktifkan kembali subdomain';
    }

    // Ticket Generator
    public static function nextTicket(string $prefix = 'SUBDATA'): string
    {
        $ym = now()->format('ym');
        $base = $prefix . '-' . $ym . '-';

        return DB::transaction(function () use ($base, $prefix) {
            $last = self::where('ticket_number', 'like', $prefix . '-' . now()->format('ym') . '-%')
                ->orderByDesc('ticket_number')
                ->lockForUpdate()
                ->first();

            $lastNumber = $last ? intval(explode('-', $last->ticket_number)[2] ?? 0) : 0;
            $nextNumber = $lastNumber + 1;
            return $base . str_pad((string)$nextNumber, 4, '0', STR_PAD_LEFT);
        }, 1);
    }

    // Auto-generate ticket on create
    protected static function booted(): void
    {
        static::creating(function (SubdomainDataUpdateRequest $model) {
            if (empty($model->ticket_number)) {
                $model->ticket_number = self::nextTicket();
            }
        });
    }
}
