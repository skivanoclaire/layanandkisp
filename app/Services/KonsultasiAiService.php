<?php

namespace App\Services;

use Anthropic\Client;
use App\Models\KonsultasiAiDocument;
use App\Models\KonsultasiAiFaq;
use App\Models\KonsultasiAiSetting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Otak layanan "Tanya Langsung" pada Konsultasi SPBE Berbasis AI.
 *
 * Dua mode operasi:
 * 1. Mode prototipe (default) — API key Anthropic belum tersedia. Jawaban diambil
 *    dari knowledge base: daftar pertanyaan contoh dan dokumen yang diunggah admin.
 * 2. Mode AI — setelah API key Anthropic diisi dan diaktifkan admin. Pertanyaan
 *    diteruskan ke Claude dengan knowledge base sebagai konteks.
 */
class KonsultasiAiService
{
    /** Batas jumlah karakter knowledge base yang dikirim sebagai konteks ke Claude. */
    private const MAX_KONTEKS_CHARS = 120000;

    /** Skor minimal agar sebuah pertanyaan contoh dianggap cocok. */
    private const AMBANG_SKOR_FAQ = 12;

    /** Kata umum yang diabaikan saat pencocokan kata kunci. */
    private const STOPWORDS = [
        'yang', 'untuk', 'dengan', 'dari', 'pada', 'apa', 'itu', 'dan', 'atau', 'ada',
        'bagaimana', 'cara', 'saya', 'kami', 'anda', 'bisa', 'dapat', 'adalah', 'ini',
        'apakah', 'kalau', 'tolong', 'mohon', 'gimana', 'kenapa', 'mengapa', 'harus',
    ];

    /**
     * Apakah jawaban akan diproses oleh Claude (bukan mode prototipe).
     */
    public function isAiActive(): bool
    {
        return $this->hasApiKey() && KonsultasiAiSetting::get('ai_enabled', '0') === '1';
    }

    public function hasApiKey(): bool
    {
        return filled(config('services.anthropic.api_key'));
    }

    public function model(): string
    {
        return KonsultasiAiSetting::get('model') ?: config('services.anthropic.model', 'claude-sonnet-5');
    }

    public function systemPrompt(): string
    {
        return (string) KonsultasiAiSetting::get('system_prompt', '');
    }

    public function fallbackMessage(): string
    {
        $pesan = KonsultasiAiSetting::get('fallback_message');

        return filled($pesan)
            ? (string) $pesan
            : 'Mohon maaf, pertanyaan tersebut belum tersedia pada basis pengetahuan kami. Silakan hubungi admin Diskominfo Provinsi Kalimantan Utara.';
    }

    /**
     * Jawab pertanyaan pengguna.
     *
     * @param  array<int, array{role: string, content: string}>  $riwayat
     * @return array{jawaban: string, sumber: string, model: ?string, input_tokens: ?int, output_tokens: ?int, error: ?string}
     */
    public function ask(string $pertanyaan, array $riwayat = []): array
    {
        $pertanyaan = trim($pertanyaan);

        if ($this->isAiActive()) {
            try {
                return $this->askClaude($pertanyaan, $riwayat);
            } catch (Throwable $e) {
                Log::error('Konsultasi SPBE AI gagal memanggil Claude', [
                    'message' => $e->getMessage(),
                ]);

                // Jangan biarkan pengguna melihat layar kosong — turunkan ke knowledge base lokal.
                $lokal = $this->askKnowledgeBase($pertanyaan);
                $lokal['error'] = $e->getMessage();

                return $lokal;
            }
        }

        return $this->askKnowledgeBase($pertanyaan);
    }

