<?php

namespace Tests\Unit;

use App\Support\SanitasiHtml;
use Tests\TestCase;

/** Audit A-06: isi rich text dari pengguna tidak boleh membawa skrip atau handler event. */
class SanitasiHtmlTest extends TestCase
{
    public function test_skrip_dan_handler_event_dibuang(): void
    {
        $bersih = SanitasiHtml::bersihkan('<p onmouseover="alert(1)">Halo</p><img src=x onerror="alert(2)"><script>alert(3)</script>');

        $this->assertStringContainsString('Halo', $bersih);
        $this->assertStringNotContainsString('onmouseover', $bersih);
        $this->assertStringNotContainsString('onerror', $bersih);
        $this->assertStringNotContainsString('<script', $bersih);
    }

    public function test_link_javascript_dibuang(): void
    {
        $bersih = SanitasiHtml::bersihkan('<a href="javascript:alert(1)">klik</a>');

        $this->assertStringNotContainsString('javascript:', $bersih);
    }

    public function test_teks_biasa_dan_format_dasar_tetap_ada(): void
    {
        $bersih = SanitasiHtml::bersihkan('<p><strong>Tebal</strong> dan <a href="https://example.go.id">tautan</a></p>');

        $this->assertStringContainsString('<strong>Tebal</strong>', $bersih);
        $this->assertStringContainsString('https://example.go.id', $bersih);
    }

    public function test_null_dan_kosong_menghasilkan_string_kosong(): void
    {
        $this->assertSame('', SanitasiHtml::bersihkan(null));
        $this->assertSame('', SanitasiHtml::bersihkan('   '));
    }
}
