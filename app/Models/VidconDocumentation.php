<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class VidconDocumentation extends Model
{
    protected $fillable = [
        'vidcon_data_id',
        'uploaded_by',
        'file_path',
        'file_name',
        'caption',
        'keterangan',
    ];

    /**
     * Get the vidcon data that owns this documentation
     */
    public function vidconData()
    {
        return $this->belongsTo(VidconData::class, 'vidcon_data_id');
    }

    /**
     * Get the user who uploaded this documentation
     */
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get the full URL for the image (cross-platform compatible)
     */
    public function getImageUrlAttribute()
    {
        return Storage::url($this->file_path);
    }
}
