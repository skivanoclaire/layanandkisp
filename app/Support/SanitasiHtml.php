<?php

namespace App\Support;

use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

/**
 * Pembersih HTML untuk isi rich text dari pengguna sebelum dicetak dengan {!! !!} (audit A-06).
 * Hanya elemen dan atribut aman yang lolos; skrip, handler event (onerror, onmouseover), dan
 * skema javascript: dibuang.
 */
final class SanitasiHtml
{
    private static ?HtmlSanitizer $sanitizer = null;

    public static function bersihkan(?string $html): string
    {
        if ($html === null || trim($html) === '') {
            return '';
        }

        return self::sanitizer()->sanitize($html);
    }

    private static function sanitizer(): HtmlSanitizer
    {
        return self::$sanitizer ??= new HtmlSanitizer(
            (new HtmlSanitizerConfig())
                ->allowSafeElements()
                ->allowRelativeLinks()
                ->allowLinkSchemes(['http', 'https', 'mailto'])
                ->allowMediaSchemes(['http', 'https'])
        );
    }
}
