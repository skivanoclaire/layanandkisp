<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

abstract class BaseDigitalExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    // Abstract methods to be implemented by child classes
    abstract protected function getModelClass();
    abstract public function headings(): array;
    abstract public function map($request): array;
    abstract public function columnWidths(): array;

    // Shared collection logic
    public function collection()
    {
        $modelClass = $this->getModelClass();
        $query = $modelClass::with($this->getRelations());

        // Apply status filter if provided
        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        // Apply date range filter on submitted_at
        if (!empty($this->filters['dari_tanggal'])) {
            $query->whereDate('submitted_at', '>=', $this->filters['dari_tanggal']);
        }
        if (!empty($this->filters['sampai_tanggal'])) {
            $query->whereDate('submitted_at', '<=', $this->filters['sampai_tanggal']);
        }

        return $query->orderBy('submitted_at', 'desc')->get();
    }

    // Override this method in child classes to specify relations
    protected function getRelations()
    {
        return ['user'];
    }

    // Shared styles
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
