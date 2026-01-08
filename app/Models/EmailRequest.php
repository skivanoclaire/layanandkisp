<?php
// app/Models/EmailRequest.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class EmailRequest extends Model
{
    protected $fillable = [
        'ticket_no','user_id','nama','nip','instansi','username','email_granted','email_alternatif','no_hp',
        'password_encrypted','consent_true','status','submitted_at','processing_at','rejected_at','completed_at'
    ];

    protected $casts = [
        'consent_true' => 'boolean',
        'submitted_at' => 'datetime',
        'processing_at'=> 'datetime',
        'rejected_at'  => 'datetime',
        'completed_at' => 'datetime',
    ];

    // ====== Password helper ======
    public function setPlainPassword(string $plain): void { $this->password_encrypted = Crypt::encryptString($plain); }
    public function getPlainPassword(): string { return Crypt::decryptString($this->password_encrypted); }

    // ====== Relations ======
    public function user(){ return $this->belongsTo(User::class); }
    public function logs(){ return $this->hasMany(EmailRequestLog::class); }

    // ====== Ticket number generator ======
    /**
     * Generate next ticket number with fixed-width counter.
     * Format default: REQYYMMNNNN (contoh: REQ25090001)
     * Ubah $prefix sesuai kebutuhan (mis. 'EML', 'TRM', dll).
     */
    public static function nextTicket(string $prefix = 'EML'): string
    {
        $ym   = now()->format('ym');          // Tahun-bulan 2 digit (YYMM)
        $base = $prefix . $ym;                // "REQYYMM"

        // Lock ringan agar aman saat banyak request bersamaan
        return DB::transaction(function () use ($base) {
            // Ambil tiket terakhir untuk bulan berjalan (urut berdasarkan ticket_no yang fixed-width)
            $last = self::where('ticket_no', 'like', $base . '%')
                ->orderByDesc('ticket_no')
                ->lockForUpdate()
                ->first();

            $lastNumber = 0;
            if ($last) {
                // Ambil 4 digit terakhir sebagai angka running
                $suffix = substr($last->ticket_no, strlen($base));
                $lastNumber = intval($suffix);
            }

            $nextNumber = $lastNumber + 1;
            return $base . str_pad((string)$nextNumber, 4, '0', STR_PAD_LEFT); // REQYYMMNNNN
        }, 1);
    }

    /**
     * Otomatis set ticket_no saat create jika belum diisi.
     */
    protected static function booted(): void
    {
        static::creating(function (EmailRequest $model) {
            if (empty($model->ticket_no)) {
                $model->ticket_no = self::nextTicket(); // gunakan prefix default 'REQ'
            }
        });
    }
}
