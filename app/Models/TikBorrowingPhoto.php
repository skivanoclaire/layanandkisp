<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TikBorrowingPhoto extends Model
{
    protected $fillable = ['tik_borrowing_id', 'phase', 'path'];

    public function borrowing()
    {
        return $this->belongsTo(TikBorrowing::class, 'tik_borrowing_id');
    }
}
