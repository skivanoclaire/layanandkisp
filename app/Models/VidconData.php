<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VidconData extends Model
{
    protected $table = 'vidcon_data';

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'id';
    }

    protected $fillable = [
        'no',
        'vidcon_request_id',
        'nama_instansi',
        'nomor_surat',
        'nama_pemohon',
        'nip_pemohon',
        'email_pemohon',
        'no_hp',
        'unit_kerja_id',
        'judul_kegiatan',
        'deskripsi_kegiatan',
        'lokasi',
        'tanggal_mulai',
        'tanggal_selesai',
        'jam_mulai',
        'jam_selesai',
        'platform',
        'link_meeting',
        'meeting_id',
        'meeting_password',
        'informasi_tambahan',
        'jumlah_peserta',
        'keperluan_khusus',
        'operator',
        'dokumentasi',
        'akun_zoom',
        'informasi_pimpinan',
        'keterangan',
        'processed_by',
        'completed_at',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'jam_mulai' => 'datetime:H:i',
        'jam_selesai' => 'datetime:H:i',
        'jumlah_peserta' => 'integer',
        'completed_at' => 'datetime',
    ];

    /**
     * Get operators assigned to this vidcon data
     */
    public function operators()
    {
        return $this->belongsToMany(User::class, 'operator_vidcon_data', 'vidcon_data_id', 'user_id')
            ->withTimestamps();
    }

    /**
     * Get operator names as comma-separated string (for backward compatibility)
     */
    public function getOperatorNamesAttribute()
    {
        return $this->operators->pluck('name')->join(', ');
    }

    /**
     * Get the unit kerja that owns this vidcon data
     */
    public function unitKerja()
    {
        return $this->belongsTo(UnitKerja::class, 'unit_kerja_id');
    }

    /**
     * Get the vidcon request that this data is created from
     */
    public function vidconRequest()
    {
        return $this->belongsTo(VidconRequest::class, 'vidcon_request_id');
    }

    /**
     * Get the user who processed this vidcon data
     */
    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Get the documentations for this vidcon data
     */
    public function documentations()
    {
        return $this->hasMany(VidconDocumentation::class, 'vidcon_data_id');
    }

    /**
     * AI-powered operator recommendation based on workload distribution
     * Uses weighted scoring algorithm to ensure fair task allocation
     *
     * @param \Illuminate\Database\Eloquent\Collection $operators Collection of User models with Operator-Vidcon role
     * @param int $count Number of operators to recommend (default: 1)
     * @return array Array of recommended operator IDs
     */
    public static function recommendOperators($operators, int $count = 1): array
    {
        if ($operators->isEmpty()) {
            return [];
        }

        // Calculate workload score for each operator
        $operatorsWithScore = $operators->map(function ($operator) {
            // Active workload has higher weight (2x) - currently in progress
            $activeWorkload = $operator->active_vidcon_workload ?? 0;

            // Total workload has lower weight (0.5x) - historical distribution
            $totalWorkload = $operator->vidcon_workload ?? 0;

            // Calculate weighted score (lower is better)
            $score = ($activeWorkload * 2) + ($totalWorkload * 0.5);

            return [
                'id' => $operator->id,
                'name' => $operator->name,
                'active_workload' => $activeWorkload,
                'total_workload' => $totalWorkload,
                'score' => $score,
            ];
        });

        // Sort by score ascending (least busy first)
        $sorted = $operatorsWithScore->sortBy('score');

        // Return top N operator IDs
        return $sorted->take($count)->pluck('id')->toArray();
    }

    /**
     * Boot method to auto-generate 'no' field
     */
    protected static function booted(): void
    {
        static::creating(function (VidconData $model) {
            if (empty($model->no)) {
                // Get the last number for current year
                $year = now()->year;
                $last = self::whereYear('created_at', $year)
                    ->orderByDesc('no')
                    ->first();

                $model->no = $last ? ($last->no + 1) : 1;
            }
        });
    }
}
