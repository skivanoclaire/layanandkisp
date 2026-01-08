<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhmApiService
{
    protected $host;
    protected $username;
    protected $token;
    protected $baseUrl;

    public function __construct()
    {
        $this->host = config('services.whm.host', 'mail.kaltaraprov.go.id');
        $this->username = config('services.whm.username', 'root');
        $this->token = config('services.whm.token');
        // WHM API v1 endpoint (for server-level operations)
        $this->baseUrl = "https://{$this->host}:2087/json-api";
    }

    /**
     * Make API request to WHM
     */
    protected function makeRequest($function, $params = [])
    {
        try {
            $url = "{$this->baseUrl}/{$function}";

            $response = Http::withHeaders([
                'Authorization' => "WHM {$this->username}:{$this->token}",
            ])
            ->withOptions([
                'verify' => false, // Disable SSL verification for local development
            ])
            ->timeout(30)
            ->get($url, $params);

            if ($response->successful()) {
                $data = $response->json();

                // Check if response contains error
                if (isset($data['metadata']['result']) && $data['metadata']['result'] == 0) {
                    throw new Exception("WHM API Error: " . ($data['metadata']['reason'] ?? 'Unknown error'));
                }

                return $data;
            }

            $errorMessage = "WHM API request failed with status: " . $response->status();

            // Try to parse error from response
            $body = $response->body();
            try {
                $jsonBody = json_decode($body, true);
                if (isset($jsonBody['metadata']['reason'])) {
                    $errorMessage .= " - " . $jsonBody['metadata']['reason'];
                } elseif (isset($jsonBody['error'])) {
                    $errorMessage .= " - " . $jsonBody['error'];
                }
            } catch (\Exception $e) {
                // If parsing fails, just append the raw body
                $errorMessage .= " - " . substr($body, 0, 200);
            }

            Log::error('WHM API Error', [
                'function' => $function,
                'url' => $url,
                'status' => $response->status(),
                'body' => $body
            ]);

            throw new Exception($errorMessage);

        } catch (Exception $e) {
            Log::error('WHM API Exception', [
                'function' => $function,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * List all email accounts from all cPanel accounts on the server
     * This uses WHM API to get all accounts across all domains
     */
    public function listEmailAccounts($domain = null)
    {
        try {
            // First, get all cPanel accounts
            $accountsResponse = $this->makeRequest('listaccts');

            // Handle different response structures
            if (isset($accountsResponse['acct'])) {
                $cpanelAccounts = $accountsResponse['acct'];
            } elseif (isset($accountsResponse['data']['acct'])) {
                $cpanelAccounts = $accountsResponse['data']['acct'];
            } else {
                Log::warning('No cPanel accounts found', ['response_keys' => array_keys($accountsResponse)]);
                return [];
            }
            $allEmailAccounts = [];

            // For each cPanel account, get its email accounts
            foreach ($cpanelAccounts as $account) {
                $cpanelUser = $account['user'];

                // Skip if filtering by domain and this isn't the domain we want
                if ($domain && $account['domain'] !== $domain) {
                    continue;
                }

                try {
                    // Use cPanel API through WHM to get email accounts
                    $url = "{$this->baseUrl}/cpanel";
                    $response = Http::withHeaders([
                        'Authorization' => "WHM {$this->username}:{$this->token}",
                    ])
                    ->withOptions(['verify' => false])
                    ->timeout(60)
                    ->get($url, [
                        'cpanel_jsonapi_user' => $cpanelUser,
                        'cpanel_jsonapi_module' => 'Email',
                        'cpanel_jsonapi_func' => 'listpops',
                        'cpanel_jsonapi_apiversion' => '2'
                    ]);

                    if ($response->successful()) {
                        $data = $response->json();

                        if (isset($data['cpanelresult']['data'])) {
                            foreach ($data['cpanelresult']['data'] as $email) {
                                $allEmailAccounts[] = array_merge($email, [
                                    'cpanel_user' => $cpanelUser,
                                    'domain' => $account['domain']
                                ]);
                            }
                        }
                    }
                } catch (Exception $e) {
                    Log::warning("Failed to get emails for user: {$cpanelUser}", [
                        'error' => $e->getMessage()
                    ]);
                    continue;
                }
            }

            Log::info("Found total email accounts", ['count' => count($allEmailAccounts)]);
            return $allEmailAccounts;

        } catch (Exception $e) {
            Log::error('Failed to list email accounts', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Get disk usage for email accounts
     */
    public function getEmailDiskUsage($domain = null)
    {
        $params = [];

        if ($domain) {
            $params['domain'] = $domain;
        }

        $response = $this->makeRequest('get_disk_usage', $params);

        if (isset($response['data'])) {
            return $response['data'];
        }

        return [];
    }

    /**
     * List all domains on the server
     */
    public function listDomains()
    {
        $response = $this->makeRequest('listaccts');

        if (isset($response['data']['acct'])) {
            return collect($response['data']['acct'])->pluck('domain')->toArray();
        }

        return [];
    }

    /**
     * Get comprehensive email account data
     */
    public function getAllEmailAccountsData()
    {
        try {
            $accounts = $this->listEmailAccounts();
            $emailData = [];

            foreach ($accounts as $account) {
                // Parse email address
                $email = $account['email'] ?? ($account['login'] ?? null);

                // Parse domain from email if not set
                $domain = $account['domain'] ?? null;
                if (!$domain && $email && strpos($email, '@') !== false) {
                    $domain = explode('@', $email)[1];
                }

                // Determine suspended status
                $suspended = 0;
                if (isset($account['suspended_login']) && $account['suspended_login'] == 1) {
                    $suspended = 1;
                } elseif (isset($account['suspended_incoming']) && $account['suspended_incoming'] == 1) {
                    $suspended = 1;
                }

                $emailData[] = [
                    'email' => $email,
                    'domain' => $domain,
                    'user' => $account['cpanel_user'] ?? ($account['user'] ?? null),
                    'disk_used' => $account['diskused'] ?? ($account['_diskused'] ?? 0),
                    'disk_quota' => $account['diskquota'] ?? ($account['_diskquota'] ?? 0),
                    'diskused_readable' => $account['humandiskused'] ?? ($account['diskusedpercent20'] ?? '0 MB'),
                    'diskquota_readable' => $account['humandiskquota'] ?? ($account['quota'] ?? 'Unlimited'),
                    'suspended' => $suspended,
                ];
            }

            Log::info("Processed email accounts data", ['count' => count($emailData)]);

            return [
                'success' => true,
                'accounts' => $emailData,
                'message' => 'Successfully fetched email accounts'
            ];

        } catch (Exception $e) {
            Log::error('Failed to get all email accounts data', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'accounts' => [],
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Create a new email account in cPanel
     *
     * @param string $email Full email address (e.g., user@kaltaraprov.go.id)
     * @param string $password Plain text password for the email account
     * @param int $quota Quota in MB (0 = unlimited)
     * @return array Result with success status and message
     */
    public function createEmailAccount($email, $password, $quota = 0)
    {
        try {
            // Parse email to get username and domain
            if (strpos($email, '@') === false) {
                throw new Exception("Invalid email format: {$email}");
            }

            list($username, $domain) = explode('@', $email, 2);

            Log::info('Creating email account', [
                'email' => $email,
                'username' => $username,
                'domain' => $domain,
                'quota' => $quota
            ]);

            // First, find the cPanel user for this domain
            $accountsResponse = $this->makeRequest('listaccts');

            $cpanelUser = null;
            if (isset($accountsResponse['acct'])) {
                foreach ($accountsResponse['acct'] as $account) {
                    if ($account['domain'] === $domain) {
                        $cpanelUser = $account['user'];
                        break;
                    }
                }
            }

            if (!$cpanelUser) {
                throw new Exception("No cPanel account found for domain: {$domain}");
            }

            Log::info("Found cPanel user for domain", [
                'domain' => $domain,
                'cpanel_user' => $cpanelUser
            ]);

            // Create email account using cPanel API through WHM
            $url = "{$this->baseUrl}/cpanel";
            $response = Http::withHeaders([
                'Authorization' => "WHM {$this->username}:{$this->token}",
            ])
            ->withOptions(['verify' => false])
            ->timeout(60)
            ->get($url, [
                'cpanel_jsonapi_user' => $cpanelUser,
                'cpanel_jsonapi_module' => 'Email',
                'cpanel_jsonapi_func' => 'addpop',
                'cpanel_jsonapi_apiversion' => '2',
                'email' => $username,
                'password' => $password,
                'quota' => $quota,
                'domain' => $domain
            ]);

            if (!$response->successful()) {
                throw new Exception("API request failed with status: " . $response->status());
            }

            $data = $response->json();
            Log::info('Create email API response', ['response' => $data]);

            // Check cPanel API response format
            if (isset($data['cpanelresult'])) {
                $result = $data['cpanelresult'];

                // Check for errors in the result
                if (isset($result['error'])) {
                    throw new Exception("cPanel API Error: " . $result['error']);
                }

                // Check event/data for success
                if (isset($result['event']['result']) && $result['event']['result'] == 1) {
                    return [
                        'success' => true,
                        'message' => 'Email account created successfully',
                        'email' => $email
                    ];
                }

                // Alternative success check
                if (isset($result['data'][0]['result']) && $result['data'][0]['result'] == 1) {
                    return [
                        'success' => true,
                        'message' => $result['data'][0]['reason'] ?? 'Email account created successfully',
                        'email' => $email
                    ];
                }

                // If we have data but no clear success indicator
                if (isset($result['data'])) {
                    $firstData = is_array($result['data']) ? $result['data'][0] : $result['data'];
                    $reason = $firstData['reason'] ?? 'Unknown response';

                    // Check if reason indicates failure
                    if (stripos($reason, 'already exists') !== false) {
                        return [
                            'success' => false,
                            'message' => 'Email account already exists: ' . $reason,
                            'email' => $email
                        ];
                    }

                    throw new Exception("Email creation failed: " . $reason);
                }
            }

            // If we couldn't parse the response properly
            throw new Exception("Unexpected API response format: " . json_encode($data));

        } catch (Exception $e) {
            Log::error('Failed to create email account', [
                'email' => $email,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
                'email' => $email
            ];
        }
    }

    /**
     * Test connection to WHM server
     */
    public function testConnection()
    {
        try {
            $response = $this->makeRequest('version');

            Log::info('WHM version response', ['response' => $response]);

            // Check various possible response structures
            $version = null;

            if (isset($response['data']['version'])) {
                $version = $response['data']['version'];
            } elseif (isset($response['version'])) {
                $version = $response['version'];
            } elseif (isset($response['metadata']['version'])) {
                $version = $response['metadata']['version'];
            }

            if ($version) {
                return [
                    'success' => true,
                    'version' => $version,
                    'message' => 'Successfully connected to WHM server'
                ];
            }

            // If we got here, connection works but version not found
            return [
                'success' => true,
                'version' => 'Connected',
                'message' => 'Connection successful'
            ];

        } catch (Exception $e) {
            Log::error('WHM test connection failed', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}
