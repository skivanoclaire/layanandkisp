<?php

namespace App\Services\MasterArsitektur;

use App\Models\MasterArsitektur\Item;
use Illuminate\Validation\Rule;

/**
 * Isian form satu entri Master Data Arsitektur: aturan validasi dan
 * pemindahan nilai antara form, entri, dan permohonan pembaruan.
 * "Nilai datar" = kunci kolom config => nilai (tanpa unit_kerja).
 */
class IsianItem
{
    /** Kolom inti (kolom tabel) beserta panjang maksimumnya. */
    public const BATAS = ['kode' => 150, 'nama' => 1000, 'status' => 100, 'ref_1' => 255, 'ref_2' => 255, 'ref_3' => 255, 'tahun' => 20];

    /**
     * Aturan validasi form suatu jenis.
     *
     * @return array{0: array<string, array>, 1: array<string, string>} [rules, nama atribut]
     */
    public static function aturan(string $jenis, bool $denganUnitKerja = true): array
    {
        $rules = [];
        $nama = [];

        foreach (Skema::kolom($jenis) as $kunci => $definisi) {
            if ($kunci === 'unit_kerja') {
                if ($denganUnitKerja) {
                    $rules['unit_kerja_id'] = ['nullable', 'integer', 'exists:unit_kerjas,id'];
                    $nama['unit_kerja_id'] = $definisi['label'];
                }
            } elseif (isset(self::BATAS[$kunci])) {
                $rules[$kunci] = [$kunci === 'nama' ? 'required' : 'nullable', 'string', 'max:' . self::BATAS[$kunci]];
                $nama[$kunci] = $definisi['label'];
            } else {
                $rules["atribut.$kunci"] = ['nullable', 'string', 'max:20000'];
                $nama["atribut.$kunci"] = $definisi['label'];
            }

            // Kolom pilihan hanya menerima opsi baku
            if ($opsi = Skema::opsi($definisi)) {
                $rules[isset(self::BATAS[$kunci]) ? $kunci : "atribut.$kunci"][] = Rule::in($opsi);
            }
        }

        return [$rules, $nama];
    }

    /** Hasil validasi form => nilai datar. */
    public static function datar(string $jenis, array $data): array
    {
        $datar = [];

        foreach (self::kunciKolom($jenis) as $kunci) {
            $nilai = isset(self::BATAS[$kunci]) ? ($data[$kunci] ?? null) : ($data['atribut'][$kunci] ?? null);
            $datar[$kunci] = $nilai === null || $nilai === '' ? null : (string) $nilai;
        }

        return $datar;
    }

    /** Nilai datar yang sekarang tersimpan pada entri. */
    public static function dariItem(Item $item): array
    {
        $datar = [];

        foreach (self::kunciKolom($item->jenis) as $kunci) {
            $nilai = isset(self::BATAS[$kunci]) ? $item->getAttribute($kunci) : ($item->atribut[$kunci] ?? null);
            $datar[$kunci] = $nilai === null || $nilai === '' ? null : (string) $nilai;
        }

        return $datar;
    }

    /** Pasang nilai datar ke entri (belum disimpan). */
    public static function isi(Item $item, array $datar): void
    {
        $atribut = [];

        foreach (self::kunciKolom($item->jenis) as $kunci) {
            $nilai = $datar[$kunci] ?? null;

            if (isset(self::BATAS[$kunci])) {
                $item->setAttribute($kunci, $nilai);
            } elseif ($nilai !== null && $nilai !== '') {
                $atribut[$kunci] = $nilai;
            }
        }

        $item->atribut = $atribut ?: null;
    }

    /**
     * Kunci kolom yang nilainya berbeda. Spasi tepi dan bentuk akhir baris
     * diabaikan, supaya kolom yang tidak disentuh di form tidak terbaca berubah.
     *
     * @return string[]
     */
    public static function beda(array $lama, array $baru): array
    {
        $rapi = fn ($v) => $v === null ? '' : trim(str_replace(["\r\n", "\r"], "\n", (string) $v));

        return array_values(array_filter(
            array_keys($baru),
            fn ($kunci) => $rapi($baru[$kunci]) !== $rapi($lama[$kunci] ?? null)
        ));
    }

    private static function kunciKolom(string $jenis): array
    {
        return array_values(array_diff(array_keys(Skema::kolom($jenis)), ['unit_kerja']));
    }
}
