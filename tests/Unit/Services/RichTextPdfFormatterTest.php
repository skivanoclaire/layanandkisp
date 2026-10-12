<?php

namespace Tests\Unit\Services;

use App\Services\RichTextPdfFormatter;
use PHPUnit\Framework\TestCase;

class RichTextPdfFormatterTest extends TestCase
{
    public function test_isi_kosong_menghasilkan_string_kosong(): void
    {
        $this->assertSame('', RichTextPdfFormatter::format(null));
        $this->assertSame('', RichTextPdfFormatter::format("  \n "));
    }

    public function test_teks_polos_mempertahankan_baris_baru(): void
    {
        $hasil = RichTextPdfFormatter::format("Baris satu\nBaris dua & tiga");

        $this->assertStringContainsString('Baris satu<br', $hasil);
        $this->assertStringContainsString('Baris dua &amp; tiga', $hasil);
    }

    public function test_format_editor_dipertahankan(): void
    {
        $html = '<h3>Judul</h3><p>Paragraf <strong>tebal</strong> dan <i>miring</i>.</p>'
            . '<ol><li>Satu<ul><li>Butir</li></ul></li><li>Dua</li></ol>';

        $this->assertSame($html, RichTextPdfFormatter::format($html));
    }

    public function test_tabel_editor_dipertahankan_tanpa_pembungkus_figure(): void
    {
        $html = '<figure class="table"><table><thead><tr><th>No</th></tr></thead>'
            . '<tbody><tr><td colspan="2">Isi</td></tr></tbody></table></figure>';

        $this->assertSame(
            '<table><thead><tr><th>No</th></tr></thead><tbody><tr><td colspan="2">Isi</td></tr></tbody></table>',
            RichTextPdfFormatter::format($html)
        );
    }

    public function test_karakter_non_ascii_tidak_rusak(): void
    {
        $this->assertSame('<p>Biaya ≥ Rp 5 juta — “é”</p>', RichTextPdfFormatter::format('<p>Biaya ≥ Rp 5 juta — “é”</p>'));
    }

    public function test_tag_dan_atribut_berbahaya_dibuang(): void
    {
        $hasil = RichTextPdfFormatter::format(
            '<p style="position:fixed" onclick="x()">Aman</p>'
            . '<script type="text/php">echo 1;</script><style>p{display:none}</style>'
            . '<img src="http://internal/x.png"><a href="javascript:alert(1)">tautan</a>'
        );

        $this->assertSame('<p>Aman</p><a>tautan</a>', $hasil);
    }

    public function test_penomoran_ol_start_dan_type_diterjemahkan(): void
    {
        $this->assertSame(
            '<ol start="3" style="list-style-type: lower-alpha;"><li>C</li></ol>',
            RichTextPdfFormatter::format('<ol start="3" type="a" class="x"><li>C</li></ol>')
        );
    }

    public function test_teks_mirip_tag_tetap_di_escape(): void
    {
        $this->assertSame(
            '<p>&lt;script&gt;alert(1)&lt;/script&gt;</p>',
            RichTextPdfFormatter::format('<p>&lt;script&gt;alert(1)&lt;/script&gt;</p>')
        );
    }
}
