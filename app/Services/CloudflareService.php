<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CloudflareService
{
    protected $apiToken;
    protected $analyticsToken;
    protected $zoneId;
    protected $baseUrl = 'https://api.cloudflare.com/client/v4';

    public function __construct()
    {
        $this->apiToken = config('services.cloudflare.api_token');
        $this->analyticsToken = config('services.cloudflare.analytics_token');
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

    /**
     * Get zone analytics data using GraphQL API
     */
    public function getZoneAnalytics(string $startDate, string $endDate): ?array
    {
        if (!$this->zoneId) {
            $this->zoneId = $this->getZoneId();
        }

        if (!$this->zoneId) {
            Log::error('Cloudflare Analytics: Zone ID not available');
            return null;
        }

        if (!$this->analyticsToken) {
            Log::error('Cloudflare Analytics: Analytics token not configured');
            return null;
        }

        try {
            $query = <<<GRAPHQL
            query {
                viewer {
                    zones(filter: {zoneTag: "{$this->zoneId}"}) {
                        httpRequests1dGroups(
                            limit: 100,
                            filter: {date_geq: "$startDate", date_leq: "$endDate"}
                        ) {
                            dimensions {
                                date
                            }
                            sum {
                                requests
                                bytes
                                cachedBytes
                                cachedRequests
                                pageViews
                                threats
                            }
                            uniq {
                                uniques
                            }
                        }
                    }
                }
            }
            GRAPHQL;

            $response = Http::withToken($this->analyticsToken)
                ->withOptions(['verify' => false])
                ->post("{$this->baseUrl}/graphql", [
                    'query' => $query
                ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['data']['viewer']['zones'][0]['httpRequests1dGroups'])) {
                    return $data['data']['viewer']['zones'][0]['httpRequests1dGroups'];
                }
                // Log if there are errors in the response
                if (isset($data['errors'])) {
                    Log::error('Cloudflare Analytics GraphQL errors: ' . json_encode($data['errors']));
                }
            }

            Log::error('Cloudflare Analytics failed: ' . $response->body());
        } catch (\Exception $e) {
            Log::error('Cloudflare Analytics error: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Get security events using GraphQL API
     * Note: firewallEventsAdaptiveGroups has a 1-day time range limit
     * So we query day by day and aggregate results
     */
    public function getSecurityEvents(string $startDate, string $endDate): ?array
    {
        if (!$this->zoneId) {
            $this->zoneId = $this->getZoneId();
        }

        if (!$this->zoneId) {
            return null;
        }

        if (!$this->analyticsToken) {
            Log::error('Cloudflare Security Events: Analytics token not configured');
            return null;
        }

        try {
            $allEvents = [];
            $currentDate = \Carbon\Carbon::parse($startDate);
            $endDateCarbon = \Carbon\Carbon::parse($endDate);

            // Query day by day due to API limitations
            while ($currentDate->lte($endDateCarbon)) {
                $dayStart = $currentDate->format('Y-m-d') . 'T00:00:00Z';
                $dayEnd = $currentDate->format('Y-m-d') . 'T23:59:59Z';

                $query = <<<GRAPHQL
                query {
                    viewer {
                        zones(filter: {zoneTag: "{$this->zoneId}"}) {
                            firewallEventsAdaptiveGroups(
                                limit: 100,
                                filter: {datetime_geq: "$dayStart", datetime_leq: "$dayEnd"}
                                orderBy: [count_DESC]
                            ) {
                                count
                                dimensions {
                                    action
                                    source
                                    clientCountryName
                                }
                            }
                        }
                    }
                }
                GRAPHQL;

                $response = Http::withToken($this->analyticsToken)
                    ->withOptions(['verify' => false])
                    ->post("{$this->baseUrl}/graphql", [
                        'query' => $query
                    ]);

                if ($response->successful()) {
                    $data = $response->json();
                    if (isset($data['data']['viewer']['zones'][0]['firewallEventsAdaptiveGroups'])) {
                        $dayEvents = $data['data']['viewer']['zones'][0]['firewallEventsAdaptiveGroups'];
                        $allEvents = array_merge($allEvents, $dayEvents);
                    }
                }

                $currentDate->addDay();
            }

            return $allEvents;
        } catch (\Exception $e) {
            Log::error('Cloudflare Security Events error: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Get traffic analytics per hostname (subdomain)
     * Uses httpRequestsAdaptiveGroups which has a 1-day limit, so we query day by day
     */
    public function getHostnameAnalytics(string $startDate, string $endDate): ?array
    {
        if (!$this->zoneId) {
            $this->zoneId = $this->getZoneId();
        }

        if (!$this->zoneId) {
            return null;
        }

        if (!$this->analyticsToken) {
            Log::error('Cloudflare Hostname Analytics: Analytics token not configured');
            return null;
        }

        try {
            $allHostnameData = [];
            $currentDate = \Carbon\Carbon::parse($startDate);
            $endDateCarbon = \Carbon\Carbon::parse($endDate);

            // Query day by day due to API limitations
            while ($currentDate->lte($endDateCarbon)) {
                $dayStart = $currentDate->format('Y-m-d') . 'T00:00:00Z';
                $dayEnd = $currentDate->format('Y-m-d') . 'T23:59:59Z';

                $query = <<<GRAPHQL
                query {
                    viewer {
                        zones(filter: {zoneTag: "{$this->zoneId}"}) {
                            httpRequestsAdaptiveGroups(
                                limit: 500,
                                filter: {datetime_geq: "$dayStart", datetime_leq: "$dayEnd"}
                                orderBy: [count_DESC]
                            ) {
                                count
                                dimensions {
                                    clientRequestHTTPHost
                                }
                            }
                        }
                    }
                }
                GRAPHQL;

                $response = Http::withToken($this->analyticsToken)
                    ->withOptions(['verify' => false])
                    ->post("{$this->baseUrl}/graphql", [
                        'query' => $query
                    ]);

                if ($response->successful()) {
                    $data = $response->json();
                    if (isset($data['data']['viewer']['zones'][0]['httpRequestsAdaptiveGroups'])) {
                        $dayData = $data['data']['viewer']['zones'][0]['httpRequestsAdaptiveGroups'];
                        $allHostnameData = array_merge($allHostnameData, $dayData);
                    }
                }

                $currentDate->addDay();
            }

            return ['httpRequestsAdaptiveGroups' => $allHostnameData];
        } catch (\Exception $e) {
            Log::error('Cloudflare Hostname Analytics error: ' . $e->getMessage());
        }

        return null;
    }
}
