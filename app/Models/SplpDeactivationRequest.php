<?php

namespace App\Models;

use App\Models\Concerns\HasSplpWorkflow;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * V5 — Penonaktifan / Pencabutan Endpoint SPLP.
 */
class SplpDeactivationRequest extends Model
{
    use HasSplpWorkflow;

    public const REQUEST_TYPE = 'deactivation';

    protected $fillable = [
        'ticket_no', 'user_id', 'nama', 'nip', 'unit_kerja_id', 'email_pemohon', 'no_hp',
        'target_type', 'splp_service_id', 'splp_consumer_id', 'jenis_tindakan', 'alasan', 'is_darurat', 'surat_path',
        'status', 'submitted_at', 'verif_admin_at', 'verif_teknis_at', 'decided_at', 'completed_at', 'rejected_at',
        'check_administrasi', 'check_dampak',
        'catatan_verifikasi', 'admin_notes', 'rejection_reason', 'consent_true',
    ];

    protected $casts = [
        'is_darurat' => 'boolean',
        'submitted_at' => 'datetime',
        'verif_admin_at' => 'datetime',
        'verif_teknis_at' => 'datetime',
        'decided_at' => 'datetime',
        'completed_at' => 'datetime',
        'rejected_at' => 'datetime',
        'check_administrasi' => 'boolean',
        'check_dampak' => 'boolean',
        'consent_true' => 'boolean',
    ];

    public static function ticketPrefix(): string
    {
        return 'SPLP-NON';
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function unitKerja(): BelongsTo { return $this->belongsTo(UnitKerja::class); }
    public function service(): BelongsTo { return $this->belongsTo(SplpService::class, 'splp_service_id'); }
    public function consumer(): BelongsTo { return $this->belongsTo(SplpConsumer::class, 'splp_consumer_id'); }

    public function logs(): HasMany
    {
        return $this->hasMany(SplpRequestLog::class, 'request_id')
            ->where('request_type', self::REQUEST_TYPE);
    }
}
