<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

abstract class BaseTteExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
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
        $query = $modelClass::with(['user', 'processedBy']);

        // Apply status filter
        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        // Apply search filter
        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('ticket_no', 'like', "%{$search}%")
                  ->orWhere('nama', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%")
                  ->orWhere('email_resmi', 'like', "%{$search}%");
            });
        }

        // Apply date range filter on created_at
        if (!empty($this->filters['dari_tanggal'])) {
            $query->whereDate('created_at', '>=', $this->filters['dari_tanggal']);
        }
        if (!empty($this->filters['sampai_tanggal'])) {
            $query->whereDate('created_at', '<=', $this->filters['sampai_tanggal']);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    // Shared styles
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
