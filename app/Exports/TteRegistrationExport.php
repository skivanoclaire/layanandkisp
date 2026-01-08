<?php

namespace App\Exports;

use App\Models\TteRegistrationRequest;

class TteRegistrationExport extends BaseTteExport
{
    protected function getModelClass()
    {
        return TteRegistrationRequest::class;
    }

    public function headings(): array
    {
        return [
            'No',
            'No. Tiket',
            'Tanggal Dibuat',
            'Nama',
            'NIP',
            'Email Resmi',
            'Instansi',
            'Jabatan',
            'No. HP',
            'Status',
            'Diproses Oleh',
            'Keterangan Admin',
        ];
    }

    public function map($request): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $request->ticket_no,
            $request->created_at ? $request->created_at->format('d/m/Y H:i') : '',
            $request->nama,
            $request->nip,
            $request->email_resmi,
            $request->instansi,
            $request->jabatan,
            $request->no_hp,
            $request->getStatusLabel(),
            $request->processedBy ? $request->processedBy->name : '-',
            $request->keterangan_admin ?? '-',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 6,   // No
            'B' => 20,  // Ticket No
            'C' => 18,  // Tanggal
            'D' => 25,  // Nama
            'E' => 15,  // NIP
            'F' => 25,  // Email
            'G' => 30,  // Instansi
            'H' => 20,  // Jabatan
            'I' => 15,  // No HP
            'J' => 12,  // Status
            'K' => 20,  // Diproses Oleh
            'L' => 30,  // Keterangan
        ];
    }
}
