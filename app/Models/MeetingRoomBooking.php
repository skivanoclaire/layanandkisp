<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class MeetingRoomBooking extends Model
{
    protected $table = 'meeting_room_bookings';

    protected $fillable = [
        'ticket_no',
        'user_id',
        'meeting_room_id',
        'unit_kerja_id',
        'nama',
        'nip',
        'no_hp',
        'keperluan',
        'jumlah_peserta',
        'tanggal',
        'jam_mulai',
        'jam_selesai',
        'status',
        'processed_by',
        'admin_notes',
        'processed_at',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'jumlah_peserta' => 'integer',
        'processed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function meetingRoom()
    {
        return $this->belongsTo(MeetingRoom::class, 'meeting_room_id');
    }

    public function unitKerja()
    {
        return $this->belongsTo(UnitKerja::class, 'unit_kerja_id');
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Permohonan yang sudah disetujui di ruang & tanggal yang sama dan jamnya beririsan.
     * Jam dibandingkan sebagai string "HH:MM" (format input time).
     */
    public function scopeBentrok(Builder $query, int $roomId, string $tanggal, string $jamMulai, string $jamSelesai, ?int $kecualiId = null): Builder
    {
        return $query->where('meeting_room_id', $roomId)
            ->where('tanggal', $tanggal)
            ->where('status', 'disetujui')
            ->where('jam_mulai', '<', $jamSelesai)
            ->where('jam_selesai', '>', $jamMulai)
            ->when($kecualiId, fn (Builder $q) => $q->where('id', '!=', $kecualiId));
    }

    protected static function booted(): void
    {
        static::creating(function (MeetingRoomBooking $model) {
            if (empty($model->ticket_no)) {
                $year = now()->year;
                $month = now()->format('m');

                $last = self::whereYear('created_at', $year)
                    ->whereMonth('created_at', $month)
                    ->orderByDesc('id')
                    ->first();

                $sequence = $last ? (int) substr($last->ticket_no, -4) + 1 : 1;
                $model->ticket_no = 'RR-' . $year . $month . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
            }
        });
    }
}
