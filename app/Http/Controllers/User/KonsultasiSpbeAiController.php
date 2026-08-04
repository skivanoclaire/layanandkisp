<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\KonsultasiAiChat;
use App\Models\KonsultasiSpbeAiAccess;
use App\Services\KonsultasiAiService;
use App\Services\SurveiDigitalService;
use Illuminate\Http\Request;

class KonsultasiSpbeAiController extends Controller
{
    /**
     * URL asisten ChatGPT SPBE Kalimantan Utara.
     */
    private const CHAT_URL = 'https://chatgpt.com/g/g-68d4e245a8348191b95faca91144169f-asisten-spbe-kalimantan-utara';

    /** Jumlah pasangan tanya-jawab terakhir yang dikirim ulang sebagai konteks percakapan. */
    private const RIWAYAT_KONTEKS = 5;

    public function __construct(private readonly KonsultasiAiService $ai)
    {
    }

    public function index()
    {
        return view('user.konsultasi-spbe-ai.index', [
            // Tombol "Beri Penilaian" muncul setelah pengguna pernah menggunakan layanan.
            'hasAccessed' => KonsultasiSpbeAiAccess::hasAccessed((int) auth()->id()),
            'aiAktif'     => $this->ai->isAiActive(),
        ]);
    }

    /**
     * Halaman "Tanya Langsung" — percakapan di dalam portal.
     */
    public function chat()
    {
        $this->catatAkses();

        return view('user.konsultasi-spbe-ai.chat', [
            'contohPertanyaan' => $this->ai->contohPertanyaan(),
            'aiAktif'          => $this->ai->isAiActive(),
            'riwayat'          => KonsultasiAiChat::where('user_id', auth()->id())
                ->latest('id')
                ->limit(20)
                ->get()
                ->reverse()
                ->values(),
        ]);
    }

    /**
     * Terima pertanyaan dari halaman chat dan kembalikan jawaban (JSON).
     */
    public function ask(Request $request)
    {
        $data = $request->validate([
            'pertanyaan' => ['required', 'string', 'min:3', 'max:2000'],
        ], [
            'pertanyaan.required' => 'Pertanyaan tidak boleh kosong.',
            'pertanyaan.min'      => 'Pertanyaan terlalu pendek.',
            'pertanyaan.max'      => 'Pertanyaan maksimal 2000 karakter.',
        ]);

        $hasil = $this->ai->ask($data['pertanyaan'], $this->riwayatKonteks());

        $chat = KonsultasiAiChat::create([
            'user_id'       => auth()->id(),
            'pertanyaan'    => $data['pertanyaan'],
            'jawaban'       => $hasil['jawaban'],
            'sumber'        => $hasil['sumber'],
            'model'         => $hasil['model'],
            'input_tokens'  => $hasil['input_tokens'],
            'output_tokens' => $hasil['output_tokens'],
            'error'         => $hasil['error'],
        ]);

        return response()->json([
            'id'         => $chat->id,
            'jawaban'    => $hasil['jawaban'],
            'sumber'     => $hasil['sumber'],
            'waktu'      => $chat->created_at->format('H:i'),
            'ai_aktif'   => $this->ai->isAiActive(),
        ]);
    }

    /**
     * Hapus riwayat percakapan pengguna sendiri.
     */
    public function clear()
    {
        KonsultasiAiChat::where('user_id', auth()->id())->delete();

        return redirect()
            ->route('user.konsultasi-spbe-ai.chat')
            ->with('status', 'Riwayat percakapan telah dihapus.');
    }

    /**
     * Catat penggunaan layanan, lalu arahkan ke asisten ChatGPT.
     */
    public function access(Request $request)
    {
        $this->catatAkses();

        return redirect()->away(self::CHAT_URL);
    }

    /**
     * Halaman "Beri Penilaian" — hanya untuk pengguna yang pernah mengakses layanan.
     */
    public function survey()
    {
        if (! KonsultasiSpbeAiAccess::hasAccessed((int) auth()->id())) {
            abort(403, 'Penilaian tersedia setelah Anda mengakses layanan Konsultasi SPBE Berbasis AI.');
        }

        $service = SurveiDigitalService::service('konsultasi-spbe-ai');

        return view('survei-digital.embed', [
            'heading'   => $service['heading'],
            'color'     => $service['color'],
            'subtitle'  => null,
            'backUrl'   => route('user.konsultasi-spbe-ai.index'),
            'surveyUrl' => SurveiDigitalService::urlFor('konsultasi-spbe-ai'),
        ]);
    }

    private function catatAkses(): void
    {
        $access = KonsultasiSpbeAiAccess::firstOrNew(['user_id' => auth()->id()]);
        $access->access_count = ($access->access_count ?? 0) + 1;
        $access->last_accessed_at = now();
        $access->save();
    }

    /**
     * Beberapa percakapan terakhir, dikirim ulang agar jawaban AI nyambung.
     *
     * @return array<int, array{role: string, content: string}>
     */
    private function riwayatKonteks(): array
    {
        $riwayat = [];

        $chats = KonsultasiAiChat::where('user_id', auth()->id())
            ->latest('id')
            ->limit(self::RIWAYAT_KONTEKS)
            ->get()
            ->reverse();

        foreach ($chats as $chat) {
            $riwayat[] = ['role' => 'user', 'content' => $chat->pertanyaan];
            if (filled($chat->jawaban)) {
                $riwayat[] = ['role' => 'assistant', 'content' => $chat->jawaban];
            }
        }

        return $riwayat;
    }
}
