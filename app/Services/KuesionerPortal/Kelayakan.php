<?php

namespace App\Services\KuesionerPortal;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Kriteria inklusi responden: akun yang pernah mengajukan minimal satu permohonan
 * dan bukan pengelola portal. Tabel permohonan diatur di config kuesioner_portal.tabel_permohonan.
 */
class Kelayakan
{
    private ?array $tabelValid = null;

    public function memenuhi(User $user): bool
    {
        if ($this->dikecualikanKarenaRole($user)) {
            return false;
        }

        foreach ($this->tabelValid() as $entri) {
            if ($this->query($entri)->where('user_id', $user->id)->exists()) {
                return true;
            }
        }

        return false;
    }

    public function dikecualikanKarenaRole(User $user): bool
    {
        $roles = config('kuesioner_portal.role_dikecualikan', []);

        return $user->roles()->whereIn('name', $roles)->exists();
    }

    /**
     * Rekapitulasi populasi untuk Subbab 3.2.1 dan gambaran pemakaian layanan.
     *
     * @return array{total_akun:int, akun_layak:int, user_ids:array<int>, per_layanan:array<string,array{permohonan:int, akun:int}>}
     */
    public function rekapPopulasi(): array
    {
        $dikecualikan = DB::table('role_user')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->whereIn('roles.name', config('kuesioner_portal.role_dikecualikan', []))
            ->pluck('role_user.user_id')
            ->all();

        $semuaAkun = [];
        $perLayanan = [];

        foreach ($this->tabelValid() as $entri) {
            $layanan = $entri['layanan'];
            $perLayanan[$layanan] ??= ['permohonan' => 0, 'akun' => []];

            $baris = $this->query($entri)
                ->whereNotIn('user_id', $dikecualikan ?: [0])
                ->select('user_id', DB::raw('COUNT(*) as jumlah'))
                ->groupBy('user_id')
                ->get();

            foreach ($baris as $b) {
                $perLayanan[$layanan]['permohonan'] += (int) $b->jumlah;
                $perLayanan[$layanan]['akun'][$b->user_id] = true;
                $semuaAkun[$b->user_id] = true;
            }
        }

        foreach ($perLayanan as $k => $v) {
            $perLayanan[$k]['akun'] = count($v['akun']);
        }

        return [
            'total_akun' => User::whereNotIn('id', $dikecualikan ?: [0])->count(),
            'akun_layak' => count($semuaAkun),
            'user_ids' => array_keys($semuaAkun),
            'per_layanan' => $perLayanan,
        ];
    }

    private function query(array $entri)
    {
        $query = DB::table($entri['tabel'])->whereNotNull('user_id');

        foreach ($entri['kecuali'] ?? [] as $kolom => $nilai) {
            $query->where($kolom, '!=', $nilai);
        }

        return $query;
    }

    private function tabelValid(): array
    {
        return $this->tabelValid ??= array_values(array_filter(
            config('kuesioner_portal.tabel_permohonan', []),
            fn ($e) => Schema::hasTable($e['tabel']) && Schema::hasColumn($e['tabel'], 'user_id')
        ));
    }
}
