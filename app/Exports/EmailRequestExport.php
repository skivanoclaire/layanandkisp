<?php

namespace App\Exports;

use App\Models\EmailRequest;

class EmailRequestExport extends BaseDigitalExport
{
    protected function getModelClass()
    {
        return EmailRequest::class;
    }

    public function headings(): array
    {
        return [
            'No',
            'No. Tiket',
            'Tanggal Diajukan',
            'Nama',
            'Username',
            'Instansi',
            'Status',
        ];
    }

    public function map($request): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $request->ticket_no,
            $request->submitted_at ? $request->submitted_at->format('d/m/Y H:i') : '',
            $request->nama,
            $request->username,
            $request->instansi,
            ucfirst($request->status),
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 6,   // No
            'B' => 20,  // Ticket No
            'C' => 18,  // Tanggal
            'D' => 25,  // Nama
            'E' => 20,  // Username
            'F' => 30,  // Instansi
            'G' => 12,  // Status
        ];
    }
}
