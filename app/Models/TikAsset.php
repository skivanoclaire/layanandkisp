<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class TikAsset extends Model
{
    protected $fillable = [
        'tik_category_id','name','code','serial_number','quantity',
        'condition','location','photo_path','notes','is_active',
        'qr_text','qr_path'
    ];

    public function category() {
        return $this->belongsTo(TikCategory::class, 'tik_category_id');
    }

    // Helper URL foto
    public function getPhotoUrlAttribute(): ?string {
        return $this->photo_path ? asset('storage/' . $this->photo_path) : null;
    }
}
