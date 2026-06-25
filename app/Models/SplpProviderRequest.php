<?php

namespace App\Models;

use App\Models\Concerns\HasSplpWorkflow;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * V1 — Pendaftaran Endpoint Penyedia Layanan (Service Provider).
 */
class SplpProviderRequest extends Model
{
    use HasSplpWorkflow;

    public const REQUEST_TYPE = 'provider';

    protected $fillable = [
        'ticket_no', 'user_id', 'nama', 'nip', 'unit_kerja_id', 'email_pemohon', 'no_hp',
        'nama_layanan', 'deskripsi', 'backend_url', 'route_path', 'auth_type', 'klasifikasi_data',
        'dc_confidentiality', 'dc_integrity', 'dc_availability',
        'surat_permohonan_path', 'openapi_doc_path', 'splp_service_id',
        'status', 'submitted_at', 'verif_admin_at', 'verif_teknis_at', 'decided_at', 'completed_at', 'rejected_at',
        'check_administrasi', 'check_teknis', 'check_dokumentasi', 'check_klasifikasi_data',
        'catatan_verifikasi', 'admin_notes', 'rejection_reason', 'consent_true',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'verif_admin_at' => 'datetime',
        'verif_teknis_at' => 'datetime',
        'decided_at' => 'datetime',
        'completed_at' => 'datetime',
        'rejected_at' => 'datetime',
        'check_administrasi' => 'boolean',
        'check_teknis' => 'boolean',
        'check_dokumentasi' => 'boolean',
        'check_klasifikasi_data' => 'boolean',
        'consent_true' => 'boolean',
    ];

    public static function ticketPrefix(): string
    {
        return 'SPLP-PEN';
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function unitKerja(): BelongsTo { return $this->belongsTo(UnitKerja::class); }
    public function service(): BelongsTo { return $this->belongsTo(SplpService::class, 'splp_service_id'); }

    public function logs(): HasMany
    {
        return $this->hasMany(SplpRequestLog::class, 'request_id')
            ->where('request_type', self::REQUEST_TYPE);
    }
}
