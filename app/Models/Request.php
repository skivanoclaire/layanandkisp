<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Request extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'service',
        'file',
        'status',
        'ticket_number',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
}
