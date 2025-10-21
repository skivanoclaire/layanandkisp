<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\ConnectionException;
use App\Exceptions\SimpegException;

class SimpegClient
{
    public function byNik(string $nik): array
    {
        $base = rtrim(config('services.simpeg.base_url'), '/');

        try {
            $resp = Http::timeout(8)
                ->retry(2, 250)
                ->withHeaders([
                    'X-API-KEY' => config('services.simpeg.api_key'),
                    'Accept'    => 'application/json',
                ])
                ->get("{$base}/pegawai/by-nik", ['nik' => $nik]); // TANPA ->throw()
        } catch (ConnectionException $e) {
            throw new SimpegException('Timeout/connection error to SIMPEG', 0, $e->getMessage());
        }

        $status = $resp->status();
        $json   = $resp->json();

        // === NIK tidak terdata (variasi gateway) ===
        if (in_array($status, [404, 400, 422], true)) {
            return ['ok' => true, 'valid' => false, 'data' => null];
        }
        if ($resp->clientError() && is_array($json) && array_key_exists('valid', $json) && $json['valid'] === false) {
            return ['ok' => (bool)($json['ok'] ?? true), 'valid' => false, 'data' => null];
        }

        // === error sebenarnya ===
        if (in_array($status, [401, 403, 429], true) || $resp->serverError()) {
            $body = $json ?? mb_strimwidth($resp->body(), 0, 500, '...');
            throw new SimpegException('SIMPEG HTTP error', $status, $body);
        }

        // === sukses 2xx ===
        return is_array($json) ? $json : ['ok' => true, 'valid' => false, 'data' => null];
    }
}
