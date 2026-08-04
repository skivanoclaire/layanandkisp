<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Contoh pertanyaan yang bisa dijawab otomatis selama mode prototipe
     * (sebelum API key Anthropic tersedia). Admin bebas menambah/mengubah
     * lewat menu Knowledge Base AI.
     */
    private array $faqs = [
        [
            'pertanyaan' => 'Apa itu SPBE?',
            'kategori' => 'Umum',
            'kata_kunci' => 'spbe, apa itu spbe, kepanjangan spbe, definisi spbe',
            'jawaban' => "SPBE (Sistem Pemerintahan Berbasis Elektronik) adalah penyelenggaraan pemerintahan yang memanfaatkan teknologi informasi dan komunikasi untuk memberikan layanan kepada pengguna SPBE, yaitu instansi pemerintah, aparatur sipil negara, pelaku usaha, masyarakat, dan pihak lainnya.\n\nDasar hukum utamanya adalah Peraturan Presiden Nomor 95 Tahun 2018 tentang Sistem Pemerintahan Berbasis Elektronik, yang bertujuan mewujudkan tata kelola pemerintahan yang bersih, efektif, transparan, dan akuntabel serta layanan publik yang berkualitas dan terpercaya.",
        ],
        [
            'pertanyaan' => 'Bagaimana cara mengajukan rekomendasi pembangunan aplikasi?',
            'kategori' => 'Rekomendasi Aplikasi',
            'kata_kunci' => 'rekomendasi aplikasi, usulan aplikasi, bangun aplikasi, pengembangan aplikasi, permohonan aplikasi',
            'jawaban' => "Pengajuan rekomendasi pembangunan/pengembangan aplikasi dilakukan melalui menu **Layanan Digital → Rekomendasi Aplikasi → Usulan** pada portal ini.\n\nLangkah umumnya:\n1. Siapkan dokumen pendukung (kajian kebutuhan, arsitektur, dan anggaran).\n2. Isi formulir usulan sesuai ketentuan Permenkomdigi, termasuk pelaksana pembangunan (menteri/swakelola/pihak ketiga).\n3. Ajukan usulan, lalu pantau statusnya pada tahapan verifikasi, penandatanganan, sampai terbit surat rekomendasi.\n\nUsulan akan diverifikasi oleh tim Dinas Kominfo Provinsi Kalimantan Utara sebelum diterbitkan rekomendasi.",
        ],
        [
            'pertanyaan' => 'Bagaimana cara mengajukan permohonan subdomain kaltaraprov.go.id?',
            'kategori' => 'Subdomain',
            'kata_kunci' => 'subdomain, domain, website, hosting, kaltaraprov.go.id',
            'jawaban' => "Permohonan subdomain diajukan melalui menu **Layanan Digital → Subdomain → Permohonan Baru**.\n\nYang perlu disiapkan:\n1. Surat permohonan resmi dari kepala instansi/perangkat daerah.\n2. Nama subdomain yang diusulkan beserta peruntukannya.\n3. Data teknis: alamat IP server atau target CNAME, serta penanggung jawab teknis.\n4. Klasifikasi data (kerahasiaan, integritas, ketersediaan).\n\nSetelah diverifikasi admin, subdomain akan dibuatkan pada layanan DNS dan Anda akan menerima notifikasi status permohonan.",
        ],
        [
            'pertanyaan' => 'Apa saja layanan digital yang tersedia di portal ini?',
            'kategori' => 'Umum',
            'kata_kunci' => 'layanan, daftar layanan, layanan digital, fitur, apa saja',
            'jawaban' => "Portal Layanan Digital Diskominfo Kalimantan Utara menyediakan antara lain:\n\n- **Surat Elektronik** — permohonan akun email resmi dan reset kata sandi\n- **Subdomain** — permohonan, perubahan IP/nama, dan pembaruan data subdomain\n- **Rekomendasi Aplikasi** — usulan pembangunan/pengembangan aplikasi\n- **Pusat Data** — kunjungan/colocation, VPS/VM, backup, dan cloud storage\n- **Internet & Konektivitas** — lapor gangguan internet dan Starlink Jelajah\n- **VPN & Jaringan Privat** — pendaftaran, reset akun, dan akses JIP PDNS\n- **Tanda Tangan Elektronik (TTE)** — pendampingan, pendaftaran akun, reset passphrase, pembaruan sertifikat\n- **SPLP** — pendaftaran endpoint penyedia dan akses konsumen layanan\n- **Video Conference**, **Pemendek Tautan**, **PSE**, dan **Konsultasi SPBE Berbasis AI**",
        ],
        [
            'pertanyaan' => 'Bagaimana cara mendapatkan akun Tanda Tangan Elektronik (TTE)?',
            'kategori' => 'TTE',
            'kata_kunci' => 'tte, tanda tangan elektronik, sertifikat elektronik, passphrase, bsre',
            'jawaban' => "Pendaftaran akun TTE dilakukan melalui menu **Layanan Digital → Tanda Tangan Elektronik → Pendaftaran Akun Baru TTE**.\n\nPersyaratan umum:\n1. Data diri sesuai KTP dan NIP (bagi ASN).\n2. Alamat surel aktif dan nomor telepon yang dapat dihubungi.\n3. Surat permohonan/penugasan dari instansi.\n\nSetelah diverifikasi, penerbitan sertifikat elektronik dilakukan bersama Balai Sertifikasi Elektronik (BSrE). Jika lupa passphrase atau sertifikat akan kedaluwarsa, gunakan menu Reset Passphrase atau Pembaruan Sertifikat.",
        ],
        [
            'pertanyaan' => 'Apa itu SPLP dan bagaimana cara mendaftarkan endpoint layanan?',
            'kategori' => 'SPLP',
            'kata_kunci' => 'splp, interoperabilitas, endpoint, api, berbagi pakai data, konsumen layanan',
            'jawaban' => "SPLP (Sistem Penghubung Layanan Pemerintah) adalah sarana interoperabilitas dan berbagi pakai data antar Sistem Elektronik instansi pemerintah.\n\nUntuk mendaftarkan endpoint sebagai **penyedia layanan**, gunakan menu **Layanan Digital → SPLP → Pendaftaran Endpoint Penyedia**. Siapkan dokumentasi API (spesifikasi endpoint, metode, parameter), alamat endpoint, dan penanggung jawab teknis.\n\nUntuk mengakses layanan milik instansi lain sebagai **konsumen**, ajukan melalui menu Pendaftaran Akses Konsumen Layanan. Tersedia pula fasilitas uji coba (sandbox) sebelum layanan digunakan pada lingkungan produksi.",
        ],
        [
            'pertanyaan' => 'Bagaimana prosedur kunjungan ke Pusat Data (Data Center)?',
            'kategori' => 'Pusat Data',
            'kata_kunci' => 'pusat data, data center, kunjungan, colocation, server, visitasi',
            'jawaban' => "Kunjungan ke Pusat Data diajukan melalui menu **Layanan Digital → Pusat Data → Kunjungan/Colocation**.\n\nKetentuan umum:\n1. Permohonan diajukan paling lambat 2 (dua) hari kerja sebelum tanggal kunjungan.\n2. Cantumkan identitas seluruh personel yang akan masuk, keperluan, serta perangkat yang dibawa.\n3. Kunjungan didampingi petugas Pusat Data dan dicatat dalam log kunjungan.\n\nPermohonan yang telah disetujui akan diberitahukan melalui portal beserta jadwal pendampingan.",
        ],
        [
            'pertanyaan' => 'Berapa lama waktu penyelesaian permohonan layanan?',
            'kategori' => 'Umum',
            'kata_kunci' => 'sla, berapa lama, waktu, durasi, penyelesaian, standar layanan',
            'jawaban' => "Setiap layanan memiliki target SLA (Service Level Agreement) yang berbeda dan dihitung berdasarkan hari serta jam kerja, dengan mengecualikan hari libur nasional dan cuti bersama.\n\nStatus dan sisa waktu penyelesaian permohonan Anda dapat dipantau langsung pada halaman detail permohonan masing-masing layanan. Bila permohonan melewati target SLA, silakan hubungi admin layanan Diskominfo Provinsi Kalimantan Utara.",
        ],
    ];

    public function up(): void
    {
        $urutan = 1;
        foreach ($this->faqs as $faq) {
            $exists = DB::table('konsultasi_ai_faqs')->where('pertanyaan', $faq['pertanyaan'])->exists();
            if ($exists) {
                $urutan++;
                continue;
            }

            DB::table('konsultasi_ai_faqs')->insert(array_merge($faq, [
                'urutan' => $urutan++,
                'is_active' => true,
                'hit_count' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        $defaults = [
            'ai_enabled' => '0',
            'model' => 'claude-sonnet-5',
            'system_prompt' => "Anda adalah Asisten SPBE Kalimantan Utara, chatbot resmi Dinas Komunikasi dan Informatika Provinsi Kalimantan Utara.\n\nTugas Anda membantu pegawai dan perangkat daerah dalam hal:\n- Implementasi SPBE (Sistem Pemerintahan Berbasis Elektronik)\n- Kebijakan dan regulasi digitalisasi pemerintahan\n- Panduan teknis serta praktik baik transformasi digital\n- Prosedur layanan digital yang disediakan Diskominfo Kalimantan Utara\n\nAturan menjawab:\n1. Jawab HANYA berdasarkan dokumen knowledge base yang diberikan. Jangan mengarang prosedur, nomor peraturan, atau tenggat waktu.\n2. Jika informasi tidak ada dalam knowledge base, katakan terus terang bahwa informasi belum tersedia dan arahkan pengguna menghubungi admin Diskominfo Kalimantan Utara.\n3. Gunakan bahasa Indonesia yang formal, ringkas, dan mudah dipahami.\n4. Sebutkan nama menu portal bila relevan agar pengguna mudah menindaklanjuti.",
            'fallback_message' => "Mohon maaf, pertanyaan tersebut belum tersedia pada basis pengetahuan kami.\n\nSaat ini layanan Tanya Langsung masih dalam tahap prototipe dan hanya dapat menjawab pertanyaan yang telah disiapkan. Silakan coba pertanyaan lain dari daftar contoh, gunakan tombol **Tanya via ChatGPT** untuk pertanyaan yang lebih luas, atau hubungi admin Diskominfo Provinsi Kalimantan Utara.",
        ];

        foreach ($defaults as $key => $value) {
            $exists = DB::table('konsultasi_ai_settings')->where('key', $key)->exists();
            if (! $exists) {
                DB::table('konsultasi_ai_settings')->insert([
                    'key' => $key,
                    'value' => $value,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        DB::table('konsultasi_ai_faqs')
            ->whereIn('pertanyaan', array_column($this->faqs, 'pertanyaan'))
            ->delete();

        DB::table('konsultasi_ai_settings')
            ->whereIn('key', ['ai_enabled', 'model', 'system_prompt', 'fallback_message'])
            ->delete();
    }
};
