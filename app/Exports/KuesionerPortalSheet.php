<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithTitle;

/** WithStrictNullComparison: nilai 0 tetap ditulis 0, tidak menjadi sel kosong. */
class KuesionerPortalSheet implements FromArray, WithTitle, WithStrictNullComparison
{
    public function __construct(private string $judul, private array $rows)
    {
    }

    public function array(): array
    {
        return $this->rows;
    }

    public function title(): string
    {
        return $this->judul;
    }
}
