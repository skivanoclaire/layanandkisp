<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class SubdomainRequest extends Model
{
    protected $fillable = [
        'ticket_no', 'user_id', 'nama', 'nip', 'unit_kerja_id', 'email_pemohon', 'no_hp',
        'subdomain_requested', 'ip_address', 'jenis_website', 'purpose', 'description',
        'nama_aplikasi', 'latar_belakang', 'manfaat_aplikasi', 'tahun_pembuatan', 'developer', 'contact_person', 'contact_phone',
        'programming_language_id', 'other_programming_language', 'programming_language_version',
        'framework_id', 'other_framework', 'framework_version',
        'database_id', 'database_version', 'frontend_tech',
        'backup_frequency', 'backup_retention', 'has_bcp', 'has_drp', 'rto', 'maintenance_schedule', 'has_https',
        'server_ownership', 'server_owner_name', 'server_location_id',
        'needs_ssl', 'needs_proxy', 'cloudflare_record_id', 'is_proxied',
        'web_monitor_id', 'status', 'submitted_at', 'processing_at', 'rejected_at', 'completed_at',
        'admin_notes', 'rejection_reason', 'consent_true'
    ];

    protected $casts = [
        'consent_true' => 'boolean',
        'has_https' => 'boolean',
        'needs_ssl' => 'boolean',
        'needs_proxy' => 'boolean',
        'is_proxied' => 'boolean',
        'submitted_at' => 'datetime',
        'processing_at' => 'datetime',
        'rejected_at' => 'datetime',
        'completed_at' => 'datetime',
        'tahun_pembuatan' => 'integer',
    ];

    // Relations
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function unitKerja(): BelongsTo { return $this->belongsTo(UnitKerja::class); }
    public function programmingLanguage(): BelongsTo { return $this->belongsTo(ProgrammingLanguage::class); }
    public function framework(): BelongsTo { return $this->belongsTo(Framework::class); }
    public function database(): BelongsTo { return $this->belongsTo(Database::class); }
    public function serverLocation(): BelongsTo { return $this->belongsTo(ServerLocation::class); }
    public function webMonitor(): BelongsTo { return $this->belongsTo(WebMonitor::class); }
    public function logs(): HasMany { return $this->hasMany(SubdomainRequestLog::class); }
    public function surveiKepuasan(): HasMany { return $this->hasMany(SurveiKepuasanLayanan::class, 'subdomain_request_id'); }

    // Scopes
    public function scopeByStatus($query, string $status) { return $query->where('status', $status); }
    public function scopePending($query) { return $query->where('status', 'menunggu'); }
    public function scopeCompleted($query) { return $query->where('status', 'selesai'); }

    // Accessor
    public function getFullDomainAttribute(): string
    {
        return $this->subdomain_requested . '.kaltaraprov.go.id';
    }

    // Ticket Generator
    public static function nextTicket(string $prefix = 'SUB'): string
    {
        $ym = now()->format('ym');
        $base = $prefix . '-' . $ym . '-';

        return DB::transaction(function () use ($base, $prefix) {
            $last = self::where('ticket_no', 'like', $prefix . '-' . now()->format('ym') . '-%')
                ->orderByDesc('ticket_no')
                ->lockForUpdate()
                ->first();

            $lastNumber = $last ? intval(explode('-', $last->ticket_no)[2] ?? 0) : 0;
            $nextNumber = $lastNumber + 1;
            return $base . str_pad((string)$nextNumber, 4, '0', STR_PAD_LEFT);
        }, 1);
    }

    protected static function booted(): void
    {
        static::creating(function (SubdomainRequest $model) {
            if (empty($model->ticket_no)) {
                $model->ticket_no = self::nextTicket();
            }
        });
    }
}
