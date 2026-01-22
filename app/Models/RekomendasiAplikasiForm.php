<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class RekomendasiAplikasiForm extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_number',
        'user_id',
        'pemilik_proses_bisnis_id',
        'status',
        'admin_feedback',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'pdf_path',
        'letter_number',
        'verification_code',
        'revision_notes',
        'revision_requested_by',
        'revision_requested_at',
        // V2 fields
        'nama_aplikasi',
        'prioritas',
        'deskripsi',
        'tujuan',
        'manfaat',
        'jenis_layanan',
        'target_pengguna',
        'estimasi_pengguna',
        'lingkup_aplikasi',
        'platform',
        'teknologi_diusulkan',
        'estimasi_waktu_pengembangan',
        'estimasi_biaya',
        'sumber_pendanaan',
        'integrasi_sistem_lain',
        'detail_integrasi',
        'kebutuhan_khusus',
        'dampak_tidak_dibangun',
        'risiko_items',
        // Permenkomdigi No. 6 Tahun 2025 - Analisis Kebutuhan
        'dasar_hukum',
        'uraian_permasalahan',
        'pihak_terkait',
        'ruang_lingkup',
        'analisis_biaya_manfaat',
        'lokasi_implementasi',
        // Permenkomdigi No. 6 Tahun 2025 - Perencanaan
        'uraian_ruang_lingkup',
        'proses_bisnis',
        'proses_bisnis_file',
        'kerangka_kerja',
        'pelaksana_pembangunan',
        'peran_tanggung_jawab',
        'jadwal_pelaksanaan',
        'rencana_aksi',
        'keamanan_informasi',
        'sumber_daya_manusia',
        'sumber_daya_anggaran',
        'sumber_daya_sarana',
        'indikator_keberhasilan',
        'alih_pengetahuan',
        'pemantauan_pelaporan',
        // Fase & Deployment
        'fase_saat_ini',
        'repository_url',
        'url_aplikasi_staging',
        'url_aplikasi_production',
        'ip_address_server',
        'domain_aplikasi',
        'spesifikasi_server',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'revision_requested_at' => 'datetime',
        'platform' => 'array',
        'risiko_items' => 'array',
        'estimasi_pengguna' => 'integer',
        'estimasi_waktu_pengembangan' => 'integer',
        'estimasi_biaya' => 'decimal:2',
    ];

    /**
     * Generate next ticket number safely with database locking
     * Format: TKT-REK-APL-YYYYMM0001
     */
    public static function nextTicketNumber(): string
    {
        $prefix = 'TKT-REK-APL-' . now()->format('Ym');

        // Use transaction with pessimistic locking to prevent race conditions
        return DB::transaction(function () use ($prefix) {
            // Get last ticket for current month with row lock
            $last = self::where('ticket_number', 'like', $prefix . '%')
                ->orderByDesc('ticket_number')
                ->lockForUpdate()
                ->first();

            $lastNumber = 0;
            if ($last) {
                // Extract the 4-digit suffix as the running number
                $suffix = substr($last->ticket_number, strlen($prefix));
                $lastNumber = intval($suffix);
            }

            $nextNumber = $lastNumber + 1;
            return $prefix . str_pad((string)$nextNumber, 4, '0', STR_PAD_LEFT);
        }, 1); // Retry once on deadlock
    }

    protected static function booted()
    {
        static::creating(function ($form) {
            if (empty($form->ticket_number)) {
                $form->ticket_number = self::nextTicketNumber();
            }
        });

        static::created(function ($form) {
            // Auto-create verifikasi record when proposal is created with status 'diajukan'
            if ($form->status === 'diajukan' && !$form->verifikasi) {
                RekomendasiVerifikasi::create([
                    'rekomendasi_aplikasi_form_id' => $form->id,
                    'verifikator_id' => null,
                    'status' => 'menunggu',
                ]);
            }
        });

        static::updated(function ($form) {
            // Handle verifikasi record when proposal status changes to 'diajukan'
            if ($form->isDirty('status') && $form->status === 'diajukan') {
                if ($form->verifikasi) {
                    // Reset existing verifikasi record
                    $form->verifikasi->update([
                        'verifikator_id' => null,
                        'status' => 'menunggu',
                        'checklist_analisis_kebutuhan' => false,
                        'checklist_perencanaan' => false,
                        'checklist_manajemen_risiko' => false,
                        'checklist_anggaran' => false,
                        'checklist_timeline' => false,
                        'catatan_verifikasi' => null,
                        'tanggal_verifikasi' => null,
                    ]);
                } else {
                    // Create new verifikasi record if it doesn't exist
                    RekomendasiVerifikasi::create([
                        'rekomendasi_aplikasi_form_id' => $form->id,
                        'verifikator_id' => null,
                        'status' => 'menunggu',
                    ]);
                }
            }
        });
    }

    public function risikoItems()
    {
        return $this->hasMany(RekomendasiRisikoItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejectedBy()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function revisionRequestedBy()
    {
        return $this->belongsTo(User::class, 'revision_requested_by');
    }

    public function pemilikProsesBisnis()
    {
        return $this->belongsTo(UnitKerja::class, 'pemilik_proses_bisnis_id');
    }

    /**
     * Alias for pemilikProsesBisnis (for convenience).
     */
    public function unitKerja()
    {
        return $this->pemilikProsesBisnis();
    }

    // V2 Relationships

    /**
     * Get all dokumen usulan for this form.
     */
    public function dokumenUsulan()
    {
        return $this->hasMany(RekomendasiDokumenUsulan::class);
    }

    /**
     * Get the verifikasi record.
     */
    public function verifikasi()
    {
        return $this->hasOne(RekomendasiVerifikasi::class);
    }

    /**
     * Get the surat rekomendasi.
     */
    public function surat()
    {
        return $this->hasOne(RekomendasiSurat::class);
    }

    /**
     * Get the status kementerian directly (without surat).
     */
    public function statusKementerian()
    {
        return $this->hasOne(RekomendasiStatusKementerian::class, 'rekomendasi_aplikasi_form_id');
    }

    /**
     * Get all fase pengembangan records.
     */
    public function fasePengembangan()
    {
        return $this->hasMany(RekomendasiFasePengembangan::class);
    }

    /**
     * Get all fase pengembangan dokumen.
     */
    public function fasePengembanganDokumen()
    {
        return $this->hasMany(FasePengembanganDokumen::class, 'rekomendasi_aplikasi_form_id');
    }

    /**
     * Get the current fase pengembangan that is in progress.
     */
    public function currentFase()
    {
        return $this->hasOne(RekomendasiFasePengembangan::class)
            ->where('status', 'sedang_berjalan')
            ->latestOfMany();
    }

    /**
     * Get all tim pengembangan members.
     */
    public function timPengembangan()
    {
        return $this->hasMany(RekomendasiTimPengembangan::class);
    }

    /**
     * Get all evaluasi records.
     */
    public function evaluasi()
    {
        return $this->hasMany(RekomendasiEvaluasi::class);
    }

    /**
     * Get the latest evaluasi.
     */
    public function latestEvaluasi()
    {
        return $this->hasOne(RekomendasiEvaluasi::class)
            ->latestOfMany('tanggal_evaluasi');
    }

    /**
     * Get all histori aktivitas.
     */
    public function histori()
    {
        return $this->hasMany(RekomendasiHistoriAktivitas::class);
    }

    /**
     * Alias for histori relationship (for backward compatibility).
     */
    public function historiAktivitas()
    {
        return $this->histori();
    }

    /**
     * Log an activity for this form.
     */
    public function logActivity(string $aktivitas, ?string $deskripsi = null, ?int $userId = null): RekomendasiHistoriAktivitas
    {
        return RekomendasiHistoriAktivitas::log(
            $this->id,
            $aktivitas,
            $deskripsi,
            $userId
        );
    }

    /**
     * Get fase saat ini display name.
     */
    public function getFaseSaatIniDisplayAttribute(): string
    {
        return match($this->fase_saat_ini) {
            'usulan' => 'Pengajuan Usulan',
            'verifikasi' => 'Verifikasi Diskominfo',
            'penandatanganan' => 'Menunggu Tanda Tangan',
            'menunggu_kementerian' => 'Menunggu Persetujuan Kementerian',
            'pengembangan' => 'Fase Pengembangan',
            'selesai' => 'Selesai',
            'ditolak' => 'Ditolak',
            default => $this->fase_saat_ini ?? 'usulan',
        };
    }

    /**
     * Get fase badge color.
     */
    public function getFaseBadgeColorAttribute(): string
    {
        return match($this->fase_saat_ini) {
            'usulan' => 'info',
            'verifikasi' => 'warning',
            'penandatanganan' => 'primary',
            'menunggu_kementerian' => 'secondary',
            'pengembangan' => 'success',
            'selesai' => 'success',
            'ditolak' => 'danger',
            default => 'secondary',
        };
    }
}
