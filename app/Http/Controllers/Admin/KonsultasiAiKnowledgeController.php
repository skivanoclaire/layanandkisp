<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KonsultasiAiCategory;
use App\Models\KonsultasiAiChat;
use App\Models\KonsultasiAiDocument;
use App\Models\KonsultasiAiFaq;
use App\Models\KonsultasiAiSetting;
use App\Services\KonsultasiAiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Pengelolaan Knowledge Base Konsultasi SPBE Berbasis AI:
 * dokumen dasar, pertanyaan contoh, pengaturan asisten, dan riwayat percakapan.
 */
class KonsultasiAiKnowledgeController extends Controller
{
    private const DISK = 'public';
    private const FOLDER = 'konsultasi-ai/dokumen';

    public function __construct(private readonly KonsultasiAiService $ai)
    {
    }

    public function index()
    {
        return view('admin.konsultasi-ai.index', [
            'dokumen'   => KonsultasiAiDocument::with('uploader')->latest('id')->get(),
            'faqs'      => KonsultasiAiFaq::orderBy('urutan')->orderBy('id')->get(),
            'tipeKategori'    => KonsultasiAiCategory::tipeLabels(),
            'semuaKategori'   => KonsultasiAiCategory::urut()->get()->groupBy('tipe'),
            'kategoriDokumen' => KonsultasiAiCategory::tipe(KonsultasiAiCategory::TIPE_DOKUMEN)->active()->urut()->get(),
            'kategoriFaq'     => KonsultasiAiCategory::tipe(KonsultasiAiCategory::TIPE_FAQ)->active()->urut()->get(),
            'pemakaianKategori' => [
                KonsultasiAiCategory::TIPE_DOKUMEN => $this->hitungPemakaian(KonsultasiAiDocument::class),
                KonsultasiAiCategory::TIPE_FAQ     => $this->hitungPemakaian(KonsultasiAiFaq::class),
            ],
            'aiAktif'   => $this->ai->isAiActive(),
            'hasApiKey' => $this->ai->hasApiKey(),
            'settings'  => [
                'ai_enabled'       => KonsultasiAiSetting::get('ai_enabled', '0'),
                'model'            => $this->ai->model(),
                'system_prompt'    => $this->ai->systemPrompt(),
                'fallback_message' => $this->ai->fallbackMessage(),
            ],
            'ukuranKonteks' => $this->ai->knowledgeSize(),
            'statistik'     => [
                'total_chat'    => KonsultasiAiChat::count(),
                'chat_ai'       => KonsultasiAiChat::where('sumber', 'ai')->count(),
                'chat_terjawab' => KonsultasiAiChat::whereIn('sumber', ['ai', 'faq', 'dokumen'])->count(),
                'chat_gagal'    => KonsultasiAiChat::where('sumber', 'fallback')->count(),
            ],
            'pertanyaanTakTerjawab' => KonsultasiAiChat::where('sumber', 'fallback')
                ->latest('id')
                ->limit(15)
                ->get(),
        ]);
    }

    /**
     * Jumlah data per nama kategori, untuk ditampilkan pada daftar kategori.
     *
     * @param  class-string<KonsultasiAiDocument|KonsultasiAiFaq>  $model
     * @return array<string, int>
     */
    private function hitungPemakaian(string $model): array
    {
        return $model::query()
            ->whereNotNull('kategori')
            ->where('kategori', '<>', '')
            ->groupBy('kategori')
            ->selectRaw('kategori, COUNT(*) as jumlah')
            ->pluck('jumlah', 'kategori')
            ->all();
    }

    // ------------------------------------------------------------------ Dokumen

