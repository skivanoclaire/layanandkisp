<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Ringkasan capaian SLA agregat 1 layanan (tanpa data permohonan individual/PII),
 * dikonsumsi oleh Portal/Aplikasi lain melalui /api/v1/sla/*.
 */
class SlaSummaryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $r = $this->resource;

        return [
            'layanan_key' => $r['service_key'],
            'layanan' => $r['label'],
            'kategori' => $r['group'],
            'total_permohonan' => $r['total'],
            'menunggu' => $r['menunggu'],
            'proses' => $r['proses'],
            'selesai' => $r['selesai'],
            'ditolak' => $r['ditolak'],
            'target_sla' => [
                'nilai' => $r['target_value'],
                'satuan' => $r['target_unit'],
                'aktif' => $r['target_active'],
            ],
            'rata_rata_durasi_jam_kerja' => $r['avg_duration_hours'],
            'jumlah_tercapai' => $r['achieved'],
            'jumlah_terlambat' => $r['breached'],
            'capaian_sla_persen' => $r['achieved_pct'],
        ];
    }
}
