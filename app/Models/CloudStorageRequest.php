<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CloudStorageRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_no',
        'user_id',
        'nama',
        'nip',
        'unit_kerja_id',
        'kapasitas_gb',
        'kapasitas_disetujui_gb',
        'tipe',
        'keterangan',
        'akses_url',
        'username',
        'password',
        'keterangan_admin',
        'status',
        'processed_by',
        'admin_notes',
        'processing_at',
        'completed_at',
        'rejected_at',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'password' => 'encrypted',
        'processing_at' => 'datetime',
        'completed_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($cloudStorageRequest) {
            if (empty($cloudStorageRequest->ticket_no)) {
                $cloudStorageRequest->ticket_no = self::generateTicketNo();
            }
        });
    }

    public static function generateTicketNo()
    {
        $prefix = 'CLD';
        $yearMonth = date('Ym');
        $latest = self::where('ticket_no', 'like', "$prefix-$yearMonth-%")
            ->orderBy('ticket_no', 'desc')
            ->first();

        if ($latest) {
            $lastNumber = (int) substr($latest->ticket_no, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return "$prefix-$yearMonth-$newNumber";
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function unitKerja()
    {
        return $this->belongsTo(UnitKerja::class);
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function kapasitasRevisis()
    {
        return $this->hasMany(CloudStorageKapasitasRevisi::class)->latest('id');
    }

    /**
     * Set kapasitas yang disetujui; jika berbeda dari nilai sebelumnya
     * (atau dari yang diminta, untuk penetapan pertama) dicatat sebagai revisi.
     */
    public function setKapasitasDisetujui(int $kapasitasBaru, ?string $alasan, ?int $adminId): void
    {
        $kapasitasLama = $this->kapasitas_disetujui_gb ?? $this->kapasitas_gb;

        $this->kapasitas_disetujui_gb = $kapasitasBaru;
        $this->save();

        if ($kapasitasBaru !== (int) $kapasitasLama) {
            $this->kapasitasRevisis()->create([
                'kapasitas_lama_gb' => $kapasitasLama,
                'kapasitas_baru_gb' => $kapasitasBaru,
                'alasan' => $alasan,
                'revised_by' => $adminId,
            ]);
        }
    }

    /** Kapasitas yang berlaku: yang disetujui admin, atau yang diminta jika belum ditetapkan. */
    public function getKapasitasBerlakuGbAttribute(): int
    {
        return $this->kapasitas_disetujui_gb ?? $this->kapasitas_gb;
    }
}