    public function storeDocument(Request $request)
    {
        $data = $request->validate([
            'judul'     => ['required', 'string', 'max:200'],
            'kategori'  => $this->aturanKategori(KonsultasiAiCategory::TIPE_DOKUMEN),
            'deskripsi' => ['nullable', 'string', 'max:1000'],
            'file'      => ['nullable', 'file', 'max:51200', 'mimes:pdf,doc,docx,txt,md,csv'],
            'konten'    => ['nullable', 'string'],
        ], [
            'file.mimes' => 'Format berkas harus PDF, DOC, DOCX, TXT, MD, atau CSV.',
            'file.max'   => 'Ukuran berkas maksimal 50 MB.',
        ]);

        if (blank($data['konten'] ?? null) && ! $request->hasFile('file')) {
            return back()
                ->withInput()
                ->withErrors(['file' => 'Unggah berkas atau isi teks knowledge base secara manual.']);
        }

        $dokumen = new KonsultasiAiDocument([
            'judul'       => $data['judul'],
            'kategori'    => $data['kategori'] ?? null,
            'deskripsi'   => $data['deskripsi'] ?? null,
            'konten'      => $data['konten'] ?? null,
            'is_active'   => true,
            'uploaded_by' => auth()->id(),
        ]);

        $pesan = 'Dokumen knowledge base ditambahkan.';

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
            $filename = ($filename ?: 'dokumen') . '-' . time() . '.' . $file->getClientOriginalExtension();

            $dokumen->file_path = $file->storeAs(self::FOLDER, $filename, self::DISK);
            $dokumen->file_name = $file->getClientOriginalName();
            $dokumen->file_mime = $file->getClientMimeType();
            $dokumen->file_size = $file->getSize();

            if (blank($dokumen->konten)) {
                $teks = $this->ai->extractText($file);
                $dokumen->konten = $teks;

                if (blank($teks)) {
                    $pesan = 'Dokumen tersimpan, tetapi teksnya belum bisa diekstrak otomatis '
                        . '(PDF dan DOC lama belum didukung). Silakan buka Edit dan tempelkan isi dokumen '
                        . 'agar dapat dipakai sebagai dasar jawaban AI.';
                }
            }
        }

        $dokumen->save();

