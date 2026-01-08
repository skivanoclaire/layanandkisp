<?php

namespace App\Exports;

use App\Models\VidconRequest;

class VidconRequestExport extends BaseDigitalExport
{
    protected function getModelClass()
    {
        return VidconRequest::class;
    }

    protected function getRelations()
    {
        return ['user', 'unitKerja'];
    }

    public function headings(): array
    {
        return [
            'No',
            'No. Tiket',
            'Tanggal Diajukan',
            'Pemohon',
            'Unit Kerja',
            'Judul Kegiatan',
            'Tanggal Pelaksanaan',
            'Platform',
            'Jumlah Peserta',
            'Status',
        ];
    }

    public function map($request): array
    {
        static $no = 0;
        $no++;

        // Format tanggal pelaksanaan
        $tanggalPelaksanaan = '';
        if ($request->tanggal_mulai) {
            $tanggalPelaksanaan = \Carbon\Carbon::parse($request->tanggal_mulai)->format('d/m/Y');
            if ($request->tanggal_selesai && $request->tanggal_mulai != $request->tanggal_selesai) {
                $tanggalPelaksanaan .= ' - ' . \Carbon\Carbon::parse($request->tanggal_selesai)->format('d/m/Y');
            }
        }

        return [
            $no,
            $request->ticket_no,
            $request->submitted_at ? $request->submitted_at->format('d/m/Y H:i') : '',
            $request->user?->name ?? '-',
            $request->unitKerja?->nama ?? '-',
            $request->judul_kegiatan,
            $tanggalPelaksanaan,
            $request->platform,
            $request->jumlah_peserta,
            ucfirst($request->status),
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 6,   // No
            'B' => 20,  // Ticket No
            'C' => 18,  // Tanggal Diajukan
            'D' => 25,  // Pemohon
            'E' => 30,  // Unit Kerja
            'F' => 35,  // Judul Kegiatan
            'G' => 20,  // Tanggal Pelaksanaan
            'H' => 15,  // Platform
            'I' => 12,  // Jumlah Peserta
            'J' => 12,  // Status
        ];
    }
}
