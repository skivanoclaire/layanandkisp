<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;

class KuesionerPortalSheet implements FromArray, WithTitle
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
