<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KonsultasiAiSetting extends Model
{
    protected $table = 'konsultasi_ai_settings';

    protected $fillable = ['key', 'value'];

    public static function get(string $key, $default = null)
    {
        $row = static::where('key', $key)->first();

        return $row ? $row->value : $default;
    }

    public static function put(string $key, $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }
}
