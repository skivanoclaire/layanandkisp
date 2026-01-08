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
        'judul_aplikasi',
        'dasar_hukum',
        'permasalahan_kebutuhan',
        'pihak_terkait',
        'pemilik_proses_bisnis_id',
        'stakeholder_internal',
        'stakeholder_eksternal',
        'maksud_tujuan',
        'ruang_lingkup',
        'analisis_biaya_manfaat',
        'analisis_risiko',
        'target_waktu',
        'sasaran_pengguna',
        'lokasi_implementasi',
        'perencanaan_ruang_lingkup',
        'perencanaan_proses_bisnis',
        'kerangka_kerja',
        'pelaksana_pembangunan',
        'peran_tanggung_jawab',
        'jadwal_pelaksanaan',
        'rencana_aksi',
        'keamanan_informasi',
        'sumber_daya',
        'indikator_keberhasilan',
        'alih_pengetahuan',
        'pemantauan_pelaporan',
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
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'revision_requested_at' => 'datetime',
        'stakeholder_internal' => 'array',
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
}
