<?php

namespace Database\Seeders;

use App\Models\SubdomainRequest;
use App\Models\SubdomainRequestLog;
use App\Models\User;
use App\Models\UnitKerja;
use App\Models\ProgrammingLanguage;
use App\Models\Database;
use App\Models\ServerLocation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SubdomainRequestTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get user dummy10
        $user = User::where('email', 'userdummy10@kaltaraprov.go.id')->first();

        if (!$user) {
            $this->command->error('User userdummy10@kaltaraprov.go.id tidak ditemukan!');
            return;
        }

        // Get reference data
        $unitKerja = UnitKerja::first();
        $phpLang = ProgrammingLanguage::where('name', 'PHP')->first();
        $mysqlDb = Database::where('name', 'MySQL')->first();
        $serverLoc = ServerLocation::first();

        $this->command->info('Membuat sample subdomain requests untuk user: ' . $user->email);

        // Sample 1: Website Resmi dengan backup mingguan
        $request1 = $this->createSubdomainRequest([
            'user_id' => $user->id,
            'nama' => $user->name,
            'nip' => $user->nip,
            'unit_kerja_id' => $unitKerja->id,
            'email_pemohon' => $user->email,
            'no_hp' => $user->phone,
            'subdomain_requested' => 'testing-website-resmi',
            'ip_address' => '103.156.110.250',
            'jenis_website' => 'Website Resmi',
            'description' => 'Website profil resmi untuk keperluan testing sistem',
            'nama_aplikasi' => 'Portal Informasi Testing',
            'latar_belakang' => 'Dibuat untuk testing sistem pengelolaan subdomain',
            'manfaat_aplikasi' => 'Memberikan informasi kepada masyarakat tentang kegiatan instansi',
            'tahun_pembuatan' => 2024,
            'developer' => 'Tim IT Internal',
            'contact_person' => 'John Doe',
            'contact_phone' => '081234567890',
            'programming_language_id' => $phpLang->id,
            'programming_language_version' => '8.2',
            'framework_id' => null,
            'framework_version' => null,
            'database_id' => $mysqlDb->id,
            'database_version' => '8.0',
            'frontend_tech' => 'Bootstrap 5, jQuery',
            'backup_frequency' => 'Mingguan',
            'backup_retention' => '90 hari',
            'has_bcp' => 'Belum',
            'has_drp' => 'Dalam Proses',
            'rto' => '1-3 hari',
            'maintenance_schedule' => 'Setiap hari Minggu pukul 02:00 - 04:00 WIB',
            'has_https' => true,
        ]);

        $this->command->info('✓ Created: ' . $request1->subdomain_requested);

        // Sample 2: Aplikasi Layanan Publik dengan backup harian
        $request2 = $this->createSubdomainRequest([
            'user_id' => $user->id,
            'nama' => $user->name,
            'nip' => $user->nip,
            'unit_kerja_id' => $unitKerja->id,
            'email_pemohon' => $user->email,
            'no_hp' => $user->phone,
            'subdomain_requested' => 'testing-layanan-publik',
            'ip_address' => '103.156.110.249',
            'jenis_website' => 'Aplikasi Layanan Publik',
            'description' => 'Sistem layanan pengaduan masyarakat untuk testing',
            'nama_aplikasi' => 'Sistem Pengaduan Online Testing',
            'latar_belakang' => 'Meningkatkan akses masyarakat terhadap layanan pengaduan',
            'manfaat_aplikasi' => 'Mempermudah masyarakat dalam menyampaikan aspirasi dan pengaduan',
            'tahun_pembuatan' => 2025,
            'developer' => 'PT Digital Solution',
            'contact_person' => 'Jane Smith',
            'contact_phone' => '082345678901',
            'programming_language_id' => $phpLang->id,
            'programming_language_version' => '8.3',
            'framework_id' => null,
            'framework_version' => null,
            'database_id' => $mysqlDb->id,
            'database_version' => '8.0',
            'frontend_tech' => 'Vue.js 3, Tailwind CSS',
            'backup_frequency' => 'Harian',
            'backup_retention' => '30 hari',
            'has_bcp' => 'Ya',
            'has_drp' => 'Ya',
            'rto' => '4-24 jam',
            'maintenance_schedule' => null,
            'has_https' => true,
        ]);

        $this->command->info('✓ Created: ' . $request2->subdomain_requested);

        // Sample 3: Aplikasi Administrasi dengan backup realtime
        $request3 = $this->createSubdomainRequest([
            'user_id' => $user->id,
            'nama' => $user->name,
            'nip' => $user->nip,
            'unit_kerja_id' => $unitKerja->id,
            'email_pemohon' => $user->email,
            'no_hp' => $user->phone,
            'subdomain_requested' => 'testing-simpeg',
            'ip_address' => '103.156.110.248',
            'jenis_website' => 'Aplikasi Administrasi Pemerintah',
            'description' => 'Sistem informasi kepegawaian untuk testing',
            'nama_aplikasi' => 'SIMPEG Testing',
            'latar_belakang' => 'Digitalisasi pengelolaan data kepegawaian',
            'manfaat_aplikasi' => 'Efisiensi pengelolaan administrasi kepegawaian dan data ASN',
            'tahun_pembuatan' => 2024,
            'developer' => 'Tim IT BKPSDM',
            'contact_person' => 'Admin BKPSDM',
            'contact_phone' => '083456789012',
            'programming_language_id' => null,
            'other_programming_language' => 'Python',
            'programming_language_version' => '3.11',
            'framework_id' => null,
            'other_framework' => 'Django',
            'framework_version' => '4.2',
            'database_id' => $mysqlDb->id,
            'database_version' => '8.0',
            'frontend_tech' => 'React 18, Material-UI',
            'backup_frequency' => 'Realtime',
            'backup_retention' => '14 hari',
            'has_bcp' => 'Ya',
            'has_drp' => 'Ya',
            'rto' => '1-4 jam',
            'maintenance_schedule' => 'Setiap akhir bulan pukul 23:00 - 01:00 WIB',
            'has_https' => true,
        ]);

        $this->command->info('✓ Created: ' . $request3->subdomain_requested);

        // Sample 4: Aplikasi Fungsi Tertentu (API) dengan backup bulanan
        $request4 = $this->createSubdomainRequest([
            'user_id' => $user->id,
            'nama' => $user->name,
            'nip' => $user->nip,
            'unit_kerja_id' => $unitKerja->id,
            'email_pemohon' => $user->email,
            'no_hp' => $user->phone,
            'subdomain_requested' => 'testing-api',
            'ip_address' => '103.156.110.247',
            'jenis_website' => 'Aplikasi Fungsi Tertentu',
            'description' => 'REST API untuk integrasi antar sistem',
            'nama_aplikasi' => 'Integration API Gateway Testing',
            'latar_belakang' => 'Kebutuhan integrasi data antar aplikasi',
            'manfaat_aplikasi' => 'Mempermudah pertukaran data antar sistem secara realtime',
            'tahun_pembuatan' => 2025,
            'developer' => 'Tim DevOps',
            'contact_person' => 'System Administrator',
            'contact_phone' => '084567890123',
            'programming_language_id' => null,
            'other_programming_language' => 'Golang',
            'programming_language_version' => '1.21',
            'framework_id' => null,
            'other_framework' => 'Gin Framework',
            'framework_version' => '1.9',
            'database_id' => $mysqlDb->id,
            'database_version' => '8.0',
            'frontend_tech' => null,
            'backup_frequency' => 'Bulanan',
            'backup_retention' => '365 hari',
            'has_bcp' => 'Dalam Proses',
            'has_drp' => 'Belum',
            'rto' => '> 3 hari',
            'maintenance_schedule' => null,
            'has_https' => true,
        ]);

        $this->command->info('✓ Created: ' . $request4->subdomain_requested);

        // Sample 5: Server DKISP dengan auto IP
        $request5 = $this->createSubdomainRequest([
            'user_id' => $user->id,
            'nama' => $user->name,
            'nip' => $user->nip,
            'unit_kerja_id' => $unitKerja->id,
            'email_pemohon' => $user->email,
            'no_hp' => $user->phone,
            'subdomain_requested' => 'testing-dkisp-server',
            'ip_address' => '103.156.110.246', // Auto-assigned
            'jenis_website' => 'Website Resmi',
            'description' => 'Website dengan server di DKISP',
            'nama_aplikasi' => 'Portal DKISP Testing',
            'latar_belakang' => 'Testing auto-assignment IP untuk server DKISP',
            'manfaat_aplikasi' => 'Portal informasi dengan infrastruktur lokal',
            'tahun_pembuatan' => 2025,
            'developer' => 'DKISP Team',
            'contact_person' => 'Network Admin',
            'contact_phone' => '085678901234',
            'programming_language_id' => $phpLang->id,
            'programming_language_version' => '8.2',
            'framework_id' => null,
            'framework_version' => null,
            'database_id' => $mysqlDb->id,
            'database_version' => '10.11',
            'frontend_tech' => 'Vanilla JS, Bootstrap',
            'backup_frequency' => 'Harian',
            'backup_retention' => '60 hari',
            'has_bcp' => null,
            'has_drp' => null,
            'rto' => null,
            'maintenance_schedule' => 'Setiap hari Sabtu pukul 01:00 - 03:00 WIB',
            'has_https' => true,
        ]);

        $this->command->info('✓ Created: ' . $request5->subdomain_requested);

        $this->command->info("\n✅ Selesai! Total " . SubdomainRequest::where('user_id', $user->id)->count() . " subdomain requests dibuat.");
    }

    /**
     * Create subdomain request with log
     */
    private function createSubdomainRequest(array $data): SubdomainRequest
    {
        // Generate ticket number
        $data['ticket_no'] = 'SD-' . date('Ymd') . '-' . strtoupper(Str::random(4));
        $data['submitted_at'] = now();
        $data['status'] = 'menunggu';

        // Default cloudflare settings
        $data['needs_ssl'] = false;
        $data['needs_proxy'] = false;

        // Add purpose if not provided (for backward compatibility)
        if (!isset($data['purpose'])) {
            $data['purpose'] = $data['nama_aplikasi'] ?? 'Testing';
        }

        $request = SubdomainRequest::create($data);

        // Create activity log
        SubdomainRequestLog::create([
            'subdomain_request_id' => $request->id,
            'actor_id' => $data['user_id'],
            'action' => 'created',
            'note' => 'Pengajuan subdomain dibuat (seeder test)',
        ]);

        return $request;
    }
}
