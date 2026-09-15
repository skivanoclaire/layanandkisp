<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Master variabel/indikator DTSEN (dikelola Bapperida selaku koordinator Forum SDD).
 */
class DtsenVariable extends Model
{
    protected $fillable = [
        'kode', 'nama', 'deskripsi', 'kategori', 'satuan',
        'level_minimal', 'dtsen_release_id', 'is_active', 'urutan',
    ];

    protected $casts = [
        'level_minimal' => 'integer',
        'is_active' => 'boolean',
        'urutan' => 'integer',
    ];

    public function release(): BelongsTo
    {
        return $this->belongsTo(DtsenRelease::class, 'dtsen_release_id');
    }

    /** Pemakaian variabel ini pada permohonan — dipakai untuk mencegah penghapusan. */
    public function requestVariables(): HasMany
    {
        return $this->hasMany(DtsenRequestVariable::class, 'dtsen_variable_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /** Variabel yang boleh diminta pada level hak akses tertentu. */
    public function scopeUpToLevel($query, int $level)
    {
        return $query->where('level_minimal', '<=', $level);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('kategori')->orderBy('urutan')->orderBy('kode');
    }

    public function getLevelLabelAttribute(): string
    {
        return DtsenDataRequest::levelLabels()[$this->level_minimal] ?? ('Level ' . $this->level_minimal);
    }
}
