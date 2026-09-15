<?php

namespace App\Models;

use App\Models\Concerns\HasDtsenTicket;
use App\Services\Sla\WorkingTimeCalculator;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Form 5.3 - Laporan insiden keamanan data DTSEN (Bab VI huruf C).
 * Wajib dilaporkan maks. 3x24 jam hari kerja sejak insiden diketahui; keterlambatan
 * dieskalasi ke Petugas Pelindung DTSEN (Kepala DKISP) dan Prosesor.
 */
class DtsenIncidentReport extends Model
{
    use HasDtsenTicket;

    /** Batas pelaporan sejak insiden diketahui (hari kerja). */
    public const BATAS_HARI_KERJA = 3;

    public const STATUS_DILAPORKAN = 'dilaporkan';
    public const STATUS_DITANGANI = 'ditangani';
    public const STATUS_SELESAI = 'selesai';

    protected $fillable = [
        'ticket_no', 'dtsen_data_request_id', 'user_id', 'unit_kerja_id',
        'jenis_insiden', 'waktu_diketahui', 'batas_pelaporan', 'kronologi', 'dampak',
        'tindakan_awal', 'file_path', 'status', 'terlambat', 'escalated_at',
        'tindak_lanjut', 'handled_by', 'handled_at',
    ];

    protected $casts = [
        'waktu_diketahui' => 'datetime',
        'batas_pelaporan' => 'datetime',
        'escalated_at' => 'datetime',
        'handled_at' => 'datetime',
        'terlambat' => 'boolean',
    ];

    public static function ticketPrefix(): string
    {
        return 'DTSEN-INS';
    }

    public function dataRequest(): BelongsTo
    {
        return $this->belongsTo(DtsenDataRequest::class, 'dtsen_data_request_id');
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function unitKerja(): BelongsTo { return $this->belongsTo(UnitKerja::class); }
    public function handledBy(): BelongsTo { return $this->belongsTo(User::class, 'handled_by'); }

    public static function jenisLabels(): array
    {
        return [
            'kebocoran' => 'Indikasi Kebocoran Data',
            'penyalahgunaan' => 'Penyalahgunaan Data',
            'lainnya' => 'Insiden Keamanan Lainnya',
        ];
    }

    public static function statusLabels(): array
    {
        return [
            self::STATUS_DILAPORKAN => 'Dilaporkan',
            self::STATUS_DITANGANI => 'Sedang Ditangani',
            self::STATUS_SELESAI => 'Selesai',
        ];
    }

    public static function statusBadgeClasses(): array
    {
        return [
            self::STATUS_DILAPORKAN => 'bg-red-100 text-red-800',
            self::STATUS_DITANGANI => 'bg-yellow-100 text-yellow-800',
            self::STATUS_SELESAI => 'bg-green-100 text-green-800',
        ];
    }

    public function getJenisLabelAttribute(): string
    {
        return self::jenisLabels()[$this->jenis_insiden] ?? (string) $this->jenis_insiden;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::statusLabels()[$this->status] ?? ucfirst((string) $this->status);
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return self::statusBadgeClasses()[$this->status] ?? 'bg-gray-100 text-gray-700';
    }

    /**
     * Tenggat pelaporan: 3 hari kerja sejak insiden diketahui.
     *
     * WorkingTimeCalculator mengembalikan Carbon\Carbon, sedangkan cast Eloquent
     * memakai Illuminate\Support\Carbon — hasilnya dinormalkan lewat instance().
     */
    public static function deadlineFrom(CarbonInterface $waktuDiketahui): Carbon
    {
        return Carbon::instance(
            WorkingTimeCalculator::default()->addWorkingDays($waktuDiketahui, self::BATAS_HARI_KERJA)
        );
    }

    /** Isi batas pelaporan & tandai keterlambatan berdasarkan waktu pelaporan. */
    public function applyDeadline(?CarbonInterface $reportedAt = null): void
    {
        if (! $this->waktu_diketahui) {
            return;
        }

        $this->batas_pelaporan = self::deadlineFrom($this->waktu_diketahui);
        $this->terlambat = ($reportedAt ?? now())->greaterThan($this->batas_pelaporan);
    }
}
