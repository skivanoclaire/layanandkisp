<?php

namespace App\Exports;

use App\Models\TtePassphraseResetRequest;

class TtePassphraseResetExport extends BaseTteExport
{
    protected function getModelClass()
    {
        return TtePassphraseResetRequest::class;
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
            $request->instansi ?? '-',
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
            'H' => 12,  // Status
            'I' => 20,  // Diproses Oleh
            'J' => 30,  // Keterangan
        ];
    }
}
