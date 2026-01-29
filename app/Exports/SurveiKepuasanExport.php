<?php

namespace App\Exports;

use App\Models\SurveiKepuasanLayanan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SurveiKepuasanExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $query = SurveiKepuasanLayanan::with(['user', 'webMonitor']);

        // Apply filters
        if (!empty($this->filters['subdomain'])) {
            $query->where('web_monitor_id', $this->filters['subdomain']);
        }

        if (!empty($this->filters['date_from'])) {
            $query->whereDate('created_at', '>=', $this->filters['date_from']);
        }

        if (!empty($this->filters['date_to'])) {
            $query->whereDate('created_at', '<=', $this->filters['date_to']);
        }

        return $query->latest()->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'No',
            'Tanggal',
            'Nama User',
            'Email',
            'Subdomain',
            'Nama Aplikasi',
            'Instansi',
            'Rating Kecepatan',
            'Rating Kemudahan',
            'Rating Kualitas',
            'Rating Responsif',
            'Rating Keamanan',
            'Rating Keseluruhan',
            'Rata-rata',
            'Kelebihan',
            'Kekurangan',
            'Saran',
        ];
    }

    /**
     * @param mixed $survey
     * @return array
     */
    public function map($survey): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $survey->created_at->format('d/m/Y H:i'),
            $survey->user->name,
            $survey->user->email,
            $survey->webMonitor->subdomain,
            $survey->webMonitor->nama_aplikasi ?? '-',
            $survey->webMonitor->nama_instansi ?? '-',
            $survey->rating_kecepatan,
            $survey->rating_kemudahan,
            $survey->rating_kualitas,
            $survey->rating_responsif,
            $survey->rating_keamanan,
            $survey->rating_keseluruhan,
            $survey->average_rating,
            $survey->kelebihan ?? '-',
            $survey->kekurangan ?? '-',
            $survey->saran ?? '-',
        ];
    }

    /**
     * @param Worksheet $sheet
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text
            1 => ['font' => ['bold' => true]],
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Data Survei Kepuasan';
    }
}
