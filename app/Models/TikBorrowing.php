<?php
// app/Models/TikBorrowing.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class TikBorrowing extends Model
{
    protected $fillable = ['operator_id','code','status','started_at','finished_at','notes'];
    protected $casts = ['started_at'=>'datetime','finished_at'=>'datetime'];

    public function operator(){ return $this->belongsTo(User::class,'operator_id'); }
    public function items(){ return $this->hasMany(TikBorrowingItem::class); }
    public function photos(){ return $this->hasMany(TikBorrowingPhoto::class); }

    public static function generateCode(): string {
        $seq = (self::whereDate('created_at', now()->toDateString())->max('id') ?? 0) + 1;
        return sprintf('TIK-%s-%04d', now()->format('Ymd'), $seq);
    }

        public const STATUS_LABELS = [
        'ongoing'  => 'Sedang dipinjam',
        'returned' => 'Dikembalikan',
        'pending'  => 'Menunggu', // tetap ada kalau suatu saat dipakai
    ];

    protected function statusLabel(): Attribute
    {
        return Attribute::get(fn () => self::STATUS_LABELS[$this->status] ?? ucfirst($this->status));
    }

        public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'ongoing'  => 'Sedang dipinjam',
            'returned' => 'Dikembalikan',
            'pending'  => 'Menunggu',
            default    => ucfirst((string) $this->status),
        };
    }
}

