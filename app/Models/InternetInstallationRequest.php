<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InternetInstallationRequest extends Model
{
    protected $table = 'internet_installation_requests';

    protected $fillable = [
        'ticket_no',
        'user_id',
        'nama',
        'nip',
        'no_hp',
        'unit_kerja_id',
        'lokasi_pemasangan',
        'ruangan',
        'pic_lokasi',
        'jumlah_titik',
        'uraian_kebutuhan',
        'status',
        'processed_by',
        'admin_notes',
        'processing_at',
        'completed_at',
        'rejected_at',
    ];

    protected $casts = [
        'jumlah_titik' => 'integer',
        'processing_at' => 'datetime',
        'completed_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function unitKerja()
    {
        return $this->belongsTo(UnitKerja::class, 'unit_kerja_id');
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    protected static function booted(): void
    {
        static::creating(function (InternetInstallationRequest $model) {
            if (empty($model->ticket_no)) {
                $year = now()->year;
                $month = now()->format('m');

                $last = self::whereYear('created_at', $year)
                    ->whereMonth('created_at', $month)
                    ->orderByDesc('id')
                    ->first();

                $sequence = $last ? (int) substr($last->ticket_no, -4) + 1 : 1;
                $model->ticket_no = 'PB-' . $year . $month . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
            }
        });
    }
}
