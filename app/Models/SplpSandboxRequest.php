<?php

namespace App\Models;

use App\Models\Concerns\HasSplpWorkflow;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * V3 — Permohonan Uji Coba (Sandbox) Integrasi SPLP.
 */
class SplpSandboxRequest extends Model
{
    use HasSplpWorkflow;

    public const REQUEST_TYPE = 'sandbox';

    protected $fillable = [
        'ticket_no', 'user_id', 'nama', 'nip', 'unit_kerja_id', 'email_pemohon', 'no_hp',
        'nama_layanan', 'spesifikasi_draft', 'masa_uji_hari', 'spesifikasi_file_path',
        'splp_service_id', 'splp_consumer_id',
        'status', 'submitted_at', 'verif_admin_at', 'verif_teknis_at', 'decided_at', 'completed_at', 'rejected_at',
        'check_administrasi', 'check_spesifikasi', 'check_sumberdaya',
        'catatan_verifikasi', 'admin_notes', 'rejection_reason', 'consent_true',
    ];

    protected $casts = [
        'masa_uji_hari' => 'integer',
        'submitted_at' => 'datetime',
        'verif_admin_at' => 'datetime',
        'verif_teknis_at' => 'datetime',
        'decided_at' => 'datetime',
        'completed_at' => 'datetime',
        'rejected_at' => 'datetime',
        'check_administrasi' => 'boolean',
        'check_spesifikasi' => 'boolean',
        'check_sumberdaya' => 'boolean',
        'consent_true' => 'boolean',
    ];

    public static function ticketPrefix(): string
    {
        return 'SPLP-SBX';
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
