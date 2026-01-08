<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GoogleAsetTikKategori extends Model
{
    use HasFactory;

    protected $table = 'google_aset_tik_kategori';

    protected $fillable = [
        'nama_perangkat',
        'kategori_perangkat',
    ];

    public static function getKategori(string $namaPerangkat): ?string
    {
        $kategori = self::where('nama_perangkat', $namaPerangkat)->first();
        return $kategori ? $kategori->kategori_perangkat : null;
    }
}
