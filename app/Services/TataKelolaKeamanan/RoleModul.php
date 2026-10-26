<?php

namespace App\Services\TataKelolaKeamanan;

use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Memasang role khusus modul ke akun saat akun didaftarkan sebagai responden,
 * supaya pengelola tidak perlu mengatur role secara terpisah di Kelola Pengguna.
 */
class RoleModul
{
    public const PENGELOLA = 'Pengelola Tata Kelola Keamanan';
    public const RESPONDEN = 'Responden Tata Kelola Keamanan';

    public function pasang(User $user, string $role): void
    {
        $roleId = DB::table('roles')->where('name', $role)->value('id');
        if (! $roleId) {
            return;
        }
        $ada = DB::table('role_user')->where('user_id', $user->id)->where('role_id', $roleId)->exists();
        if (! $ada) {
            DB::table('role_user')->insert(['user_id' => $user->id, 'role_id' => $roleId, 'created_at' => now(), 'updated_at' => now()]);
        }
    }

    /** Melepas role bila akun tidak lagi terdaftar di evaluasi mana pun untuk peran tersebut. */
    public function lepasBilaTidakDipakai(User $user, string $role, bool $masihDipakai): void
    {
        if ($masihDipakai) {
            return;
        }
        $roleId = DB::table('roles')->where('name', $role)->value('id');
        if ($roleId) {
            DB::table('role_user')->where('user_id', $user->id)->where('role_id', $roleId)->delete();
        }
    }
}
