<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class VpnRegistration extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_no',
        'user_id',
        'nama',
        'nip',
        'is_kabupaten_kota',
        'kabupaten_kota',
        'unit_kerja_manual',
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
        'is_kabupaten_kota' => 'boolean',
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

    public function logs()
    {
        return $this->hasMany(VpnRegistrationLog::class)->orderBy('created_at', 'desc');
    }

    public function setPlainUsernameVpn(?string $plain): void
    {
        $this->username_vpn = $plain ? Crypt::encryptString($plain) : null;
    }

    public function setPlainPasswordVpn(?string $plain): void
    {
        $this->password_vpn = $plain ? Crypt::encryptString($plain) : null;
    }

    public function getPlainUsernameVpn(): ?string
    {
        return $this->decryptCredential($this->username_vpn);
    }

    public function getPlainPasswordVpn(): ?string
    {
        return $this->decryptCredential($this->password_vpn);
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
