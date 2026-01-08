<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\UnitKerja;
use App\Models\LaporanGangguan;
use App\Models\StarlinkRequest;
use App\Models\VpnRegistration;
use App\Models\VpnReset;
use App\Models\JipPdnsRequest;
use Carbon\Carbon;

class InternetAndVpnTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Find test user
        $user = User::where('email', 'userdummy10@kaltaraprov.go.id')->first();

        if (!$user) {
            $this->command->error('User userdummy10@kaltaraprov.go.id tidak ditemukan!');
            return;
        }

        // Get a unit kerja
        $unitKerja = UnitKerja::first();

        if (!$unitKerja) {
            $this->command->error('Tidak ada data Unit Kerja!');
            return;
        }

        $this->command->info('Membuat data uji coba untuk user: ' . $user->email);

        // ========== INTERNET SERVICES ==========

        // 1. Laporan Gangguan - Status: Menunggu
        LaporanGangguan::create([
            'user_id' => $user->id,
            'nama' => $user->name,
            'nip' => $user->nik ?? '199001012015031001',
            'unit_kerja_id' => $unitKerja->id,
            'no_hp' => '081234567890',
            'uraian_permasalahan' => 'INTERNET MATI TOTAL - Ruang Server Lantai 3, Gedung Utama\n\nInternet di ruang server mati total sejak pukul 08:00 WITA. Sudah dicoba restart modem tapi tetap tidak bisa connect. Lampu indikator pada modem berkedip merah. Sangat mengganggu pekerjaan karena semua sistem online tidak bisa diakses.',
            'lokasi_koordinat' => '3.3294,117.5964',
            'status' => 'menunggu',
            'created_at' => Carbon::now()->subDays(1),
        ]);

        // 2. Laporan Gangguan - Status: Proses
        $laporanProses = LaporanGangguan::create([
            'user_id' => $user->id,
            'nama' => $user->name,
            'nip' => $user->nik ?? '199001012015031001',
            'unit_kerja_id' => $unitKerja->id,
            'no_hp' => '081234567890',
            'uraian_permasalahan' => 'KONEKSI INTERNET LAMBAT - Ruang Kerja Bagian Perencanaan\n\nKoneksi internet sangat lambat, loading website lebih dari 1 menit. Kecepatan download hanya 1 Mbps padahal biasanya 50 Mbps. Sudah coba ganti kabel LAN dan restart komputer tapi masih sama. Pekerjaan jadi terhambat.',
            'lokasi_koordinat' => '3.3280,117.5970',
            'status' => 'proses',
            'processed_by' => 1, // Assume admin ID 1
            'processing_at' => Carbon::now()->subHours(3),
            'admin_notes' => 'Sedang dilakukan pengecekan bandwidth dan konfigurasi router.',
            'created_at' => Carbon::now()->subDays(2),
        ]);

        // 3. Laporan Gangguan - Status: Selesai
        $laporanSelesai = LaporanGangguan::create([
            'user_id' => $user->id,
            'nama' => $user->name,
            'nip' => $user->nik ?? '199001012015031001',
            'unit_kerja_id' => $unitKerja->id,
            'no_hp' => '081234567890',
            'uraian_permasalahan' => 'TIDAK BISA AKSES WEBSITE TERTENTU - Ruang Keuangan Lantai 2\n\nTidak bisa membuka website SIPD (sipd.kemendagri.go.id) dan beberapa website pemerintah lainnya seperti e-planning.kaltaraprov.go.id. Website lain bisa dibuka dengan normal. Sudah dicoba dari browser Chrome dan Firefox hasilnya sama.',
            'lokasi_koordinat' => '3.3300,117.5950',
            'status' => 'selesai',
            'processed_by' => 1,
            'processing_at' => Carbon::now()->subDays(4),
            'completed_at' => Carbon::now()->subDays(3),
            'admin_notes' => 'Sudah diselesaikan dengan mengupdate DNS server dan whitelist website terkait. Website sudah bisa diakses kembali.',
            'created_at' => Carbon::now()->subDays(5),
        ]);

        // 4. Starlink Jelajah - Status: Menunggu
        StarlinkRequest::create([
            'user_id' => $user->id,
            'nama' => $user->name,
            'nip' => $user->nik ?? '199001012015031001',
            'unit_kerja_id' => $unitKerja->id,
            'no_hp' => '081234567890',
            'uraian_kegiatan' => 'Kegiatan Monitoring dan Evaluasi Program Pembangunan Daerah di wilayah terpencil Kecamatan Long Pujungan. Membutuhkan koneksi internet untuk upload data real-time dan video conference dengan tim pusat.',
            'tanggal_mulai' => Carbon::now()->addDays(7),
            'tanggal_selesai' => Carbon::now()->addDays(10),
            'jam_mulai' => '08:00:00',
            'jam_selesai' => '17:00:00',
            'status' => 'menunggu',
            'created_at' => Carbon::now()->subHours(12),
        ]);

        // 5. Starlink Jelajah - Status: Proses
        StarlinkRequest::create([
            'user_id' => $user->id,
            'nama' => $user->name,
            'nip' => $user->nik ?? '199001012015031001',
            'unit_kerja_id' => $unitKerja->id,
            'no_hp' => '081234567890',
            'uraian_kegiatan' => 'Rapat Koordinasi Lintas Sektor di Kabupaten Nunukan. Dibutuhkan untuk presentasi dan koordinasi online dengan stakeholder.',
            'tanggal_mulai' => Carbon::now()->addDays(14),
            'tanggal_selesai' => Carbon::now()->addDays(16),
            'jam_mulai' => '09:00:00',
            'jam_selesai' => '16:00:00',
            'status' => 'proses',
            'processed_by' => 1,
            'processing_at' => Carbon::now()->subDays(1),
            'admin_notes' => 'Sedang dilakukan pengecekan ketersediaan unit Starlink untuk tanggal tersebut.',
            'created_at' => Carbon::now()->subDays(2),
        ]);

        // 6. Starlink Jelajah - Status: Selesai
        StarlinkRequest::create([
            'user_id' => $user->id,
            'nama' => $user->name,
            'nip' => $user->nik ?? '199001012015031001',
            'unit_kerja_id' => $unitKerja->id,
            'no_hp' => '081234567890',
            'uraian_kegiatan' => 'Sosialisasi Program Digitalisasi Desa di Kecamatan Krayan. Memerlukan koneksi internet untuk demo aplikasi dan streaming.',
            'tanggal_mulai' => Carbon::now()->subDays(3),
            'tanggal_selesai' => Carbon::now()->subDays(1),
            'jam_mulai' => '08:30:00',
            'jam_selesai' => '15:30:00',
            'status' => 'selesai',
            'processed_by' => 1,
            'processing_at' => Carbon::now()->subDays(7),
            'completed_at' => Carbon::now()->subDays(5),
            'admin_notes' => 'Unit Starlink sudah dijadwalkan dan dikonfirmasi. Silakan ambil unit di Bagian TIK sehari sebelum kegiatan.',
            'created_at' => Carbon::now()->subDays(10),
        ]);

        $this->command->info('✓ Data Internet Services berhasil dibuat (3 Laporan Gangguan, 3 Starlink)');

        // ========== VPN SERVICES ==========

        // 7. VPN Registration - Status: Menunggu
        VpnRegistration::create([
            'user_id' => $user->id,
            'nama' => $user->name,
            'nip' => $user->nik ?? '199001012015031001',
            'unit_kerja_id' => $unitKerja->id,
            'uraian_kebutuhan' => 'Membutuhkan akses VPN untuk mengakses sistem SIMPEG dari rumah. Sebagai pegawai yang sering bekerja remote, saya perlu akses ke database internal untuk entry data kepegawaian.',
            'tipe' => 'VPN PPTP',
            'bandwidth' => '10 Mbps',
            'status' => 'menunggu',
            'created_at' => Carbon::now()->subHours(6),
        ]);

        // 8. VPN Registration - Status: Proses
        VpnRegistration::create([
            'user_id' => $user->id,
            'nama' => $user->name,
            'nip' => $user->nik ?? '199001012015031001',
            'unit_kerja_id' => $unitKerja->id,
            'uraian_kebutuhan' => 'Memerlukan koneksi VPN IPSec/L2TP untuk akses aplikasi keuangan daerah SIPD. Digunakan untuk pelaporan dan monitoring anggaran secara real-time.',
            'tipe' => 'VPN IPSec/L2TP',
            'bandwidth' => '20 Mbps',
            'status' => 'proses',
            'processed_by' => 1,
            'processing_at' => Carbon::now()->subDays(1),
            'admin_notes' => 'Sedang dilakukan setup konfigurasi VPN di server.',
            'created_at' => Carbon::now()->subDays(2),
        ]);

        // 9. VPN Registration - Status: Selesai
        VpnRegistration::create([
            'user_id' => $user->id,
            'nama' => $user->name,
            'nip' => $user->nik ?? '199001012015031001',
            'unit_kerja_id' => $unitKerja->id,
            'uraian_kebutuhan' => 'Akses SDWAN untuk kantor cabang di Kabupaten Malinau. Dibutuhkan untuk koneksi dedicated antara kantor pusat dengan kantor cabang untuk transfer data besar.',
            'tipe' => 'SDWAN',
            'bandwidth' => '100 Mbps',
            'status' => 'selesai',
            'username_vpn' => 'vpn.user.malinau',
            'password_vpn' => 'SecureP@ss2025!',
            'ip_vpn' => '10.10.50.15',
            'keterangan_admin' => 'VPN sudah aktif dan bisa digunakan. Untuk konfigurasi client, silakan download manual di portal IT atau hubungi helpdesk.',
            'processed_by' => 1,
            'processing_at' => Carbon::now()->subDays(5),
            'completed_at' => Carbon::now()->subDays(3),
            'admin_notes' => 'SDWAN berhasil dikonfigurasi dengan bandwidth dedicated 100 Mbps.',
            'created_at' => Carbon::now()->subDays(7),
        ]);

        // 10. VPN Reset - Status: Menunggu
        VpnReset::create([
            'user_id' => $user->id,
            'nama' => $user->name,
            'nip' => $user->nik ?? '199001012015031001',
            'unit_kerja_id' => $unitKerja->id,
            'username_vpn_lama' => 'dummy10.vpn',
            'alasan' => 'Lupa password VPN. Sudah beberapa kali mencoba login tapi selalu gagal. Terakhir kali login sekitar 3 bulan yang lalu.',
            'status' => 'menunggu',
            'created_at' => Carbon::now()->subHours(4),
        ]);

        // 11. VPN Reset - Status: Proses
        VpnReset::create([
            'user_id' => $user->id,
            'nama' => $user->name,
            'nip' => $user->nik ?? '199001012015031001',
            'unit_kerja_id' => $unitKerja->id,
            'username_vpn_lama' => 'old.dummy10',
            'alasan' => 'Akun VPN terkena hack/compromise. Ada aktivitas mencurigakan dari IP asing. Perlu segera direset untuk keamanan.',
            'status' => 'proses',
            'processed_by' => 1,
            'processing_at' => Carbon::now()->subHours(2),
            'admin_notes' => 'Sedang dilakukan investigasi log akses dan persiapan kredensial baru.',
            'created_at' => Carbon::now()->subHours(5),
        ]);

        // 12. VPN Reset - Status: Selesai
        VpnReset::create([
            'user_id' => $user->id,
            'nama' => $user->name,
            'nip' => $user->nik ?? '199001012015031001',
            'unit_kerja_id' => $unitKerja->id,
            'username_vpn_lama' => 'vpn_dummy10_old',
            'alasan' => 'Password sudah kadaluarsa sesuai kebijakan keamanan (harus diganti setiap 90 hari). Mohon dibuatkan kredensial baru.',
            'status' => 'selesai',
            'username_vpn_baru' => 'vpn.dummy10.new',
            'password_vpn_baru' => 'NewSecure@2025#',
            'keterangan_admin' => 'Password VPN baru sudah dibuat. Silakan login dengan kredensial baru. Jangan lupa ganti password secara berkala setiap 90 hari.',
            'processed_by' => 1,
            'processing_at' => Carbon::now()->subDays(2),
            'completed_at' => Carbon::now()->subDays(1),
            'admin_notes' => 'Kredensial lama sudah dinonaktifkan, kredensial baru aktif.',
            'created_at' => Carbon::now()->subDays(3),
        ]);

        // 13. JIP PDNS - Status: Menunggu (Provinsi)
        JipPdnsRequest::create([
            'user_id' => $user->id,
            'nama' => $user->name,
            'nip' => $user->nik ?? '199001012015031001',
            'is_kabupaten_kota' => false,
            'unit_kerja_id' => $unitKerja->id,
            'uraian_permohonan' => 'Membutuhkan akses JIP PDNS untuk koneksi antar sistem informasi di lingkungan Pemprov Kaltara. Digunakan untuk integrasi data SIMPEG, SIPD, dan sistem lainnya.',
            'keterangan' => 'Memerlukan informasi Segment IPSec dari Kabupaten Bulungan untuk routing network.',
            'status' => 'menunggu',
            'created_at' => Carbon::now()->subHours(8),
        ]);

        // 14. JIP PDNS - Status: Proses (Kabupaten/Kota)
        JipPdnsRequest::create([
            'user_id' => $user->id,
            'nama' => $user->name,
            'nip' => $user->nik ?? '199001012015031001',
            'is_kabupaten_kota' => true,
            'kabupaten_kota' => 'Tarakan',
            'unit_kerja_manual' => 'Dinas Komunikasi dan Informatika Kota Tarakan',
            'uraian_permohonan' => 'Permohonan akses JIP PDNS untuk Kota Tarakan. CPE kami sudah terpasang dan perlu dikonfigurasi untuk akses ke jaringan Provinsi.',
            'keterangan' => 'Segment IPSec: 192.168.100.0/24, Gateway: 192.168.100.1, Public IP: 103.xxx.xxx.xxx',
            'status' => 'proses',
            'processed_by' => 1,
            'processing_at' => Carbon::now()->subDays(1),
            'admin_notes' => 'Sedang dilakukan konfigurasi routing untuk Segment IPSec Kota Tarakan.',
            'created_at' => Carbon::now()->subDays(2),
        ]);

        // 15. JIP PDNS - Status: Selesai (Kabupaten/Kota)
        JipPdnsRequest::create([
            'user_id' => $user->id,
            'nama' => $user->name,
            'nip' => $user->nik ?? '199001012015031001',
            'is_kabupaten_kota' => true,
            'kabupaten_kota' => 'Nunukan',
            'unit_kerja_manual' => 'Badan Pengelolaan Keuangan dan Aset Daerah Kabupaten Nunukan',
            'uraian_permohonan' => 'Akses JIP PDNS untuk sinkronisasi data keuangan dengan sistem Provinsi. Diperlukan untuk pelaporan APBD dan integrasi SIPD.',
            'keterangan' => 'Segment IPSec sudah dikonfigurasi:\n- Network: 172.16.50.0/24\n- Gateway: 172.16.50.254\n- DNS: 8.8.8.8, 8.8.4.4\n- Tunnel Interface: tun0',
            'status' => 'selesai',
            'keterangan_admin' => 'Akses JIP PDNS untuk Kabupaten Nunukan sudah berhasil dikonfigurasi.\n\nDetail Konfigurasi:\n- Routing sudah ditambahkan untuk segment 172.16.50.0/24\n- Koneksi ke Edge Provinsi Kaltara sudah aktif\n- Test ping ke gateway Provinsi (10.10.10.1) berhasil\n- Bandwidth allocated: 50 Mbps dedicated\n\nSilakan lakukan testing koneksi dari sisi Kabupaten. Jika ada kendala, segera hubungi tim kami.',
            'processed_by' => 1,
            'processing_at' => Carbon::now()->subDays(5),
            'completed_at' => Carbon::now()->subDays(3),
            'admin_notes' => 'Konfigurasi selesai, routing aktif, sudah ditest koneksi OK.',
            'created_at' => Carbon::now()->subDays(7),
        ]);

        $this->command->info('✓ Data VPN Services berhasil dibuat (3 VPN Registration, 3 VPN Reset, 3 JIP PDNS)');
        $this->command->info('');
        $this->command->info('========================================');
        $this->command->info('TOTAL DATA BERHASIL DIBUAT:');
        $this->command->info('- Laporan Gangguan: 3 (1 menunggu, 1 proses, 1 selesai)');
        $this->command->info('- Starlink Jelajah: 3 (1 menunggu, 1 proses, 1 selesai)');
        $this->command->info('- VPN Registration: 3 (1 menunggu, 1 proses, 1 selesai)');
        $this->command->info('- VPN Reset: 3 (1 menunggu, 1 proses, 1 selesai)');
        $this->command->info('- JIP PDNS: 3 (1 menunggu, 1 proses, 1 selesai)');
        $this->command->info('========================================');
        $this->command->info('User: ' . $user->email);
        $this->command->info('Total: 15 data uji coba');
        $this->command->info('========================================');
    }
}
