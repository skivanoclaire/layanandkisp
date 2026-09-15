<?php

namespace App\Models\Concerns;

use Illuminate\Support\Facades\DB;

/**
 * Generator nomor tiket DTSEN: PREFIX-YYMM-0001, unik per tabel.
 *
 * Pola sama dengan HasSplpWorkflow::nextTicket(), dipisah agar berkas DTSEN yang
 * tidak memakai siklus status SPLP (mis. laporan insiden, pengaduan) tetap bisa
 * memakai penomoran tiket tanpa ikut membawa konstanta status yang tidak relevan.
 *
 * Model pemakai wajib mendefinisikan: public static function ticketPrefix(): string
 */
trait HasDtsenTicket
{
    public static function nextTicket(?string $prefix = null): string
    {
        $prefix = $prefix ?: static::ticketPrefix();
        $ym = now()->format('ym');
        $base = $prefix . '-' . $ym . '-';

        return DB::transaction(function () use ($base, $prefix, $ym) {
            $last = static::where('ticket_no', 'like', $prefix . '-' . $ym . '-%')
                ->orderByDesc('ticket_no')
                ->lockForUpdate()
                ->first();

            $lastNumber = 0;
            if ($last) {
                // Prefix bisa mengandung tanda hubung (mis. DTSEN-AKUN), jadi ambil
                // segmen terakhir alih-alih indeks tetap.
                $parts = explode('-', $last->ticket_no);
                $lastNumber = (int) end($parts);
            }

            return $base . str_pad((string) ($lastNumber + 1), 4, '0', STR_PAD_LEFT);
        }, 1);
    }

    protected static function bootHasDtsenTicket(): void
    {
        static::creating(function ($model) {
            if (empty($model->ticket_no)) {
                $model->ticket_no = static::nextTicket();
            }
        });
    }
}