    /**
     * Mode AI — kirim pertanyaan ke Claude beserta konteks knowledge base.
     *
     * @param  array<int, array{role: string, content: string}>  $riwayat
     * @return array{jawaban: string, sumber: string, model: ?string, input_tokens: ?int, output_tokens: ?int, error: ?string}
     */
    private function askClaude(string $pertanyaan, array $riwayat): array
    {
        $client = new Client(apiKey: config('services.anthropic.api_key'));

        $system = [
            ['type' => 'text', 'text' => $this->systemPrompt()],
            [
                'type' => 'text',
                'text' => "Berikut basis pengetahuan resmi yang menjadi satu-satunya sumber jawaban Anda:\n\n" . $this->knowledgeContext(),
                // Knowledge base jarang berubah — cache agar biaya token turun drastis.
                'cacheControl' => ['type' => 'ephemeral'],
            ],
        ];

        $messages = [];
        foreach ($riwayat as $turn) {
            $role = ($turn['role'] ?? '') === 'assistant' ? 'assistant' : 'user';
            $content = trim((string) ($turn['content'] ?? ''));
            if ($content !== '') {
                $messages[] = ['role' => $role, 'content' => $content];
            }
        }
        $messages[] = ['role' => 'user', 'content' => $pertanyaan];

        $message = $client->messages->create(
            maxTokens: 4096,
            messages: $messages,
            model: $this->model(),
            system: $system,
        );

        // Klasifikasi keamanan Claude dapat menolak permintaan — tangani sebelum membaca isi.
        if ($message->stopReason === 'refusal') {
            return [
                'jawaban' => 'Maaf, pertanyaan tersebut tidak dapat diproses oleh asisten. Silakan ajukan pertanyaan lain seputar SPBE dan layanan digital.',
                'sumber' => 'ai',
                'model' => $this->model(),
                'input_tokens' => $message->usage->inputTokens ?? null,
                'output_tokens' => $message->usage->outputTokens ?? null,
                'error' => null,
            ];
        }

        $teks = '';
        foreach ($message->content as $block) {
            if (($block->type ?? null) === 'text') {
                $teks .= $block->text;
            }
        }

        return [
            'jawaban' => trim($teks) !== '' ? trim($teks) : $this->fallbackMessage(),
            'sumber' => 'ai',
            'model' => $this->model(),
            'input_tokens' => $message->usage->inputTokens ?? null,
            'output_tokens' => $message->usage->outputTokens ?? null,
            'error' => null,
        ];
    }

    /**
     * Mode prototipe — cari jawaban dari pertanyaan contoh, lalu dokumen.
     *
     * @return array{jawaban: string, sumber: string, model: ?string, input_tokens: ?int, output_tokens: ?int, error: ?string}
     */
    private function askKnowledgeBase(string $pertanyaan): array
    {
        $faq = $this->matchFaq($pertanyaan);
        if ($faq) {
            $faq->increment('hit_count');

            return [
                'jawaban' => $faq->jawaban,
                'sumber' => 'faq',
                'model' => null,
                'input_tokens' => null,
                'output_tokens' => null,
                'error' => null,
            ];
        }

        $kutipan = $this->matchDocument($pertanyaan);
        if ($kutipan) {
            return [
                'jawaban' => $kutipan,
                'sumber' => 'dokumen',
                'model' => null,
                'input_tokens' => null,
                'output_tokens' => null,
                'error' => null,
            ];
        }

        return [
            'jawaban' => $this->fallbackMessage(),
            'sumber' => 'fallback',
            'model' => null,
            'input_tokens' => null,
            'output_tokens' => null,
            'error' => null,
        ];
    }

    /**
     * Cari pertanyaan contoh yang paling cocok dengan pertanyaan pengguna.
     */
    public function matchFaq(string $pertanyaan): ?KonsultasiAiFaq
    {
        $normal = $this->normalize($pertanyaan);
        if ($normal === '') {
            return null;
        }

        $tokenPengguna = $this->tokens($normal);

        $terbaik = null;
        $skorTerbaik = 0;

        foreach (KonsultasiAiFaq::active()->orderBy('urutan')->get() as $faq) {
            $skor = 0;
            $pertanyaanFaq = $this->normalize($faq->pertanyaan);

            if ($pertanyaanFaq !== '' && ($normal === $pertanyaanFaq
                || str_contains($normal, $pertanyaanFaq)
                || str_contains($pertanyaanFaq, $normal))) {
                $skor += 60;
            }

            foreach ($faq->kataKunciList() as $kunci) {
                $kunci = $this->normalize($kunci);
                if ($kunci !== '' && str_contains($normal, $kunci)) {
                    // Kata kunci yang lebih panjang & spesifik bernilai lebih tinggi.
                    $skor += 10 + min(10, mb_strlen($kunci));
                }
            }

            $tokenFaq = $this->tokens($pertanyaanFaq);
            $irisan = array_intersect($tokenPengguna, $tokenFaq);
            if ($tokenFaq !== []) {
                $skor += (int) round((count($irisan) / count($tokenFaq)) * 25);
            }

            if ($skor > $skorTerbaik) {
                $skorTerbaik = $skor;
                $terbaik = $faq;
            }
        }

        return $skorTerbaik >= self::AMBANG_SKOR_FAQ ? $terbaik : null;
    }

