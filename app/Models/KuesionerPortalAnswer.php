<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KuesionerPortalAnswer extends Model
{
    protected $table = 'kuesioner_portal_answers';

    protected $fillable = [
        'response_id',
        'kode',
        'kepentingan',
        'kinerja',
        'kano_fungsional',
        'kano_disfungsional',
    ];

    protected $casts = [
        'kepentingan' => 'integer',
        'kinerja' => 'integer',
        'kano_fungsional' => 'integer',
        'kano_disfungsional' => 'integer',
    ];

    public function response(): BelongsTo
    {
        return $this->belongsTo(KuesionerPortalResponse::class, 'response_id');
    }
}
