<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RekomendasiTimPengembangan extends Model
{
    use HasFactory;

    protected $table = 'rekomendasi_tim_pengembangan';

    protected $fillable = [
        'rekomendasi_aplikasi_form_id',
        'nama',
        'peran',
        'kontak',
    ];

    /**
     * Get the rekomendasi aplikasi form that owns this tim member.
     */
    public function rekomendasiAplikasiForm(): BelongsTo
    {
        return $this->belongsTo(RekomendasiAplikasiForm::class, 'rekomendasi_aplikasi_form_id');
    }

    /**
     * Get common role options.
     */
    public static function getRoleOptions(): array
    {
        return [
            'Project Manager',
            'System Analyst',
            'Developer',
            'Frontend Developer',
            'Backend Developer',
            'Full Stack Developer',
            'UI/UX Designer',
            'Database Administrator',
            'Quality Assurance',
            'Tester',
            'DevOps Engineer',
            'Technical Writer',
            'Business Analyst',
        ];
    }
}