        return redirect()->route('admin.konsultasi-ai.index')->with('status', $pesan);
    }

    public function editDocument(KonsultasiAiDocument $dokumen)
    {
        return view('admin.konsultasi-ai.edit-dokumen', [
            'dokumen'      => $dokumen,
            'pilihanKategori' => $this->pilihanKategori(KonsultasiAiCategory::TIPE_DOKUMEN, $dokumen->kategori),
        ]);
    }

    public function updateDocument(Request $request, KonsultasiAiDocument $dokumen)
    {
        $data = $request->validate([
            'judul'     => ['required', 'string', 'max:200'],
            'kategori'  => $this->aturanKategori(KonsultasiAiCategory::TIPE_DOKUMEN, $dokumen->kategori),
            'deskripsi' => ['nullable', 'string', 'max:1000'],
            'konten'    => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $dokumen->update([
            'judul'     => $data['judul'],
            'kategori'  => $data['kategori'] ?? null,
            'deskripsi' => $data['deskripsi'] ?? null,
            'konten'    => $data['konten'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.konsultasi-ai.index')->with('status', 'Dokumen diperbarui.');
    }

    public function toggleDocument(KonsultasiAiDocument $dokumen)
    {
        $dokumen->update(['is_active' => ! $dokumen->is_active]);

        return back()->with('status', $dokumen->is_active
            ? 'Dokumen diaktifkan sebagai dasar jawaban.'
            : 'Dokumen dinonaktifkan.');
    }

    public function destroyDocument(KonsultasiAiDocument $dokumen)
    {
        if ($dokumen->file_path) {
            Storage::disk(self::DISK)->delete($dokumen->file_path);
        }

        $dokumen->delete();

        return back()->with('status', 'Dokumen dihapus.');
    }

    // ------------------------------------------------------- Pertanyaan contoh

    public function storeFaq(Request $request)
    {
        $data = $this->validateFaq($request);
        $data['urutan'] = $data['urutan'] ?? ((int) KonsultasiAiFaq::max('urutan') + 1);
        $data['is_active'] = $request->boolean('is_active', true);

        KonsultasiAiFaq::create($data);

        return back()->with('status', 'Pertanyaan contoh ditambahkan.');
    }

    public function editFaq(KonsultasiAiFaq $faq)
    {
        return view('admin.konsultasi-ai.edit-faq', [
            'faq'             => $faq,
            'pilihanKategori' => $this->pilihanKategori(KonsultasiAiCategory::TIPE_FAQ, $faq->kategori),
        ]);
    }

    public function updateFaq(Request $request, KonsultasiAiFaq $faq)
    {
        $data = $this->validateFaq($request, $faq->kategori);
        $data['is_active'] = $request->boolean('is_active');

        $faq->update($data);

        return redirect()->route('admin.konsultasi-ai.index')->with('status', 'Pertanyaan contoh diperbarui.');
    }

    public function destroyFaq(KonsultasiAiFaq $faq)
    {
        $faq->delete();

        return back()->with('status', 'Pertanyaan contoh dihapus.');
    }

    private function validateFaq(Request $request, ?string $kategoriSaatIni = null): array
    {
        return $request->validate([
            'pertanyaan' => ['required', 'string', 'max:255'],
            'jawaban'    => ['required', 'string'],
            'kategori'   => $this->aturanKategori(KonsultasiAiCategory::TIPE_FAQ, $kategoriSaatIni),
            'kata_kunci' => ['nullable', 'string', 'max:255'],
            'urutan'     => ['nullable', 'integer', 'min:0', 'max:9999'],
        ], [
            'pertanyaan.required' => 'Pertanyaan wajib diisi.',
            'jawaban.required'    => 'Jawaban wajib diisi.',
            'kategori.in'         => 'Kategori tidak dikenal. Pilih dari daftar kategori yang tersedia.',
        ]);
    }

    // ---------------------------------------------------------------- Kategori

    public function storeCategory(Request $request)
    {
        $data = $this->validateCategory($request);
        $data['urutan'] = $data['urutan'] ?? ((int) KonsultasiAiCategory::tipe($data['tipe'])->max('urutan') + 1);
        $data['is_active'] = $request->boolean('is_active', true);

        KonsultasiAiCategory::create($data);

        return back()->with('status', 'Kategori ditambahkan.');
    }

    public function editCategory(KonsultasiAiCategory $kategori)
    {
        return view('admin.konsultasi-ai.edit-kategori', [
            'kategori'  => $kategori,
            'pemakaian' => $kategori->jumlahPemakaian(),
        ]);
    }

    public function updateCategory(Request $request, KonsultasiAiCategory $kategori)
    {
        $data = $this->validateCategory($request, $kategori);
        $data['is_active'] = $request->boolean('is_active');

        $namaLama = $kategori->nama;
        $tipeLama = $kategori->tipe;

        DB::transaction(function () use ($kategori, $data, $namaLama, $tipeLama) {
            $kategori->update($data);

            // Nama kategori tersimpan sebagai teks pada dokumen/pertanyaan,
            // jadi perubahan nama harus ikut merapikan data yang memakainya.
            if ($data['nama'] !== $namaLama) {
                $this->modelKategori($tipeLama)::where('kategori', $namaLama)
                    ->update(['kategori' => $data['nama']]);
            }
        });

        return redirect()->route('admin.konsultasi-ai.index')->with('status', 'Kategori diperbarui.');
    }

    public function toggleCategory(KonsultasiAiCategory $kategori)
    {
        $kategori->update(['is_active' => ! $kategori->is_active]);

        return back()->with('status', $kategori->is_active
            ? 'Kategori diaktifkan dan kembali muncul sebagai pilihan.'
            : 'Kategori dinonaktifkan — tidak lagi ditawarkan pada formulir baru.');
    }

    public function destroyCategory(KonsultasiAiCategory $kategori)
    {
        $pemakaian = $kategori->jumlahPemakaian();

        if ($pemakaian > 0) {
            return back()->withErrors([
                'kategori' => "Kategori \"{$kategori->nama}\" masih dipakai {$pemakaian} data. "
                    . 'Pindahkan data tersebut ke kategori lain, atau nonaktifkan kategori ini.',
            ]);
        }

        $kategori->delete();

        return back()->with('status', 'Kategori dihapus.');
    }

    /**
     * Tipe kategori tidak bisa dipindah setelah dibuat agar nama kategori
     * tetap sinkron dengan data dokumen/pertanyaan yang memakainya.
     */
    private function validateCategory(Request $request, ?KonsultasiAiCategory $kategori = null): array
    {
        $request->merge(['nama' => trim((string) $request->input('nama'))]);

        $rules = [
            'nama'      => [
                'required', 'string', 'max:100',
                Rule::unique('konsultasi_ai_categories', 'nama')
                    ->where(fn ($q) => $q->where('tipe', $kategori?->tipe ?? $request->input('tipe')))
                    ->ignore($kategori?->id),
            ],
            'deskripsi' => ['nullable', 'string', 'max:255'],
            'urutan'    => ['nullable', 'integer', 'min:0', 'max:9999'],
        ];

        if (! $kategori) {
            $rules['tipe'] = ['required', Rule::in(array_keys(KonsultasiAiCategory::tipeLabels()))];
        }

        $data = $request->validate($rules, [
            'nama.required' => 'Nama kategori wajib diisi.',
            'nama.unique'   => 'Nama kategori sudah dipakai pada tipe yang sama.',
            'tipe.required' => 'Pilih tipe kategori.',
            'tipe.in'       => 'Tipe kategori tidak dikenal.',
        ]);

        return $data;
    }

    /**
     * Aturan validasi kolom kategori: hanya menerima nama dari daftar master,
     * ditambah nilai yang sedang dipakai data agar penyuntingan lama tidak tertolak.
     *
     * @return array<int, mixed>
     */
    private function aturanKategori(string $tipe, ?string $kategoriSaatIni = null): array
    {
        $pilihan = $this->pilihanKategori($tipe, $kategoriSaatIni);

        return ['nullable', 'string', 'max:100', Rule::in($pilihan)];
    }

    /**
     * Nama kategori aktif untuk satu tipe, ditambah nilai lama yang sedang dipakai.
     *
     * @return array<int, string>
     */
    private function pilihanKategori(string $tipe, ?string $kategoriSaatIni = null): array
    {
        $pilihan = KonsultasiAiCategory::namaUntuk($tipe)->all();

        if (filled($kategoriSaatIni) && ! in_array($kategoriSaatIni, $pilihan, true)) {
            array_unshift($pilihan, $kategoriSaatIni);
        }

        return $pilihan;
    }

    /**
     * @return class-string<KonsultasiAiDocument|KonsultasiAiFaq>
     */
    private function modelKategori(string $tipe): string
    {
        return $tipe === KonsultasiAiCategory::TIPE_DOKUMEN
            ? KonsultasiAiDocument::class
            : KonsultasiAiFaq::class;
    }

    // ------------------------------------------------------------- Pengaturan

    public function updateSettings(Request $request)
    {
        $data = $request->validate([
            'model'            => ['required', 'string', 'max:60'],
            'system_prompt'    => ['required', 'string', 'max:20000'],
            'fallback_message' => ['required', 'string', 'max:5000'],
            'ai_enabled'       => ['nullable', 'boolean'],
        ]);

        $aktif = $request->boolean('ai_enabled');

        if ($aktif && ! $this->ai->hasApiKey()) {
            return back()->withErrors([
                'ai_enabled' => 'API key Anthropic belum diisi pada berkas .env (ANTHROPIC_API_KEY). '
                    . 'Asisten AI tidak dapat diaktifkan.',
            ])->withInput();
        }

        KonsultasiAiSetting::put('ai_enabled', $aktif ? '1' : '0');
        KonsultasiAiSetting::put('model', $data['model']);
        KonsultasiAiSetting::put('system_prompt', $data['system_prompt']);
        KonsultasiAiSetting::put('fallback_message', $data['fallback_message']);

        return back()->with('status', $aktif
            ? 'Pengaturan disimpan. Asisten AI aktif — pertanyaan akan dijawab oleh Claude.'
            : 'Pengaturan disimpan. Layanan berjalan pada mode prototipe (knowledge base lokal).');
    }

    /**
     * Uji koneksi ke API Anthropic dengan satu pertanyaan singkat.
     */
    public function testConnection()
    {
        if (! $this->ai->hasApiKey()) {
            return back()->withErrors([
                'ai_enabled' => 'API key Anthropic belum diisi pada berkas .env (ANTHROPIC_API_KEY).',
            ]);
        }

        if (! $this->ai->isAiActive()) {
            return back()->withErrors([
                'ai_enabled' => 'Aktifkan asisten AI terlebih dahulu sebelum menguji koneksi.',
            ]);
        }

        $hasil = $this->ai->ask('Sebutkan satu kalimat singkat tentang SPBE.');

        if (filled($hasil['error'])) {
            return back()->withErrors(['ai_enabled' => 'Uji koneksi gagal: ' . $hasil['error']]);
        }

        return back()->with('status', 'Uji koneksi berhasil. Jawaban model: ' . Str::limit($hasil['jawaban'], 200));
    }
}
