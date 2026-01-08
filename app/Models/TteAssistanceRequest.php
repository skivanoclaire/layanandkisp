<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TteAssistanceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_no',
        'user_id',
        'nama',
        'nip',
        'email_resmi',
        'instansi',
        'jabatan',
        'no_hp',
        'waktu_pendampingan',
        'surat_permohonan_path',
        'status',
        'admin_notes',
        'keterangan_admin',
        'processed_by',
    ];

    protected $casts = [
        'waktu_pendampingan' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->ticket_no)) {
                $model->ticket_no = self::generateTicketNumber();
            }
        });
    }

    public static function generateTicketNumber()
    {
        $prefix = 'TKT-TTE-ASSIST';
        $yearMonth = date('Ym');
        $lastTicket = self::where('ticket_no', 'like', "{$prefix}-{$yearMonth}-%")
            ->orderBy('ticket_no', 'desc')
            ->first();

        if ($lastTicket) {
            $lastNumber = (int) substr($lastTicket->ticket_no, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return "{$prefix}-{$yearMonth}-{$newNumber}";
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function getStatusBadgeClass()
    {
        return match($this->status) {
            'menunggu' => 'bg-yellow-100 text-yellow-800',
            'diproses' => 'bg-blue-100 text-blue-800',
            'selesai' => 'bg-green-100 text-green-800',
            'ditolak' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getStatusLabel()
    {
        return match($this->status) {
            'menunggu' => 'Menunggu',
            'diproses' => 'Diproses',
            'selesai' => 'Selesai',
            'ditolak' => 'Ditolak',
            default => ucfirst($this->status),
        };
    }

    public function scopePending($query)
    {
        return $query->where('status', 'menunggu');
    }

    public function scopeProcessing($query)
    {
        return $query->where('status', 'diproses');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'selesai');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'ditolak');
    }
}
