<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CloudStorageKapasitasRevisi extends Model
{
    protected $fillable = [
        'cloud_storage_request_id',
        'kapasitas_lama_gb',
        'kapasitas_baru_gb',
        'alasan',
        'revised_by',
    ];

    public function cloudStorageRequest()
    {
        return $this->belongsTo(CloudStorageRequest::class);
    }

    public function revisedBy()
    {
        return $this->belongsTo(User::class, 'revised_by');
    }
}
