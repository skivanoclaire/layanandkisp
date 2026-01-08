<?php

namespace App\Exports;

use App\Models\SubdomainRequest;

class SubdomainRequestExport extends BaseDigitalExport
{
    protected function getModelClass()
    {
        return SubdomainRequest::class;
    }

    protected function getRelations()
    {
        return ['user', 'programmingLanguage', 'framework', 'database'];
    }

    public function headings(): array
    {
        return [
            'No',
            'No. Tiket',
            'Tanggal Diajukan',
            'Pemohon',
            'Subdomain',
            'IP Address',
            'Nama Aplikasi',
            'Tech Stack',
            'Status',
        ];
    }

    public function map($request): array
    {
        static $no = 0;
        $no++;

        $techStack = collect([
            $request->programmingLanguage?->name,
            $request->framework?->name,
            $request->database?->name
        ])->filter()->implode(', ');

        return [
            $no,
            $request->ticket_no,
            $request->submitted_at ? $request->submitted_at->format('d/m/Y H:i') : '',
            $request->user?->name ?? $request->nama,
            $request->subdomain_requested,
            $request->ip_address,
            $request->nama_aplikasi,
            $techStack ?: '-',
            ucfirst($request->status),
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 6,   // No
            'B' => 20,  // Ticket No
            'C' => 18,  // Tanggal
            'D' => 25,  // Pemohon
            'E' => 25,  // Subdomain
            'F' => 15,  // IP Address
            'G' => 30,  // Nama Aplikasi
            'H' => 30,  // Tech Stack
            'I' => 12,  // Status
        ];
    }
}
