<?php

namespace App\Services;

use App\Exceptions\YourlsException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Klien API YOURLS (link.kaltaraprov.go.id).
 *
 * Auth: signature token passwordless (config services.yourls.signature).
 * Aksi "update" & "delete" disediakan oleh plugin "portal-api" di instalasi YOURLS.
 *
 * Semua method mengembalikan array hasil JSON dari YOURLS, atau melempar YourlsException
 * bila terjadi error koneksi / konfigurasi. Error "domain" (mis. keyword sudah dipakai)
 * dikembalikan apa adanya dalam array (cek key 'status' === 'fail' dan 'code').
 */
class YourlsClient
{
    private string $endpoint;
    private string $base;
    private ?string $signature;

    public function __construct()
    {
        $this->endpoint  = rtrim((string) config('services.yourls.url'), '/');
        $this->base      = rtrim((string) config('services.yourls.base'), '/');
        $this->signature = config('services.yourls.signature');
    }

    public function baseUrl(): string
    {
        return $this->base;
    }

    /**
     * Buat short URL baru.
     *
     * @return array hasil YOURLS. Sukses: ['status'=>'success','shorturl'=>...,'url'=>['keyword'=>...], ...]
     *               Gagal domain: ['status'=>'fail','code'=>'error:keyword'|'error:url', 'message'=>..., 'url'=>[...]]
     */
    public function createShortUrl(string $url, ?string $keyword = null, ?string $title = null): array
    {
        $params = ['action' => 'shorturl', 'url' => $url];
        if ($keyword !== null && $keyword !== '') $params['keyword'] = $keyword;
        if ($title !== null && $title !== '')     $params['title']   = $title;

        return $this->call($params);
    }

    /** Perluas short URL -> long URL. */
    public function expand(string $keyword): array
    {
        return $this->call(['action' => 'expand', 'shorturl' => $keyword]);
    }

    /** Statistik satu short URL (termasuk 'clicks'). */
    public function urlStats(string $keyword): array
    {
        return $this->call(['action' => 'url-stats', 'shorturl' => $keyword]);
    }

    /** Ubah URL tujuan (dan opsional judul) sebuah short link. Butuh plugin portal-api. */
    public function updateUrl(string $keyword, string $newUrl, ?string $title = null): array
    {
        $params = ['action' => 'update', 'keyword' => $keyword, 'url' => $newUrl];
        if ($title !== null) $params['title'] = $title;

        return $this->call($params);
    }

    /** Hapus short link. Butuh plugin portal-api. */
    public function delete(string $keyword): array
    {
        return $this->call(['action' => 'delete', 'keyword' => $keyword]);
    }

    /** Versi YOURLS — berguna untuk health check integrasi. */
    public function version(): array
    {
        return $this->call(['action' => 'version']);
    }

    /**
     * Jalankan panggilan API.
     *
     * @throws YourlsException
     */
    protected function call(array $params): array
    {
        if (empty($this->endpoint)) {
            throw new YourlsException('YOURLS endpoint belum dikonfigurasi (YOURLS_API_URL).');
        }
        if (empty($this->signature)) {
            throw new YourlsException('YOURLS signature token belum dikonfigurasi (YOURLS_API_SIGNATURE).');
        }

        $params['format']    = 'json';
        $params['signature'] = $this->signature;

        try {
            $resp = Http::timeout(15)
                // Hanya ulang saat gangguan koneksi; respons 4xx YOURLS adalah "error domain"
                // yang valid (mis. keyword sudah dipakai) dan harus dikembalikan apa adanya.
                ->retry(2, 500, fn ($e) => $e instanceof ConnectionException, throw: false)
                ->asForm()
                ->post($this->endpoint, $params);
        } catch (ConnectionException $e) {
            Log::error('YOURLS: connection error', ['action' => $params['action'] ?? null, 'error' => $e->getMessage()]);
            throw new YourlsException('Gagal terhubung ke YOURLS: ' . $e->getMessage(), 0, $e);
        }

        $json = $resp->json();

        if (!is_array($json)) {
            Log::error('YOURLS: respons tidak valid', ['status' => $resp->status(), 'body' => mb_strimwidth((string) $resp->body(), 0, 500, '...')]);
            throw new YourlsException('Respons YOURLS tidak valid (HTTP ' . $resp->status() . ').');
        }

        // HTTP 401/403 dari yourls-api.php = signature salah / auth gagal
        if (in_array($resp->status(), [401, 403], true)) {
            Log::error('YOURLS: auth gagal', ['body' => $json]);
            throw new YourlsException('Autentikasi ke YOURLS gagal (signature token salah / kedaluwarsa).');
        }

        return $json;
    }
}
