<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Personel OPD yang didaftarkan sebagai pengguna akun layanan DTSEN (Lampiran I).
 */
class DtsenAccountMember extends Model
{
    protected $fillable = [
        'dtsen_account_request_id', 'user_id',
        'nama', 'nip', 'jabatan', 'unit_kerja', 'no_hp', 'email',
    ];

    public function accountRequest(): BelongsTo
    {
        return $this->belongsTo(DtsenAccountRequest::class, 'dtsen_account_request_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Surel dianjurkan memakai domain resmi pemerintah (Bab V huruf A Juknis).
     */
    public function usesGovernmentEmail(): bool
    {
        return (bool) preg_match('/\.go\.id$/i', (string) $this->email);
    }
}
