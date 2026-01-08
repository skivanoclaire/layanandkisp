<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class CpanelEmailService
{
    protected $client;
    protected $host;
    protected $token;
    protected $cpanelUser;

    public function __construct()
    {
        $this->host = env('WHM_HOST');
        $this->token = env('WHM_TOKEN');
        $this->cpanelUser = env('WHM_CPANEL_USER', 'kaltara'); // Default cpanel username

        $this->client = new Client([
            'base_uri' => "https://{$this->host}:2087",
            'verify' => false, // For self-signed certs
            'headers' => [
                'Authorization' => 'WHM root:' . $this->token,
            ],
            'timeout' => 30,
        ]);
    }

    /**
     * Reset password for email account via WHM cPanel API
     *
     * @param string $email Full email address (e.g., user@domain.com)
     * @param string $newPassword The new password
     * @return array ['success' => bool, 'message' => string]
     */
    public function resetPassword(string $email, string $newPassword): array
    {
        try {
            // Extract domain and email user from full email
            list($emailUser, $domain) = explode('@', $email);

            Log::info("CPanel API: Attempting to reset password for {$email}");

            // Use cPanel API via WHM
            // https://docs.cpanel.net/cpanel/email/email-accounts/#modify-a-password
            $response = $this->client->get('/json-api/cpanel', [
                'query' => [
                    'cpanel_jsonapi_user' => $this->cpanelUser,
                    'cpanel_jsonapi_module' => 'Email',
                    'cpanel_jsonapi_func' => 'passwdpop',
                    'domain' => $domain,
                    'email' => $emailUser,
                    'password' => $newPassword,
                ]
            ]);

            $result = json_decode($response->getBody()->getContents(), true);

            // Check cPanel API response
            // Response format: {"cpanelresult":{"data":[{"result":1,"reason":""}]}}
            if (isset($result['cpanelresult']['data'][0]['result']) &&
                $result['cpanelresult']['data'][0]['result'] == 1) {

                Log::info("CPanel API: Password reset successful for {$email}");
                return [
                    'success' => true,
                    'message' => 'Password berhasil direset di cPanel'
                ];
            }

            // API call succeeded but operation failed
            $errorMessage = $result['cpanelresult']['data'][0]['reason'] ?? 'Unknown error from cPanel API';
            Log::error("CPanel API: Password reset failed for {$email}: {$errorMessage}");

            return [
                'success' => false,
                'message' => "cPanel Error: {$errorMessage}"
            ];

        } catch (\GuzzleHttp\Exception\ConnectException $e) {
            Log::error("CPanel API: Connection error - " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Tidak dapat terhubung ke server cPanel. Periksa konfigurasi WHM_HOST.'
            ];
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            // 4xx errors (authentication, not found, etc.)
            $statusCode = $e->getResponse()->getStatusCode();
            $body = $e->getResponse()->getBody()->getContents();
            Log::error("CPanel API: Client error ({$statusCode}) - " . $body);

            if ($statusCode == 401) {
                return [
                    'success' => false,
                    'message' => 'Autentikasi WHM gagal. Periksa WHM_TOKEN.'
                ];
            }

            return [
                'success' => false,
                'message' => "cPanel API Error: HTTP {$statusCode}"
            ];
        } catch (\GuzzleHttp\Exception\ServerException $e) {
            // 5xx errors
            Log::error("CPanel API: Server error - " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Server cPanel mengalami masalah. Silakan coba lagi nanti.'
            ];
        } catch (\Exception $e) {
            Log::error("CPanel API: Unexpected error - " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Test connection to WHM API
     *
     * @return array ['success' => bool, 'message' => string]
     */
    public function testConnection(): array
    {
        try {
            // Simple test: try to call a basic cPanel function
            $response = $this->client->get('/json-api/cpanel', [
                'query' => [
                    'cpanel_jsonapi_user' => $this->cpanelUser,
                    'cpanel_jsonapi_module' => 'Email',
                    'cpanel_jsonapi_func' => 'listpops',
                ]
            ]);

            $result = json_decode($response->getBody()->getContents(), true);

            if (isset($result['cpanelresult'])) {
                return [
                    'success' => true,
                    'message' => 'Koneksi ke WHM/cPanel berhasil. cPanel User: ' . $this->cpanelUser
                ];
            }

            return [
                'success' => false,
                'message' => 'Response tidak sesuai format'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Koneksi gagal: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get list of email accounts for the domain
     *
     * @param string $domain Domain name
     * @return array|null
     */
    public function listEmailAccounts(string $domain): ?array
    {
        try {
            $response = $this->client->get('/json-api/cpanel', [
                'query' => [
                    'cpanel_jsonapi_user' => $this->cpanelUser,
                    'cpanel_jsonapi_module' => 'Email',
                    'cpanel_jsonapi_func' => 'listpops',
                ]
            ]);

            $result = json_decode($response->getBody()->getContents(), true);

            if (isset($result['cpanelresult']['data'])) {
                return $result['cpanelresult']['data'];
            }

            return null;
        } catch (\Exception $e) {
            Log::error("CPanel API: Error listing emails - " . $e->getMessage());
            return null;
        }
    }
}
