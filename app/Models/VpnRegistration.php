<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VpnRegistration extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_no',
        'user_id',
        'nama',
        'nip',
        'unit_kerja_id',
        'uraian_kebutuhan',
        'tipe',
        'bandwidth',
        'username_vpn',
        'password_vpn',
        'ip_vpn',
        'keterangan_admin',
        'status',
        'processed_by',
        'admin_notes',
        'processing_at',
        'completed_at',
        'rejected_at',
    ];

    protected $casts = [
        'processing_at' => 'datetime',
        'completed_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($vpnRegistration) {
            if (empty($vpnRegistration->ticket_no)) {
                $vpnRegistration->ticket_no = self::generateTicketNo();
            }
        });
    }

    public static function generateTicketNo()
    {
        $prefix = 'VR';
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
