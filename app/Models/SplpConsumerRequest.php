<?php

namespace App\Models;

use App\Models\Concerns\HasSplpWorkflow;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * V2 — Pendaftaran Akses Konsumen Layanan (Service Consumer).
 */
class SplpConsumerRequest extends Model
{
    use HasSplpWorkflow;

    public const REQUEST_TYPE = 'consumer';

    protected $fillable = [
        'ticket_no', 'user_id', 'nama', 'nip', 'unit_kerja_id', 'email_pemohon', 'no_hp',
        'splp_service_id', 'instansi_konsumen_id', 'is_eksternal',
        'ip_domain', 'estimasi_volume', 'volume_satuan', 'credential_pref', 'tujuan_penggunaan',
        'surat_permohonan_path', 'pks_path', 'hasil_uji_path', 'splp_consumer_id',
        'status', 'submitted_at', 'verif_admin_at', 'verif_teknis_at', 'decided_at', 'completed_at', 'rejected_at',
        'check_administrasi', 'check_koordinasi_opd', 'check_teknis', 'check_legalitas_data',
        'catatan_verifikasi', 'admin_notes', 'rejection_reason', 'consent_true',
    ];

    protected $casts = [
        'is_eksternal' => 'boolean',
        'submitted_at' => 'datetime',
        'verif_admin_at' => 'datetime',
        'verif_teknis_at' => 'datetime',
        'decided_at' => 'datetime',
        'completed_at' => 'datetime',
        'rejected_at' => 'datetime',
        'check_administrasi' => 'boolean',
        'check_koordinasi_opd' => 'boolean',
        'check_teknis' => 'boolean',
        'check_legalitas_data' => 'boolean',
        'consent_true' => 'boolean',
    ];

    public static function ticketPrefix(): string
    {
        return 'SPLP-KON';
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function unitKerja(): BelongsTo { return $this->belongsTo(UnitKerja::class); }
    public function service(): BelongsTo { return $this->belongsTo(SplpService::class, 'splp_service_id'); }
    public function instansiKonsumen(): BelongsTo { return $this->belongsTo(UnitKerja::class, 'instansi_konsumen_id'); }
    public function consumer(): BelongsTo { return $this->belongsTo(SplpConsumer::class, 'splp_consumer_id'); }

    public function logs(): HasMany
    {
        return $this->hasMany(SplpRequestLog::class, 'request_id')
            ->where('request_type', self::REQUEST_TYPE);
    }
}
