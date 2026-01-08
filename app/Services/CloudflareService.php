<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CloudflareService
{
    protected $apiToken;
    protected $zoneId;
    protected $baseUrl = 'https://api.cloudflare.com/client/v4';

    public function __construct()
    {
        $this->apiToken = config('services.cloudflare.api_token');
        $this->zoneId = config('services.cloudflare.zone_id');
    }

    /**
     * Get Zone ID by zone name
     */
    public function getZoneId(string $zoneName = null): ?string
    {
        if ($this->zoneId) {
            return $this->zoneId;
        }

        $zoneName = $zoneName ?? config('services.cloudflare.zone_name');

        try {
            $response = Http::withToken($this->apiToken)
                ->withOptions(['verify' => false]) // For development on Windows
                ->get("{$this->baseUrl}/zones", [
                    'name' => $zoneName
                ]);

            if ($response->successful() && $response->json('success')) {
                $zones = $response->json('result');
                if (!empty($zones)) {
                    return $zones[0]['id'];
                }
            }
        } catch (\Exception $e) {
            Log::error('Cloudflare get zone failed: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Get all DNS A records
     */
    public function getDnsRecords(string $type = 'A'): array
    {
        if (!$this->zoneId) {
            $this->zoneId = $this->getZoneId();
        }

        if (!$this->zoneId) {
            return [];
        }

        try {
            $response = Http::withToken($this->apiToken)
                ->withOptions(['verify' => false])
                ->get("{$this->baseUrl}/zones/{$this->zoneId}/dns_records", [
                    'type' => $type,
                    'per_page' => 1000
                ]);

            if ($response->successful() && $response->json('success')) {
                return $response->json('result', []);
            }

            Log::error('Cloudflare DNS records failed: ' . $response->body());
        } catch (\Exception $e) {
            Log::error('Cloudflare DNS records error: ' . $e->getMessage());
        }

        return [];
    }

    /**
     * Get single DNS record by ID
     */
    public function getDnsRecord(string $recordId): ?array
    {
        if (!$this->zoneId) {
            $this->zoneId = $this->getZoneId();
        }

        if (!$this->zoneId) {
            return null;
        }

        try {
            $response = Http::withToken($this->apiToken)
                ->withOptions(['verify' => false])
                ->get("{$this->baseUrl}/zones/{$this->zoneId}/dns_records/{$recordId}");

            if ($response->successful() && $response->json('success')) {
                return $response->json('result');
            }
        } catch (\Exception $e) {
            Log::error('Cloudflare get DNS record error: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Update DNS record
     */
    public function updateDnsRecord(string $recordId, array $data): ?array
    {
        if (!$this->zoneId) {
            $this->zoneId = $this->getZoneId();
        }

        if (!$this->zoneId) {
            return null;
        }

        try {
            $response = Http::withToken($this->apiToken)
                ->withOptions(['verify' => false])
                ->put("{$this->baseUrl}/zones/{$this->zoneId}/dns_records/{$recordId}", $data);

            if ($response->successful() && $response->json('success')) {
                return $response->json('result');
            }

            Log::error('Cloudflare update DNS record failed: ' . $response->body());
        } catch (\Exception $e) {
            Log::error('Cloudflare update DNS record error: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Create DNS record
     */
    public function createDnsRecord(array $data): ?array
    {
        if (!$this->zoneId) {
            $this->zoneId = $this->getZoneId();
        }

        if (!$this->zoneId) {
            return null;
        }

        try {
            $response = Http::withToken($this->apiToken)
                ->withOptions(['verify' => false])
                ->post("{$this->baseUrl}/zones/{$this->zoneId}/dns_records", $data);

            if ($response->successful() && $response->json('success')) {
                return $response->json('result');
            }

            Log::error('Cloudflare create DNS record failed: ' . $response->body());
        } catch (\Exception $e) {
            Log::error('Cloudflare create DNS record error: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Delete DNS record
     */
    public function deleteDnsRecord(string $recordId): bool
    {
        if (!$this->zoneId) {
            $this->zoneId = $this->getZoneId();
        }

        if (!$this->zoneId) {
            return false;
        }

        try {
            $response = Http::withToken($this->apiToken)
                ->withOptions(['verify' => false])
                ->delete("{$this->baseUrl}/zones/{$this->zoneId}/dns_records/{$recordId}");

            return $response->successful() && $response->json('success');
        } catch (\Exception $e) {
            Log::error('Cloudflare delete DNS record error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if domain is accessible (for status check)
     */
    public function checkDomainStatus(string $domain): array
    {
        try {
            $url = 'https://' . $domain;
            $response = Http::timeout(10)
                ->withOptions(['verify' => false])
                ->get($url);

            return [
                'is_active' => $response->successful(),
                'status_code' => $response->status(),
                'error' => null
            ];
        } catch (\Exception $e) {
            return [
                'is_active' => false,
                'status_code' => null,
                'error' => $e->getMessage()
            ];
        }
    }
}
