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
        'instansi_id',
        'nama_sistem',
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
        'description',
        'latar_belakang',
        'manfaat_aplikasi',
        'tahun_pembuatan',
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
        // Electronic System Category
        'esc_answers',
        'esc_total_score',
        'esc_category',
        'esc_document_path',
        'esc_filled_at',
        'esc_updated_by',
        // Data Classification
        'dc_data_name',
        'dc_data_attributes',
        'dc_confidentiality',
        'dc_integrity',
        'dc_availability',
        'dc_total_score',
        'dc_filled_at',
        'dc_updated_by',
    ];

    protected $casts = [
        'is_proxied' => 'boolean',
        'last_checked_at' => 'datetime',
        'esc_answers' => 'array',
        'esc_filled_at' => 'datetime',
        'dc_filled_at' => 'datetime',
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
    public function instansi()
    {
        return $this->belongsTo(UnitKerja::class, 'instansi_id');
    }

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

    public function escUpdatedBy()
    {
        return $this->belongsTo(User::class, 'esc_updated_by');
    }

    public function dcUpdatedBy()
    {
        return $this->belongsTo(User::class, 'dc_updated_by');
    }

    public function surveiKepuasan()
    {
        return $this->hasMany(SurveiKepuasanLayanan::class);
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

    // Electronic System Category Methods

    /**
     * Calculate total score from ESC answers
     */
    public function calculateEscScore(): int
    {
        if (!$this->esc_answers) {
            return 0;
        }

        $score = 0;
        $scoreMap = ['A' => 5, 'B' => 2, 'C' => 1];

        foreach ($this->esc_answers as $answer) {
            $score += $scoreMap[$answer] ?? 0;
        }

        return $score;
    }

    /**
     * Get ESC category based on score
     */
    public function getEscCategoryFromScore(int $score): string
    {
        if ($score >= 36) return 'Strategis';
        if ($score >= 16) return 'Tinggi';
        return 'Rendah';
    }

    /**
     * Update ESC score and category
     */
    public function updateEscScoreAndCategory(): void
    {
        $score = $this->calculateEscScore();
        $this->esc_total_score = $score;
        $this->esc_category = $this->getEscCategoryFromScore($score);
        $this->esc_filled_at = now();
        $this->save();
    }

    /**
     * Check if ESC questionnaire is completed
     */
    public function hasCompletedEsc(): bool
    {
        return !empty($this->esc_answers) && count($this->esc_answers) === 10;
    }

    /**
     * Get formatted ESC document name
     */
    public function getEscDocumentNameAttribute(): ?string
    {
        return $this->esc_document_path ? basename($this->esc_document_path) : null;
    }

    // Data Classification Methods

    /**
     * Calculate total score from Data Classification
     */
    public function calculateDcScore(): int
    {
        $scoreMap = ['Rendah' => 1, 'Sedang' => 3, 'Tinggi' => 5];

        $confidentiality = $scoreMap[$this->dc_confidentiality] ?? 0;
        $integrity = $scoreMap[$this->dc_integrity] ?? 0;
        $availability = $scoreMap[$this->dc_availability] ?? 0;

        return $confidentiality + $integrity + $availability;
    }

    /**
     * Update DC score
     */
    public function updateDcScore(): void
    {
        $this->dc_total_score = $this->calculateDcScore();
        $this->dc_filled_at = now();
        $this->save();
    }

    /**
     * Check if Data Classification is completed
     */
    public function hasCompletedDc(): bool
    {
        return !empty($this->dc_data_name)
            && !empty($this->dc_confidentiality)
            && !empty($this->dc_integrity)
            && !empty($this->dc_availability);
    }
}
