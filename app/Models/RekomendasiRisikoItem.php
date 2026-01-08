<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RekomendasiRisikoItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'rekomendasi_aplikasi_form_id',
        'jenis_risiko',
        'kategori_risiko_spbe',
        'area_dampak_risiko_spbe',
        'uraian_risiko',
        'penyebab',
        'dampak',
        'level_kemungkinan',
        'level_dampak',
        'besaran_risiko',
        'perlu_penanganan',
        'opsi_penanganan',
        'rencana_aksi',
        'jadwal_implementasi',
        'penanggung_jawab',
        'risiko_residual',
    ];

    public function form()
    {
        return $this->belongsTo(RekomendasiAplikasiForm::class, 'rekomendasi_aplikasi_form_id');
    }
}
