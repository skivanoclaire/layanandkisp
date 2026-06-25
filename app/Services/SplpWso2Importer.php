<?php

namespace App\Services;

use RuntimeException;
use Symfony\Component\Yaml\Yaml;

/**
 * Parser ekspor API dari SPLP (WSO2 API Manager) → data prefill untuk Master Data Layanan SPLP.
 *
 * Menerima file ekspor berupa:
 *  - .zip bundle WSO2 (berisi api.yaml + Definitions/swagger.yaml), atau
 *  - api.yaml mentah.
 *
 * CATATAN KEAMANAN: nilai secret (mis. header X-API-KEY di operationPolicies) TIDAK
 * dibaca/disimpan — sesuai prinsip "kredensial via kanal aman".
 */
class SplpWso2Importer
{
    /**
     * @return array{prefill: array<string,mixed>, info: array<string,mixed>, warnings: string[]}
     */
    public function import(string $path, ?string $originalName = null): array
    {
        $ext = strtolower(pathinfo($originalName ?? $path, PATHINFO_EXTENSION));

        if ($ext === 'zip') {
            [$apiYaml, $hasSwagger, $operationsCount] = $this->readFromZip($path);
        } else {
            $apiYaml = @file_get_contents($path);
            $hasSwagger = false;
            $operationsCount = null;
            if ($apiYaml === false || trim($apiYaml) === '') {
                throw new RuntimeException('File tidak dapat dibaca atau kosong.');
            }
        }

        $parsed = $this->parseYaml($apiYaml);
        $data = $parsed['data'] ?? null;

        if (!is_array($data) || (($parsed['type'] ?? null) !== 'api' && empty($data['name']))) {
            throw new RuntimeException('Format tidak dikenali. Pastikan ini ekspor API dari SPLP (WSO2) yang berisi api.yaml.');
        }

        return $this->map($data, $hasSwagger, $operationsCount);
    }

    /**
     * @return array{0: string, 1: bool, 2: int|null}
     */
    private function readFromZip(string $path): array
    {
        if (!class_exists(\ZipArchive::class)) {
            throw new RuntimeException('Ekstensi ZIP PHP tidak aktif. Unggah file api.yaml langsung, atau aktifkan ext-zip.');
        }

        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) {
            throw new RuntimeException('Gagal membuka file ZIP.');
        }

        $apiYaml = null;
        $hasSwagger = false;
        $operationsCount = null;

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            $lower = strtolower($name);

