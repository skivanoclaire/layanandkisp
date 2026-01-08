<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VidconRequestActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'vidcon_request_id',
        'user_id',
        'action', // 'info_updated', 'status_changed', 'assigned', etc.
        'old_values',
        'new_values',
        'notes',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function vidconRequest()
    {
        return $this->belongsTo(VidconRequest::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
