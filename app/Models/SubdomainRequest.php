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
        'admin_notes', 'rejection_reason', 'consent_true',
        'esc_answers', 'esc_total_score', 'esc_category', 'esc_document_path', 'esc_filled_at',
        'dc_data_name', 'dc_data_attributes', 'dc_confidentiality', 'dc_integrity', 'dc_availability', 'dc_total_score', 'dc_filled_at'
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
        'esc_answers' => 'array',
        'esc_filled_at' => 'datetime',
        'dc_filled_at' => 'datetime',
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

    // Electronic System Category Methods

    /**
     * Calculate total score from ESC answers
     */
    public function calculateEscScore(): int
    {
        if (!$this->esc_answers) {
            return 0;
        }

        $score = 0;
        $scoreMap = ['A' => 5, 'B' => 2, 'C' => 1];

        foreach ($this->esc_answers as $answer) {
            $score += $scoreMap[$answer] ?? 0;
        }

        return $score;
    }

    /**
     * Get ESC category based on score
     */
    public function getEscCategoryFromScore(int $score): string
    {
        if ($score >= 36) return 'Strategis';
        if ($score >= 16) return 'Tinggi';
        return 'Rendah';
    }

    /**
     * Update ESC score and category
     */
    public function updateEscScoreAndCategory(): void
    {
        $score = $this->calculateEscScore();
        $this->esc_total_score = $score;
        $this->esc_category = $this->getEscCategoryFromScore($score);
        $this->esc_filled_at = now();
        $this->save();
    }

    /**
     * Check if ESC questionnaire is completed
     */
    public function hasCompletedEsc(): bool
    {
        return !empty($this->esc_answers) && count($this->esc_answers) === 10;
    }

    /**
     * Get formatted ESC document name
     */
    public function getEscDocumentNameAttribute(): ?string
    {
        return $this->esc_document_path ? basename($this->esc_document_path) : null;
    }

    // Data Classification Methods

    /**
     * Calculate total score from Data Classification
     */
    public function calculateDcScore(): int
    {
        $scoreMap = ['Rendah' => 1, 'Sedang' => 3, 'Tinggi' => 5];

        $confidentiality = $scoreMap[$this->dc_confidentiality] ?? 0;
        $integrity = $scoreMap[$this->dc_integrity] ?? 0;
        $availability = $scoreMap[$this->dc_availability] ?? 0;

        return $confidentiality + $integrity + $availability;
    }

    /**
     * Update DC score
     */
    public function updateDcScore(): void
    {
        $this->dc_total_score = $this->calculateDcScore();
        $this->dc_filled_at = now();
        $this->save();
    }

    /**
     * Check if Data Classification is completed
     */
    public function hasCompletedDc(): bool
    {
        return !empty($this->dc_data_name)
            && !empty($this->dc_confidentiality)
            && !empty($this->dc_integrity)
            && !empty($this->dc_availability);
    }
}