            // api.yaml di root bundle (bukan Policies/*.yaml)
            if (preg_match('#(^|/)api\.yaml$#', $lower) && !str_contains($lower, '/policies/')) {
                $apiYaml = $zip->getFromIndex($i);
            }
            if (str_ends_with($lower, 'definitions/swagger.yaml') || str_ends_with($lower, 'definitions/swagger.json')) {
                $hasSwagger = true;
                $swagger = $zip->getFromIndex($i);
                $operationsCount = $this->countSwaggerOperations($swagger);
            }
        }
        $zip->close();

        if (!$apiYaml) {
            throw new RuntimeException('api.yaml tidak ditemukan dalam ZIP. Pastikan ini ekspor API SPLP (WSO2).');
        }

        return [$apiYaml, $hasSwagger, $operationsCount];
    }

    private function parseYaml(string $content): array
    {
        try {
            $parsed = Yaml::parse($content);
        } catch (\Throwable $e) {
            throw new RuntimeException('YAML tidak valid: ' . $e->getMessage());
        }

        if (!is_array($parsed)) {
            throw new RuntimeException('Isi api.yaml tidak valid.');
        }

        return $parsed;
    }

    private function countSwaggerOperations(?string $swagger): ?int
    {
        if (!$swagger) {
            return null;
        }
        try {
            $spec = Yaml::parse($swagger);
            $paths = $spec['paths'] ?? [];
            $count = 0;
            foreach ($paths as $methods) {
                if (is_array($methods)) {
                    $count += count(array_intersect(array_keys($methods), ['get', 'post', 'put', 'patch', 'delete', 'options', 'head']));
                }
            }
            return $count;
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @return array{prefill: array<string,mixed>, info: array<string,mixed>, warnings: string[]}
     */
    private function map(array $data, bool $hasSwagger, ?int $operationsCount): array
    {
        $warnings = [];

        $endpointConfig = $data['endpointConfig'] ?? [];
        $prodUrl = data_get($endpointConfig, 'production_endpoints.url');
        $sandboxUrl = data_get($endpointConfig, 'sandbox_endpoints.url');

        $schemes = (array) ($data['securityScheme'] ?? []);
        $authType = $this->mapAuthType($schemes);

        $visibility = strtoupper((string) ($data['visibility'] ?? 'PUBLIC'));
        $klasifikasi = $visibility === 'PUBLIC' ? 'publik' : 'terbatas';

        $lifeCycle = strtoupper((string) ($data['lifeCycleStatus'] ?? ''));
        $status = $lifeCycle === 'PUBLISHED' ? 'aktif' : 'nonaktif';

        $operations = collect($data['operations'] ?? [])
            ->map(fn ($op) => strtoupper((string) ($op['verb'] ?? '')) . ' ' . (string) ($op['target'] ?? ''))
            ->filter()
            ->values()
            ->all();

        $deskripsi = $this->buildDeskripsi($data, $operations, $schemes);

        // Deteksi secret yang sengaja TIDAK diimpor (untuk diberitahukan ke admin)
        if ($this->hasInjectedSecret($data)) {
            $warnings[] = 'Ekspor memuat secret backend (mis. header X-API-KEY) — nilai ini sengaja TIDAK diimpor demi keamanan.';
        }
        if (!$prodUrl && $sandboxUrl) {
            $warnings[] = 'URL endpoint produksi tidak ditemukan; memakai URL sandbox.';
        }

        return [
            'prefill' => [
                'nama_layanan' => $data['name'] ?? null,
                'route_path' => $data['context'] ?? null,
                'backend_url' => $prodUrl ?: $sandboxUrl,
                'environment' => 'produksi',
                'auth_type' => $authType,
                'klasifikasi_data' => $klasifikasi,
                'status' => $status,
                'gateway_service_id' => $data['id'] ?? null,
                'deskripsi' => $deskripsi,
            ],
            'info' => [
                'nama' => $data['name'] ?? '-',
                'versi' => $data['version'] ?? '-',
                'provider' => $data['provider'] ?? '-',
                'sandbox_url' => $sandboxUrl,
                'jumlah_endpoint' => $operationsCount ?? count($operations),
                'has_swagger' => $hasSwagger,
                'technical_owner' => data_get($data, 'businessInformation.technicalOwner'),
            ],
            'warnings' => $warnings,
        ];
    }

    private function mapAuthType(array $schemes): string
    {
        $schemes = array_map('strtolower', $schemes);
        if (in_array('api_key', $schemes, true)) {
            return 'apikey';
        }
        if (in_array('oauth2', $schemes, true)) {
            return 'oauth2';
        }
        return 'none';
    }

    private function hasInjectedSecret(array $data): bool
    {
        foreach ($data['operations'] ?? [] as $op) {
            foreach (data_get($op, 'operationPolicies.request', []) as $policy) {
                if (!empty(data_get($policy, 'parameters.headerValue'))) {
                    return true;
                }
            }
        }
        return false;
    }

    private function buildDeskripsi(array $data, array $operations, array $schemes): string
    {
        $lines = [];
        $lines[] = 'API ' . ($data['name'] ?? '') . ' versi ' . ($data['version'] ?? '-') . '.';

        if ($provider = $data['provider'] ?? null) {
            $lines[] = 'Penyedia: ' . $provider . '.';
        }
        if ($owner = data_get($data, 'businessInformation.technicalOwner')) {
            $email = data_get($data, 'businessInformation.technicalOwnerEmail');
            $lines[] = 'Pemilik teknis: ' . $owner . ($email ? " ({$email})" : '') . '.';
        }
        if ($tags = $data['tags'] ?? []) {
            $lines[] = 'Kategori: ' . implode(', ', (array) $tags) . '.';
        }
        if ($schemes) {
            $lines[] = 'Keamanan: ' . implode(', ', (array) $schemes) . '.';
        }
        if ($operations) {
            $lines[] = '';
            $lines[] = 'Endpoint:';
            foreach (array_slice($operations, 0, 30) as $op) {
                $lines[] = '- ' . $op;
            }
        }

        return trim(implode("\n", $lines));
    }
}
