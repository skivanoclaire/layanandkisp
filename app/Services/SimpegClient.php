<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use App\Exceptions\SimpegException;
use Illuminate\Support\Facades\Log;

class SimpegClient
{
    /**
     * Fetch pegawai data by NIK from SIMPEG API
     *
     * @param string $nik NIK (16 digits)
     * @return array ['ok' => bool, 'valid' => bool, 'data' => array|null]
     * @throws SimpegException on API errors
     */
    public function byNik(string $nik): array
    {
        return $this->get('/pegawai/by-nik', ['nik' => $nik], 'invalid_nik');
    }

    /**
     * Fetch pegawai data by NIP from SIMPEG API
     *
     * @param string $nip NIP (18 digits)
     * @return array ['ok' => bool, 'valid' => bool, 'data' => array|null]
     * @throws SimpegException on API errors
     */
    public function byNip(string $nip): array
    {
        return $this->get('/pegawai/by-nip', ['nip' => $nip], 'invalid_nip');
    }

    /**
     * Shared GET to the SIMPEG gateway: auth header, retry on transient failure,
     * and normalization through handleResponse().
     */
    protected function get(string $path, array $query, string $invalidReason): array
    {
        $apiUrl = rtrim(config('services.simpeg.api_url'), '/');
        $apiKey = config('services.simpeg.api_key');

        if (empty($apiKey)) {
            throw new SimpegException('SIMPEG API key not configured', 500, 'Missing SIMPEG_API_KEY in .env');
        }

        $url = $apiUrl . $path;

        Log::info('SIMPEG: Fetching pegawai data', [
            'url' => $url,
            'query' => $query,
        ]);

        try {
            $resp = Http::timeout(10)
                // Retry only transient failures (connection errors / 5xx). Crucially, do NOT let
                // retry() throw on 4xx — a 404 (not found) / 422 (invalid param) must reach
                // handleResponse() so it can be classified, not bubble up as a RequestException.
                ->retry(2, 500, function ($exception) {
                    return $exception instanceof ConnectionException
                        || ($exception instanceof RequestException && $exception->response?->serverError());
                }, throw: false)
                ->withOptions(['verify' => false]) // Disable SSL verification for SPLP
                ->withHeaders([
                    'apikey' => $apiKey,
                    'Accept' => 'application/json',
                ])
                ->get($url, $query);
        } catch (ConnectionException $e) {
            Log::error('SIMPEG: Connection error', [
                'query' => $query,
                'error' => $e->getMessage()
            ]);
            throw new SimpegException('Timeout/connection error to SIMPEG API', 0, $e->getMessage());
        } catch (RequestException $e) {
            // Safety net: if a transient retry is exhausted and Laravel re-throws, still route the
            // final response through handleResponse() so 4xx is classified rather than treated as
            // an unexpected error.
            if ($e->response) {
                return $this->handleResponse($e->response, $invalidReason);
            }
            throw new SimpegException('SIMPEG request failed', 0, $e->getMessage());
        }

        return $this->handleResponse($resp, $invalidReason);
    }

    /**
     * Handle SIMPEG API response
     *
     * Expected success format: {"data": {...pegawai fields...}} or direct data object
     * Expected not found: HTTP 404 or {"ok": false, "valid": false}
     *
     * @param \Illuminate\Http\Client\Response $resp
     * @return array Normalized response
     * @throws SimpegException on API errors
     */
    protected function handleResponse($resp, string $invalidReason = 'invalid_nik'): array
    {
        $status = $resp->status();
        $json = $resp->json();
        $body = $resp->body();

        Log::info('SIMPEG: API Response', [
            'status' => $status,
            'body_preview' => is_array($json) ? array_keys($json) : substr($body, 0, 200)
        ]);

        // === Invalid NIK/NIP format (422, e.g. {"ok":false,"error":"Parameter nik wajib 16 digit"}) ===
        if ($status === 422) {
            return [
                'ok' => false,
                'valid' => false,
                'data' => null,
                'reason' => $invalidReason,
                'message' => is_array($json) ? ($json['error'] ?? null) : null,
            ];
        }

        // === NIK not found (404, 400) ===
        if (in_array($status, [404, 400], true)) {
            return ['ok' => true, 'valid' => false, 'data' => null, 'reason' => 'not_found'];
        }

        // === Check for explicit "valid: false" response ===
        if ($resp->clientError() && is_array($json) && array_key_exists('valid', $json) && $json['valid'] === false) {
            return ['ok' => (bool)($json['ok'] ?? true), 'valid' => false, 'data' => null, 'reason' => 'not_found'];
        }

        // === Authentication / Permission / Rate limit errors ===
        if (in_array($status, [401, 403, 429], true) || $resp->serverError()) {
            $errorBody = $json ?? mb_strimwidth($body, 0, 500, '...');
            Log::error('SIMPEG: API Error', [
                'status' => $status,
                'body' => $errorBody
            ]);
            throw new SimpegException('SIMPEG API error', $status, $errorBody);
        }

        // === Success (2xx) ===
        if ($status >= 200 && $status < 300 && is_array($json)) {
            // Check if data is wrapped in SPLP's "mapData" field
            if (array_key_exists('mapData', $json)) {
                $data = $json['mapData'];
                if (empty($data)) {
                    return ['ok' => true, 'valid' => false, 'data' => null, 'reason' => 'not_found'];
                }
                return ['ok' => true, 'valid' => true, 'data' => $data];
            }

            // Check if data is wrapped in "data" field
            if (array_key_exists('data', $json)) {
                $data = $json['data'];
                if (empty($data)) {
                    return ['ok' => true, 'valid' => false, 'data' => null, 'reason' => 'not_found'];
                }
                return ['ok' => true, 'valid' => true, 'data' => $data];
            }

            // Check if response has "ok" and "valid" fields (already normalized)
            if (array_key_exists('ok', $json) && array_key_exists('valid', $json)) {
                return $json;
            }

            // Assume direct data format
            if (!empty($json)) {
                return ['ok' => true, 'valid' => true, 'data' => $json];
            }

            return ['ok' => true, 'valid' => false, 'data' => null, 'reason' => 'not_found'];
        }

        // === Unexpected response ===
        return ['ok' => true, 'valid' => false, 'data' => null, 'reason' => 'not_found'];
    }
}