    /**
     * Cari paragraf paling relevan pada dokumen knowledge base.
     * Dipakai hanya pada mode prototipe, sebagai kutipan apa adanya.
     */
    private function matchDocument(string $pertanyaan): ?string
    {
        $tokenPengguna = $this->tokens($this->normalize($pertanyaan));
        if ($tokenPengguna === []) {
            return null;
        }

        $terbaik = null;
        $skorTerbaik = 0;

        foreach (KonsultasiAiDocument::active()->get() as $dok) {
            if (! $dok->hasKonten()) {
                continue;
            }

            foreach ($this->paragraphs($dok->konten) as $paragraf) {
                $tokenParagraf = $this->tokens($this->normalize($paragraf));
                if ($tokenParagraf === []) {
                    continue;
                }

                $skor = count(array_intersect($tokenPengguna, $tokenParagraf));
                if ($skor > $skorTerbaik) {
                    $skorTerbaik = $skor;
                    $terbaik = ['judul' => $dok->judul, 'teks' => $paragraf];
                }
            }
        }

        // Minimal dua kata kunci bermakna harus cocok agar kutipan tidak asal-asalan.
        if (! $terbaik || $skorTerbaik < 2) {
            return null;
        }

        $teks = mb_substr(trim($terbaik['teks']), 0, 1200);

        return "Kutipan dari dokumen **{$terbaik['judul']}**:\n\n{$teks}\n\n"
            . '_Jawaban ini merupakan kutipan langsung dari dokumen knowledge base. '
            . 'Untuk penjelasan yang lebih spesifik, silakan hubungi admin Diskominfo Provinsi Kalimantan Utara._';
    }

    /**
     * Susun seluruh knowledge base menjadi satu teks konteks untuk Claude.
     */
    public function knowledgeContext(): string
    {
        $bagian = [];

        $faqs = KonsultasiAiFaq::active()->orderBy('urutan')->get();
        if ($faqs->isNotEmpty()) {
            $isi = "## Pertanyaan yang Sering Diajukan\n";
            foreach ($faqs as $faq) {
                $isi .= "\n### {$faq->pertanyaan}\n{$faq->jawaban}\n";
            }
            $bagian[] = $isi;
        }

        foreach (KonsultasiAiDocument::active()->get() as $dok) {
            if (! $dok->hasKonten()) {
                continue;
            }

            $judul = "## Dokumen: {$dok->judul}";
            if (filled($dok->kategori)) {
                $judul .= " (kategori: {$dok->kategori})";
            }

            $bagian[] = $judul . "\n" . trim($dok->konten);
        }

        if ($bagian === []) {
            return '(Knowledge base masih kosong. Sampaikan bahwa informasi belum tersedia dan arahkan pengguna menghubungi admin Diskominfo Provinsi Kalimantan Utara.)';
        }

        return mb_substr(implode("\n\n---\n\n", $bagian), 0, self::MAX_KONTEKS_CHARS);
    }

    /**
     * Perkiraan ukuran knowledge base, untuk ditampilkan di halaman admin.
     */
    public function knowledgeSize(): int
    {
        return mb_strlen($this->knowledgeContext());
    }

    /**
     * Daftar pertanyaan contoh untuk ditampilkan sebagai saran di halaman chat.
     */
    public function contohPertanyaan(int $limit = 6)
    {
        return KonsultasiAiFaq::active()->orderBy('urutan')->limit($limit)->get();
    }

    /**
     * Ambil teks dari berkas yang diunggah admin.
     * PDF tidak diekstrak otomatis (butuh pustaka tambahan) — admin mengisi manual.
     */
    public function extractText(UploadedFile $file): ?string
    {
        $ext = strtolower($file->getClientOriginalExtension());

        if (in_array($ext, ['txt', 'md', 'csv'], true)) {
            $isi = @file_get_contents($file->getRealPath());

            return $isi === false ? null : $this->cleanText($isi);
        }

        if ($ext === 'docx' && class_exists(\ZipArchive::class)) {
            return $this->extractDocx($file->getRealPath());
        }

        return null;
    }

