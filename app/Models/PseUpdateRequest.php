<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class PseUpdateRequest extends Model
{
    protected $fillable = [
        'ticket_no',
        'user_id',
        'web_monitor_id',
        'update_esc',
        'update_dc',
        // ESC fields
        'esc_answers',
        'esc_total_score',
        'esc_category',
        'esc_document_path',
        // DC fields
        'dc_data_name',
        'dc_data_attributes',
        'dc_confidentiality',
        'dc_integrity',
        'dc_availability',
        'dc_total_score',
        // Status & workflow
        'status',
        'submitted_at',
        'processing_at',
        'approved_at',
        'rejected_at',
        // Admin tracking
        'processed_by',
        'approved_by',
        'rejected_by',
        // Revision mechanism
        'revision_notes',
        'revision_requested_by',
        'revision_requested_at',
        // Admin notes
        'admin_notes',
        'rejection_reason',
    ];

    protected $casts = [
        'update_esc' => 'boolean',
        'update_dc' => 'boolean',
        'esc_answers' => 'array',
        'submitted_at' => 'datetime',
        'processing_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'revision_requested_at' => 'datetime',
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

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function revisionRequestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revision_requested_by');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(PseUpdateRequestLog::class);
    }

    // Ticket Generator

    /**
     * Generate next ticket number with format PSE-YYYYMM-0001
     */
    public static function nextTicket(): string
    {
        $ym = now()->format('Ym');
        $prefix = 'PSE';
        $base = $prefix . '-' . $ym . '-';

        return DB::transaction(function () use ($base, $prefix, $ym) {
            $last = self::where('ticket_no', 'like', $prefix . '-' . $ym . '-%')
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
        static::creating(function (PseUpdateRequest $model) {
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

    // Status Helper Methods

    /**
     * Check if request can be edited (draft or perlu_revisi)
     */
    public function canBeEdited(): bool
    {
        return in_array($this->status, ['draft', 'perlu_revisi']);
    }

    /**
     * Check if request can be submitted (draft only)
     */
    public function canBeSubmitted(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Check if request needs revision
     */
    public function needsRevision(): bool
    {
        return $this->status === 'perlu_revisi';
    }
}
