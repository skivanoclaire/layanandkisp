<?php

namespace App\Services\MasterArsitektur;

use App\Models\UnitKerja;

/**
 * Mencocokkan nama perangkat daerah pada Excel dengan Master Data Instansi.
 * Nama yang berbeda penulisannya didaftarkan di config master_arsitektur.alias_instansi.
 */
class PencocokInstansi
{
    /** @var array<string, int>|null nama ternormalisasi => id */
    private ?array $instansi = null;

    /** @var array<string, string>|null nama Excel ternormalisasi => nama instansi ternormalisasi */
    private ?array $alias = null;

    public function cari(?string $nama): ?int
    {
        $kunci = self::normal($nama);

        if ($kunci === '') {
            return null;
        }

        $this->muat();

        return $this->instansi[$kunci]
            ?? $this->instansi[$this->alias[$kunci] ?? '']
            ?? null;
    }

    public static function normal(?string $nama): string
    {
        $nama = str_replace(['"', '“', '”', "'"], '', (string) $nama);

        return mb_strtoupper(trim(preg_replace('/\s+/u', ' ', $nama)));
    }

    private function muat(): void
    {
        if ($this->instansi !== null) {
            return;
        }

        $this->instansi = [];
        foreach (UnitKerja::orderBy('id')->get(['id', 'nama']) as $unit) {
            $this->instansi[self::normal($unit->nama)] ??= $unit->id;
        }

        $this->alias = [];
        foreach (config('master_arsitektur.alias_instansi', []) as $dari => $ke) {
            $this->alias[self::normal($dari)] = self::normal($ke);
        }
    }
}
