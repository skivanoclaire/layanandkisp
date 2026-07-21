<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SlaHoliday extends Model
{
    public const SUMBER_MANUAL = 'manual';
    public const SUMBER_IMPORT = 'import';

    public const JENIS_LIBUR_NASIONAL = 'libur_nasional';
    public const JENIS_CUTI_BERSAMA = 'cuti_bersama';

    protected $table = 'sla_holidays';

    protected $fillable = [
        'tanggal',
        'keterangan',
        'sumber',
        'jenis',
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
