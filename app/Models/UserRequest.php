<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UserRequest extends Model
{
    use HasFactory;

    protected $table = 'requests';

    protected $fillable = [
        'user_id',
        'service',
        'file',
        'status',
        'ticket_number',
    ];

    protected $attributes = [
        'status' => 'Menunggu',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ✅ Fungsi pembangkit ticket_number dengan format TKT-YYYYMMDD-0001
    public static function generateTicketNumber()
    {
        $date = Carbon::now()->format('Ymd');

        $lastTicket = self::whereDate('created_at', Carbon::today())
            ->whereNotNull('ticket_number')
            ->orderBy('id', 'desc')
            ->first();

        $nextNumber = 1;
        if ($lastTicket && preg_match('/\d{4}$/', $lastTicket->ticket_number, $matches)) {
            $nextNumber = intval($matches[0]) + 1;
        }

        return 'TKT-' . $date . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    // Auto-generate ticket_number saat create
    protected static function booted()
    {
        static::creating(function ($request) {
            if (empty($request->ticket_number)) {
                $request->ticket_number = self::generateTicketNumber();
            }
        });
    }
}
