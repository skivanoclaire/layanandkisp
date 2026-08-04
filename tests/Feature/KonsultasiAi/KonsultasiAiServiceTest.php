<?php

namespace Tests\Feature\KonsultasiAi;

use App\Models\KonsultasiAiDocument;
use App\Models\KonsultasiAiFaq;
use App\Models\KonsultasiAiSetting;
use App\Services\KonsultasiAiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Perilaku mode prototipe: menjawab dari knowledge base lokal tanpa API Anthropic.
 */
class KonsultasiAiServiceTest extends TestCase
{
    use RefreshDatabase;

    private KonsultasiAiService $service;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.anthropic.api_key' => null]);

        // Migrasi menanam contoh pertanyaan bawaan; kosongkan agar setiap
        // pengujian berjalan di atas knowledge base yang bersih.
        KonsultasiAiFaq::query()->delete();
        KonsultasiAiDocument::query()->delete();

        $this->service = app(KonsultasiAiService::class);
    }

    private function buatFaq(array $attributes = []): KonsultasiAiFaq
    {
        return KonsultasiAiFaq::create(array_merge([
            'pertanyaan' => 'Bagaimana cara mengajukan permohonan subdomain?',
            'jawaban'    => 'Ajukan melalui menu Layanan Digital lalu pilih Subdomain.',
            'kata_kunci' => 'subdomain, domain, website',
            'urutan'     => 1,
            'is_active'  => true,
        ], $attributes));
    }

    public function test_ai_tidak_aktif_tanpa_api_key(): void
    {
        KonsultasiAiSetting::put('ai_enabled', '1');

        $this->assertFalse($this->service->hasApiKey());
        $this->assertFalse($this->service->isAiActive());
    }

    public function test_ai_tidak_aktif_bila_belum_dinyalakan_admin(): void
    {
        config(['services.anthropic.api_key' => 'sk-ant-contoh']);
        KonsultasiAiSetting::put('ai_enabled', '0');

        $this->assertTrue($this->service->hasApiKey());
        $this->assertFalse($this->service->isAiActive());
    }

    public function test_pertanyaan_dijawab_dari_faq_lewat_kata_kunci(): void
    {
        $faq = $this->buatFaq();

        $hasil = $this->service->ask('Saya mau minta subdomain baru, caranya bagaimana?');

        $this->assertSame('faq', $hasil['sumber']);
        $this->assertSame($faq->jawaban, $hasil['jawaban']);
        $this->assertNull($hasil['error']);
        $this->assertSame(1, $faq->fresh()->hit_count);
    }

    public function test_faq_nonaktif_tidak_dipakai_menjawab(): void
    {
        $this->buatFaq(['is_active' => false]);

        $hasil = $this->service->ask('Saya mau minta subdomain baru, caranya bagaimana?');

        $this->assertSame('fallback', $hasil['sumber']);
    }

    public function test_pertanyaan_di_luar_knowledge_base_mendapat_pesan_fallback(): void
    {
        $this->buatFaq();
        KonsultasiAiSetting::put('fallback_message', 'Belum tersedia.');

        $hasil = $this->service->ask('Bagaimana resep rendang padang yang enak?');

        $this->assertSame('fallback', $hasil['sumber']);
        $this->assertSame('Belum tersedia.', $hasil['jawaban']);
    }

    public function test_jawaban_diambil_dari_dokumen_bila_tidak_ada_faq_yang_cocok(): void
    {
        KonsultasiAiDocument::create([
            'judul'     => 'Pedoman Walidata',
            'konten'    => "Setiap perangkat daerah menunjuk walidata yang memelihara kualitas metadata "
                . "dan melakukan pemutakhiran paling sedikit satu kali dalam tiga bulan.\n\n"
                . 'Penetapan walidata dituangkan dalam keputusan kepala perangkat daerah.',
            'is_active' => true,
        ]);

        $hasil = $this->service->ask('Seberapa sering pemutakhiran metadata oleh walidata dilakukan?');

        $this->assertSame('dokumen', $hasil['sumber']);
        $this->assertStringContainsString('Pedoman Walidata', $hasil['jawaban']);
        $this->assertStringContainsString('tiga bulan', $hasil['jawaban']);
    }

    public function test_dokumen_nonaktif_tidak_dipakai_menjawab(): void
    {
        KonsultasiAiDocument::create([
            'judul'     => 'Pedoman Walidata',
            'konten'    => 'Setiap perangkat daerah menunjuk walidata yang memelihara kualitas metadata secara berkala.',
            'is_active' => false,
        ]);

        $hasil = $this->service->ask('Seberapa sering pemutakhiran metadata oleh walidata dilakukan?');

        $this->assertSame('fallback', $hasil['sumber']);
    }

    public function test_konteks_hanya_memuat_knowledge_base_yang_aktif(): void
    {
        $this->buatFaq(['pertanyaan' => 'Pertanyaan aktif?', 'jawaban' => 'Jawaban aktif.']);
        $this->buatFaq(['pertanyaan' => 'Pertanyaan nonaktif?', 'jawaban' => 'Jawaban nonaktif.', 'is_active' => false]);

        KonsultasiAiDocument::create(['judul' => 'Dokumen Aktif', 'konten' => 'Isi aktif.', 'is_active' => true]);
        KonsultasiAiDocument::create(['judul' => 'Dokumen Nonaktif', 'konten' => 'Isi nonaktif.', 'is_active' => false]);

        $konteks = $this->service->knowledgeContext();

        $this->assertStringContainsString('Jawaban aktif.', $konteks);
        $this->assertStringContainsString('Dokumen Aktif', $konteks);
        $this->assertStringNotContainsString('Jawaban nonaktif.', $konteks);
        $this->assertStringNotContainsString('Dokumen Nonaktif', $konteks);
    }

    public function test_konteks_kosong_memberi_instruksi_aman_untuk_model(): void
    {
        $this->assertStringContainsString('belum tersedia', $this->service->knowledgeContext());
    }

    public function test_format_html_mengubah_markdown_ringan_dan_meloloskan_html_berbahaya(): void
    {
        $html = KonsultasiAiService::formatToHtml("Catatan **penting**.\n\n- poin satu\n- poin dua");

        $this->assertStringContainsString('<strong>penting</strong>', $html);
        $this->assertStringContainsString('<li>poin satu</li>', $html);

        $aman = KonsultasiAiService::formatToHtml('<script>alert(1)</script>');
        $this->assertStringNotContainsString('<script>', $aman);
    }
}
