<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TikCategory extends Model
{
    protected $fillable = ['name','code','description'];

    public function assets() {
        return $this->hasMany(TikAsset::class);
    }
}
