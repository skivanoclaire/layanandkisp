<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InstansiSubdomainResource extends JsonResource
{
    /**
     * Domain induk untuk seluruh subdomain Pemprov Kaltara.
     */
    private const DOMAIN_SUFFIX = 'kaltaraprov.go.id';

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nama' => $this->nama,
            'tipe' => $this->tipe,
            'subdomains' => $this->webMonitors->map(function ($monitor) {
                return [
                    'nama_sistem' => $monitor->nama_sistem,
                    'subdomain' => $monitor->subdomain,
                    'full_domain' => $this->fullDomain($monitor->subdomain),
                    'jenis' => $monitor->jenis,
                    'status' => $monitor->status,
                    'ip_address' => $monitor->ip_address,
                ];
            })->values(),
        ];
    }

    /**
     * Bangun domain lengkap. Kolom `subdomain` bisa berisi label DNS saja
     * (mis. "abang") atau hostname lengkap (mis. "abang.kaltaraprov.go.id");
     * tangani keduanya agar tidak dobel suffix.
     */
    private function fullDomain(?string $subdomain): ?string
    {
        if (empty($subdomain)) {
            return null;
        }

        if (str_ends_with($subdomain, self::DOMAIN_SUFFIX)) {
            return $subdomain;
        }

        return $subdomain . '.' . self::DOMAIN_SUFFIX;
    }
}
