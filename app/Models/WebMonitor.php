<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebMonitor extends Model
{
    use HasFactory;

    protected $table = 'web_monitors';

    protected $fillable = [
        'nama_instansi',
        'subdomain',
        'status',
        'keterangan',
        'jenis',
    ];
}
