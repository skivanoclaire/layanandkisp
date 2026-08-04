<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KonsultasiAiFaq extends Model
{
    protected $table = 'konsultasi_ai_faqs';

    protected $fillable = [
        'pertanyaan',
        'jawaban',
        'kategori',
        'kata_kunci',
        'urutan',
        'is_active',
        'hit_count',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'urutan' => 'integer',
        'hit_count' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Kata kunci sebagai array yang sudah dinormalisasi.
     *
     * @return array<int, string>
     */
    public function kataKunciList(): array
    {
        $raw = explode(',', (string) $this->kata_kunci);
        $list = array_filter(array_map(fn ($k) => trim(mb_strtolower($k)), $raw));

        return array_values($list);
    }
}
