<?php

namespace App\Exports;

use App\Models\VidconData;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class VidconDataExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
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
        $query = VidconData::query();

        // Apply filters
        if (!empty($this->filters['nama_instansi'])) {
            $query->where('nama_instansi', 'like', '%' . $this->filters['nama_instansi'] . '%');
        }
        if (!empty($this->filters['tanggal_mulai'])) {
            $query->whereDate('tanggal_mulai', '>=', $this->filters['tanggal_mulai']);
        }
        if (!empty($this->filters['tanggal_selesai'])) {
            $query->whereDate('tanggal_selesai', '<=', $this->filters['tanggal_selesai']);
        }
        if (!empty($this->filters['platform'])) {
            $query->where('platform', 'like', '%' . $this->filters['platform'] . '%');
        }

        return $query->orderBy('tanggal_mulai', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Instansi',
            'Nomor Surat',
            'Judul Kegiatan',
            'Lokasi',
            'Tanggal Mulai',
            'Tanggal Selesai',
            'Jam Mulai',
            'Jam Selesai',
            'Platform',
            'Operator',
            'Dokumentasi',
            'Akun Zoom',
            'Informasi Pimpinan',
            'Keterangan',
        ];
    }

    public function map($vidconData): array
    {
        return [
            $vidconData->no,
            $vidconData->nama_instansi,
            $vidconData->nomor_surat,
            $vidconData->judul_kegiatan,
            $vidconData->lokasi,
            $vidconData->tanggal_mulai ? $vidconData->tanggal_mulai->format('d/m/Y') : '',
            $vidconData->tanggal_selesai ? $vidconData->tanggal_selesai->format('d/m/Y') : '',
            $vidconData->jam_mulai ? $vidconData->jam_mulai->format('H:i') : '',
            $vidconData->jam_selesai ? $vidconData->jam_selesai->format('H:i') : '',
            $vidconData->platform,
            $vidconData->operator,
            $vidconData->dokumentasi,
            $vidconData->akun_zoom,
            $vidconData->informasi_pimpinan,
            $vidconData->keterangan,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,
            'B' => 30,
            'C' => 20,
            'D' => 50,
            'E' => 20,
            'F' => 15,
            'G' => 15,
            'H' => 12,
            'I' => 12,
            'J' => 15,
            'K' => 20,
            'L' => 30,
            'M' => 20,
            'N' => 30,
            'O' => 30,
        ];
    }
}
