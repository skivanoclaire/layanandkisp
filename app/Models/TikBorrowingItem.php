<?php

// app/Models/TikBorrowingItem.php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class TikBorrowingItem extends Model
{
    protected $fillable = ['tik_borrowing_id','tik_asset_id','qty'];
    public function borrowing(){ return $this->belongsTo(TikBorrowing::class,'tik_borrowing_id'); }
    public function asset(){ return $this->belongsTo(TikAsset::class,'tik_asset_id'); }
}

// app/Models/TikBorrowingPhoto.php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class TikBorrowingPhoto extends Model
{
    protected $fillable = ['tik_borrowing_id','phase','path'];
    public function borrowing(){ return $this->belongsTo(TikBorrowing::class,'tik_borrowing_id'); }
}