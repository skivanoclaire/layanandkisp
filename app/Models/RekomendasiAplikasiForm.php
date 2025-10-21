<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RekomendasiAplikasiForm extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_number',
        'user_id',
        'judul_aplikasi',
        'dasar_hukum',
        'permasalahan_kebutuhan',
        'pihak_terkait',
        'maksud_tujuan',
        'ruang_lingkup',
        'analisis_biaya_manfaat',
        'analisis_risiko',
        'target_waktu',
        'sasaran_pengguna',
        'lokasi_implementasi',
        'perencanaan_ruang_lingkup',
        'perencanaan_proses_bisnis',
        'kerangka_kerja',
        'pelaksana_pembangunan',
        'peran_tanggung_jawab',
        'jadwal_pelaksanaan',
        'rencana_aksi',
        'keamanan_informasi',
        'sumber_daya',
        'indikator_keberhasilan',
        'alih_pengetahuan',
        'pemantauan_pelaporan',
        'status',
    ];

    protected static function booted()
    {
        static::creating(function ($form) {
            $prefix = 'TKT-REK-' . now()->format('Ym');
            $last = self::where('ticket_number', 'like', "$prefix%")->count() + 1;
            $form->ticket_number = $prefix . str_pad($last, 4, '0', STR_PAD_LEFT);
        });
    }

    public function risikoItems()
    {
        return $this->hasMany(RekomendasiRisikoItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
