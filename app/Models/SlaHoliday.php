<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SlaHoliday extends Model
{
    protected $table = 'sla_holidays';

    protected $fillable = [
        'tanggal',
        'keterangan',
        'created_by',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
