<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Visitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_no',
        'user_id',
        'nama',
        'nip',
        'unit_kerja_id',
        'tujuan_kunjungan',
        'nama_aset',
        'nomor_aset',
        'catatan_aset',
        'tanggal_kunjungan',
        'jam_mulai',
        'jam_selesai',
        'keterangan',
        'keterangan_admin',
        'status',
        'processed_by',
        'admin_notes',
        'approved_at',
        'rejected_at',
        'completed_at',
    ];

    protected $casts = [
        'tanggal_kunjungan' => 'date',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($visitation) {
            if (empty($visitation->ticket_no)) {
                $visitation->ticket_no = self::generateTicketNo();
            }
        });
    }

    public static function generateTicketNo()
    {
        $prefix = 'VST';
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
}
