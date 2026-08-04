<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KonsultasiAiChat;
use App\Models\KonsultasiAiDocument;
use App\Models\KonsultasiAiFaq;
use App\Models\KonsultasiAiSetting;
use App\Services\KonsultasiAiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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

    // ------------------------------------------------------------------ Dokumen

    public function storeDocument(Request $request)
    {
        $data = $request->validate([
            'judul'     => ['required', 'string', 'max:200'],
            'kategori'  => ['nullable', 'string', 'max:100'],
            'deskripsi' => ['nullable', 'string', 'max:1000'],
            'file'      => ['nullable', 'file', 'max:10240', 'mimes:pdf,doc,docx,txt,md,csv'],
            'konten'    => ['nullable', 'string'],
        ], [
            'file.mimes' => 'Format berkas harus PDF, DOC, DOCX, TXT, MD, atau CSV.',
            'file.max'   => 'Ukuran berkas maksimal 10 MB.',
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
        return view('admin.konsultasi-ai.edit-dokumen', compact('dokumen'));
    }

    public function updateDocument(Request $request, KonsultasiAiDocument $dokumen)
    {
        $data = $request->validate([
            'judul'     => ['required', 'string', 'max:200'],
            'kategori'  => ['nullable', 'string', 'max:100'],
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
        return view('admin.konsultasi-ai.edit-faq', compact('faq'));
    }

    public function updateFaq(Request $request, KonsultasiAiFaq $faq)
    {
        $data = $this->validateFaq($request);
        $data['is_active'] = $request->boolean('is_active');

        $faq->update($data);

        return redirect()->route('admin.konsultasi-ai.index')->with('status', 'Pertanyaan contoh diperbarui.');
    }

    public function destroyFaq(KonsultasiAiFaq $faq)
    {
        $faq->delete();

        return back()->with('status', 'Pertanyaan contoh dihapus.');
    }

    private function validateFaq(Request $request): array
    {
        return $request->validate([
            'pertanyaan' => ['required', 'string', 'max:255'],
            'jawaban'    => ['required', 'string'],
            'kategori'   => ['nullable', 'string', 'max:100'],
            'kata_kunci' => ['nullable', 'string', 'max:255'],
            'urutan'     => ['nullable', 'integer', 'min:0', 'max:9999'],
        ], [
            'pertanyaan.required' => 'Pertanyaan wajib diisi.',
            'jawaban.required'    => 'Jawaban wajib diisi.',
        ]);
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
