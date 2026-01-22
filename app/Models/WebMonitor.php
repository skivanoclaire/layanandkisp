<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Services\CloudflareService;

class WebMonitor extends Model
{
    use HasFactory;

    protected $table = 'web_monitors';

    protected $fillable = [
        'nama_instansi',
        'subdomain',
        'cloudflare_record_id',
        'ip_address',
        'is_proxied',
        'status',
        'keterangan',
        'jenis',
        'last_checked_at',
        'check_error',
        // Subdomain request link
        'subdomain_request_id',
        // Informasi Aplikasi
        'nama_aplikasi',
        'developer',
        'contact_person',
        'contact_phone',
        // Teknologi
        'programming_language_id',
        'programming_language_version',
        'framework_id',
        'framework_version',
        'database_id',
        'database_version',
        'frontend_tech',
        // Server
        'server_ownership',
        'server_owner_name',
        'server_location_id',
    ];

    protected $casts = [
        'is_proxied' => 'boolean',
        'last_checked_at' => 'datetime',
    ];

    // Jenis options
    public const JENIS_WEBSITE_RESMI = 'Website Resmi';
    public const JENIS_APLIKASI_LAYANAN = 'Aplikasi Layanan Publik';
    public const JENIS_APLIKASI_ADMINISTRASI = 'Aplikasi Administrasi Pemerintah';
    public const JENIS_APLIKASI_FUNGSI = 'Aplikasi Fungsi Tertentu';

    public static function jenisOptions(): array
    {
        return [
            self::JENIS_WEBSITE_RESMI,
            self::JENIS_APLIKASI_LAYANAN,
            self::JENIS_APLIKASI_ADMINISTRASI,
            self::JENIS_APLIKASI_FUNGSI,
        ];
    }

    // Relationships
    public function subdomainRequest()
    {
        return $this->belongsTo(SubdomainRequest::class);
    }

    public function programmingLanguage()
    {
        return $this->belongsTo(ProgrammingLanguage::class);
    }

    public function framework()
    {
        return $this->belongsTo(Framework::class);
    }

    public function database()
    {
        return $this->belongsTo(Database::class);
    }

    public function serverLocation()
    {
        return $this->belongsTo(ServerLocation::class);
    }

    public function techHistories()
    {
        return $this->hasMany(SubdomainTechHistory::class);
    }

    public function surveiKepuasan()
    {
        return $this->hasMany(SurveiKepuasanLayanan::class, 'web_monitor_id');
    }

    // Status attribute accessor
    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'active';
    }

    // Check and update domain status
    public function checkStatus(): void
    {
        // If subdomain is null, skip checking (IP only, no domain)
        if (empty($this->subdomain)) {
            $this->update([
                'status' => 'no-domain',
                'last_checked_at' => now(),
                'check_error' => null,
            ]);
            return;
        }

        $cloudflare = new CloudflareService();
        $result = $cloudflare->checkDomainStatus($this->subdomain);

        $this->update([
            'status' => $result['is_active'] ? 'active' : 'inactive',
            'last_checked_at' => now(),
            'check_error' => $result['error'],
        ]);
    }

    // Sync with Cloudflare DNS record
    public function syncWithCloudflare(): bool
    {
        if (!$this->cloudflare_record_id) {
            return false;
        }

        $cloudflare = new CloudflareService();
        $record = $cloudflare->getDnsRecord($this->cloudflare_record_id);

        if ($record) {
            $this->update([
                'subdomain' => $record['name'],
                'ip_address' => $record['content'],
                'is_proxied' => $record['proxied'] ?? false,
            ]);
            return true;
        }

        return false;
    }

    // Update Cloudflare DNS record
    public function updateCloudflareRecord(string $ipAddress, bool $isProxied = false): bool
    {
        if (!$this->cloudflare_record_id) {
            return false;
        }

        $cloudflare = new CloudflareService();
        return $cloudflare->updateDnsRecord($this->cloudflare_record_id, [
            'type' => 'A',
            'name' => $this->subdomain,
            'content' => $ipAddress,
            'proxied' => $isProxied,
            'ttl' => $isProxied ? 1 : 3600,
        ]);
    }

    // Update subdomain name in Cloudflare DNS record
    public function updateSubdomainName(string $newSubdomainName): bool
    {
        if (!$this->cloudflare_record_id) {
            return false;
        }

        $cloudflare = new CloudflareService();
        $success = $cloudflare->updateDnsRecord($this->cloudflare_record_id, [
            'type' => 'A',
            'name' => $newSubdomainName,
            'content' => $this->ip_address,
            'proxied' => false,  // Always DNS Only for name changes
            'ttl' => 3600,
        ]);

        if ($success) {
            $this->subdomain = $newSubdomainName;
            $this->is_proxied = false;  // Ensure it's set to DNS Only
            $this->save();
        }

        return $success;
    }
}
