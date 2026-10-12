<?php

namespace App\Services;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMText;

/**
 * Menyiapkan isi rich text (CKEditor) untuk dicetak ke PDF lewat DomPDF.
 *
 * Format editor (paragraf, penomoran, butir, tebal/miring, heading, tabel) dipertahankan,
 * tetapi hanya tag dan atribut pada daftar putih yang lolos. Ini penting karena DomPDF
 * dijalankan dengan isPhpEnabled & isRemoteEnabled: <script type="text/php">, <img>, <style>
 * maupun atribut style/on* dari isian pemohon tidak boleh ikut ter-render.
 */
class RichTextPdfFormatter
{
    /** Tag yang dipertahankan apa adanya (tanpa atribut, kecuali yang diatur di bawah). */
    private const TAG_DIIZINKAN = [
        'p', 'br', 'div', 'hr',
        'strong', 'b', 'em', 'i', 'u', 's', 'sub', 'sup',
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'ul', 'ol', 'li', 'blockquote', 'a',
        'table', 'thead', 'tbody', 'tfoot', 'tr', 'th', 'td',
    ];

    /** Tag yang dibuang beserta seluruh isinya. */
    private const TAG_DIBUANG = [
        'script', 'style', 'head', 'title', 'meta', 'link', 'base',
        'iframe', 'frame', 'frameset', 'object', 'embed', 'applet',
        'img', 'picture', 'svg', 'math', 'video', 'audio', 'canvas',
        'form', 'input', 'button', 'select', 'textarea', 'template', 'noscript',
    ];

    private const TAG_KOSONG = ['br', 'hr'];

    /** Atribut type pada <ol> diterjemahkan ke list-style-type agar dipahami DomPDF. */
    private const TIPE_OL = [
        '1' => 'decimal',
        'a' => 'lower-alpha',
        'A' => 'upper-alpha',
        'i' => 'lower-roman',
        'I' => 'upper-roman',
    ];

    public static function format(?string $html): string
    {
        $html = trim((string) $html);

        if ($html === '') {
            return '';
        }

        // Isian lama berupa teks polos: cukup pertahankan baris barunya.
        if ($html === strip_tags($html)) {
            return nl2br(htmlspecialchars($html, ENT_QUOTES, 'UTF-8', false));
        }

        $dom = new DOMDocument();
        $sebelumnya = libxml_use_internal_errors(true);
        $dom->loadHTML(
            '<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"></head><body>'
            . $html . '</body></html>'
        );
        libxml_clear_errors();
        libxml_use_internal_errors($sebelumnya);

        $body = $dom->getElementsByTagName('body')->item(0);

        if (! $body) {
            return nl2br(htmlspecialchars(strip_tags($html), ENT_QUOTES, 'UTF-8', false));
        }

        return trim(self::renderAnak($body));
    }

    private static function renderAnak(DOMNode $node): string
    {
        $hasil = '';

        foreach ($node->childNodes as $anak) {
            $hasil .= self::renderNode($anak);
        }

        return $hasil;
    }

    private static function renderNode(DOMNode $node): string
    {
        if ($node instanceof DOMText) {
            return htmlspecialchars($node->nodeValue, ENT_QUOTES, 'UTF-8');
        }

        // Komentar, processing instruction, dsb. dibuang.
        if (! $node instanceof DOMElement) {
            return '';
        }

        $tag = strtolower($node->nodeName);

        if (in_array($tag, self::TAG_DIBUANG, true)) {
            return '';
        }

        // Tag tak dikenal (span, figure, font, ...) dilepas, isinya tetap dipakai.
        if (! in_array($tag, self::TAG_DIIZINKAN, true)) {
            return self::renderAnak($node);
        }

        $atribut = self::atribut($tag, $node);

        if (in_array($tag, self::TAG_KOSONG, true)) {
            return "<{$tag}{$atribut}>";
        }

        return "<{$tag}{$atribut}>" . self::renderAnak($node) . "</{$tag}>";
    }

    private static function atribut(string $tag, DOMElement $node): string
    {
        $atribut = '';

        if ($tag === 'td' || $tag === 'th') {
            foreach (['colspan', 'rowspan'] as $nama) {
                $nilai = (int) $node->getAttribute($nama);
                if ($nilai > 1 && $nilai <= 100) {
                    $atribut .= " {$nama}=\"{$nilai}\"";
                }
            }
        }

        if ($tag === 'ol') {
            $mulai = $node->getAttribute('start');
            if (preg_match('/^-?\d{1,6}$/', $mulai)) {
                $atribut .= " start=\"{$mulai}\"";
            }

            $tipe = $node->getAttribute('type');
            if (isset(self::TIPE_OL[$tipe])) {
                $atribut .= ' style="list-style-type: ' . self::TIPE_OL[$tipe] . ';"';
            }
        }

        if ($tag === 'a') {
            $href = trim($node->getAttribute('href'));
            if (preg_match('#^(https?://|mailto:)#i', $href)) {
                $atribut .= ' href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '"';
            }
        }

        return $atribut;
    }
}
