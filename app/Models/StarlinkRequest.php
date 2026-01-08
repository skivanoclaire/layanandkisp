<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StarlinkRequest extends Model
{
    protected $table = 'starlink_requests';

    protected $fillable = [
        'ticket_no',
        'user_id',
        'nama',
        'nip',
        'no_hp',
        'unit_kerja_id',
        'uraian_kegiatan',
        'tanggal_mulai',
        'tanggal_selesai',
        'jam_mulai',
        'jam_selesai',
        'status',
        'processed_by',
        'admin_notes',
        'processing_at',
        'completed_at',
        'rejected_at',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'processing_at' => 'datetime',
        'completed_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    /**
     * Get the user who created this request
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the unit kerja
     */
    public function unitKerja()
    {
        return $this->belongsTo(UnitKerja::class, 'unit_kerja_id');
    }

    /**
     * Get the user who processed this request
     */
    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Boot method to auto-generate ticket number
     */
    protected static function booted(): void
    {
        static::creating(function (StarlinkRequest $model) {
            if (empty($model->ticket_no)) {
                $year = now()->year;
                $month = now()->format('m');

                $lastTicket = self::whereYear('created_at', $year)
                    ->whereMonth('created_at', $month)
                    ->orderByDesc('id')
                    ->first();

                $sequence = $lastTicket ? (int) substr($lastTicket->ticket_no, -4) + 1 : 1;
                $model->ticket_no = 'SL-' . $year . $month . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
            }
        });
    }
}
