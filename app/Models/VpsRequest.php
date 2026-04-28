<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class VpsRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_no',
        'user_id',
        'nama',
        'nip',
        'unit_kerja_id',
        'vcpu',
        'jumlah_socket',
        'vcpu_per_socket',
        'ram_gb',
        'storage_gb',
        'keterangan',
        'ip_public',
        'username_vps',
        'password_vps',
        'os_vps',
        'keterangan_admin',
        'status',
        'processed_by',
        'admin_notes',
        'processing_at',
        'completed_at',
        'rejected_at',
    ];

    protected $casts = [
        'storage_gb' => 'integer',
        'processing_at' => 'datetime',
        'completed_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($vpsRequest) {
            if (empty($vpsRequest->ticket_no)) {
                $vpsRequest->ticket_no = self::generateTicketNo();
            }
        });
    }

    public static function generateTicketNo()
    {
        $prefix = 'VPS';
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

    public function setPlainUsernameVps(?string $plain): void
    {
        $this->username_vps = $plain ? Crypt::encryptString($plain) : null;
    }

    public function setPlainPasswordVps(?string $plain): void
    {
        $this->password_vps = $plain ? Crypt::encryptString($plain) : null;
    }

    public function getPlainUsernameVps(): ?string
    {
        return $this->decryptCredential($this->username_vps);
    }

    public function getPlainPasswordVps(): ?string
    {
        return $this->decryptCredential($this->password_vps);
    }

    private function decryptCredential(?string $value): ?string
    {
        if (!$value) {
            return null;
        }
        try {
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            return $value;
        }
    }
}
