<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MeetingRoom extends Model
{
    protected $table = 'meeting_rooms';

    protected $fillable = [
        'nama',
        'lokasi',
        'kapasitas',
        'fasilitas',
        'is_active',
    ];

    protected $casts = [
        'kapasitas' => 'integer',
        'is_active' => 'boolean',
    ];

    public function bookings(): HasMany
    {
        return $this->hasMany(MeetingRoomBooking::class, 'meeting_room_id');
    }

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
