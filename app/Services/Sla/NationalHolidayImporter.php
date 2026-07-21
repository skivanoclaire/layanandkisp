<?php

namespace App\Services\Sla;

use App\Models\SlaHoliday;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Impor libur nasional & cuti bersama dari API publik ke tabel `sla_holidays`.
 *
 * API sengaja TIDAK dipanggil saat menghitung SLA — hasilnya disalin ke database
 * lebih dulu, supaya perhitungan tetap jalan kalau API-nya mati. (Dua API sejenis
 * yang dulu populer, api-harilibur & dayoffapi, sekarang balas HTTP 402.)
 *
 * Tanggal bersumber `manual` tidak pernah disentuh: itu milik admin, mis. libur
 * daerah yang tidak ada di API. Sebaliknya tanggal `import` yang hilang dari API
 * akan dihapus, supaya revisi SKB (cuti bersama digeser/dibatalkan) ikut terbawa.
 */
class NationalHolidayImporter
{
    public function __construct(private ?string $endpoint = null)
    {
        $this->endpoint = $endpoint ?: config('services.libur_nasional.url');
    }

    /**
     * @return array{ditambah: int, diperbarui: int, dihapus: int, dilewati: int}
     */
    public function import(int $year, ?int $userId = null): array
    {
        $items = $this->fetch($year);

        $hasil = ['ditambah' => 0, 'diperbarui' => 0, 'dihapus' => 0, 'dilewati' => 0];

        foreach ($items as $item) {
            $existing = SlaHoliday::where('tanggal', $item['tanggal'])->first();

            if ($existing && $existing->sumber === SlaHoliday::SUMBER_MANUAL) {
                $hasil['dilewati']++;

                continue;
            }

            if ($existing) {
                $existing->update([
                    'keterangan' => $item['keterangan'],
                    'jenis' => $item['jenis'],
                ]);
                $hasil['diperbarui']++;

                continue;
            }

            SlaHoliday::create([
                'tanggal' => $item['tanggal'],
                'keterangan' => $item['keterangan'],
                'sumber' => SlaHoliday::SUMBER_IMPORT,
                'jenis' => $item['jenis'],
                'created_by' => $userId,
            ]);
            $hasil['ditambah']++;
        }

        $hasil['dihapus'] = SlaHoliday::where('sumber', SlaHoliday::SUMBER_IMPORT)
            ->whereYear('tanggal', $year)
            ->whereNotIn('tanggal', array_column($items, 'tanggal'))
            ->delete();

        return $hasil;
    }

    /**
     * Ambil & normalisasi daftar libur satu tahun.
     *
     * Format API: [{"date":"2026-03-23","name":"Cuti Bersama ...","is_national_holiday":false}]
     *
     * @return array<int, array{tanggal: string, keterangan: string, jenis: string}>
     *
     * @throws RuntimeException bila API tidak bisa dihubungi atau balasannya tidak dikenali
     */
    public function fetch(int $year): array
    {
        try {
            $response = Http::timeout(15)->retry(2, 500)->get($this->endpoint, ['year' => $year]);
        } catch (\Throwable $e) {
            Log::warning('Impor libur nasional gagal', ['year' => $year, 'error' => $e->getMessage()]);

            throw new RuntimeException('Tidak dapat menghubungi API libur nasional: '.$e->getMessage());
        }

        if (! $response->successful()) {
            throw new RuntimeException('API libur nasional membalas HTTP '.$response->status().'.');
        }

        $data = $response->json();

        if (! is_array($data) || $data === []) {
            throw new RuntimeException('API libur nasional tidak mengembalikan data untuk tahun '.$year.'.');
        }

        $items = [];

        foreach ($data as $row) {
            if (! is_array($row) || empty($row['date']) || empty($row['name'])) {
                continue;
            }

            try {
                $tanggal = Carbon::parse($row['date']);
            } catch (\Throwable) {
                continue;
            }

            // API kadang menyertakan tanggal di luar tahun yang diminta.
            if ($tanggal->year !== $year) {
                continue;
            }

            $items[$tanggal->format('Y-m-d')] = [
                'tanggal' => $tanggal->format('Y-m-d'),
                'keterangan' => trim((string) $row['name']),
                'jenis' => ($row['is_national_holiday'] ?? true)
                    ? SlaHoliday::JENIS_LIBUR_NASIONAL
                    : SlaHoliday::JENIS_CUTI_BERSAMA,
            ];
        }

        if ($items === []) {
            throw new RuntimeException('Tidak ada tanggal yang valid pada balasan API untuk tahun '.$year.'.');
        }

        return array_values($items);
    }
}