    /**
     * Ekstraksi teks .docx dengan membaca word/document.xml di dalam arsip zip.
     */
    private function extractDocx(string $path): ?string
    {
        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) {
            return null;
        }

        $xml = $zip->getFromName('word/document.xml');
        $zip->close();

        if ($xml === false) {
            return null;
        }

        // Jadikan akhir paragraf & baris sebagai newline sebelum tag dibuang.
        $xml = preg_replace('/<w:(p|br)\b[^>]*\/?>/', "\n", $xml);
        $teks = strip_tags((string) $xml);

        return $this->cleanText(html_entity_decode($teks, ENT_QUOTES | ENT_XML1, 'UTF-8'));
    }

    private function cleanText(string $teks): string
    {
        $teks = str_replace(["\r\n", "\r"], "\n", $teks);
        $teks = preg_replace("/[ \t]+/", ' ', $teks);
        $teks = preg_replace("/\n{3,}/", "\n\n", (string) $teks);

        return trim((string) $teks);
    }

    /**
     * Label sumber jawaban untuk ditampilkan di antarmuka.
     */
    public static function labelSumber(?string $sumber): string
    {
        return match ($sumber) {
            'ai' => 'Dijawab asisten AI',
            'faq' => 'Dari basis pengetahuan',
            'dokumen' => 'Dari dokumen knowledge base',
            default => 'Belum tersedia di basis pengetahuan',
        };
    }

    /**
     * Pemformatan markdown ringan (tebal, miring, daftar) menjadi HTML aman.
     * Padanan sisi server dari formatter di halaman chat.
     */
    public static function formatToHtml(?string $teks): string
    {
        $teks = trim((string) $teks);
        if ($teks === '') {
            return '';
        }

        $blok = preg_split("/\n\s*\n/", e($teks)) ?: [];
        $html = '';

        foreach ($blok as $bagian) {
            $baris = array_values(array_filter(array_map('trim', explode("\n", $bagian)), fn ($b) => $b !== ''));
            if ($baris === []) {
                continue;
            }

            $inline = static function (string $s): string {
                $s = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $s);

                return (string) preg_replace('/(^|[\s(])_(.+?)_(?=[\s.,)]|$)/s', '$1<em>$2</em>', (string) $s);
            };

            $semuaBullet = ! array_filter($baris, fn ($b) => ! preg_match('/^[-*]\s+/', $b));
            $semuaNomor = ! array_filter($baris, fn ($b) => ! preg_match('/^\d+[.)]\s+/', $b));

            if ($semuaBullet) {
                $html .= '<ul>';
                foreach ($baris as $b) {
                    $html .= '<li>' . $inline(preg_replace('/^[-*]\s+/', '', $b)) . '</li>';
                }
                $html .= '</ul>';
            } elseif ($semuaNomor) {
                $html .= '<ol>';
                foreach ($baris as $b) {
                    $html .= '<li>' . $inline(preg_replace('/^\d+[.)]\s+/', '', $b)) . '</li>';
                }
                $html .= '</ol>';
            } else {
                $html .= '<p>' . $inline(implode('<br>', $baris)) . '</p>';
            }
        }

        return $html;
    }

    /**
     * @return array<int, string>
     */
    private function paragraphs(string $teks): array
    {
        $bagian = preg_split("/\n\s*\n/", $teks) ?: [];
        $bagian = array_map('trim', $bagian);

        return array_values(array_filter($bagian, fn ($p) => mb_strlen($p) >= 40));
    }

    private function normalize(string $teks): string
    {
        $teks = mb_strtolower(trim($teks));
        $teks = preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $teks);
        $teks = preg_replace('/\s+/', ' ', (string) $teks);

        return trim((string) $teks);
    }

    /**
     * @return array<int, string>
     */
    private function tokens(string $normal): array
    {
        $kata = explode(' ', $normal);
        $kata = array_filter(
            $kata,
            fn ($k) => mb_strlen($k) > 3 && ! in_array($k, self::STOPWORDS, true)
        );

        return array_values(array_unique($kata));
    }
}
