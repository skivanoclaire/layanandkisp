<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\KonsultasiSpbeAiAccess;
use App\Services\SurveiDigitalService;
use Illuminate\Http\Request;

class KonsultasiSpbeAiController extends Controller
{
    /**
     * URL asisten ChatGPT SPBE Kalimantan Utara.
     */
    private const CHAT_URL = 'https://chatgpt.com/g/g-68d4e245a8348191b95faca91144169f-asisten-spbe-kalimantan-utara';

    public function index()
    {
        return view('user.konsultasi-spbe-ai.index', [
            // Tombol "Beri Penilaian" muncul setelah pengguna pernah menekan "Akses Disini"
            'hasAccessed' => KonsultasiSpbeAiAccess::hasAccessed((int) auth()->id()),
        ]);
    }

    /**
     * Catat bahwa pengguna menekan "Akses Disini", lalu arahkan ke asisten AI.
     */
    public function access(Request $request)
    {
        $access = KonsultasiSpbeAiAccess::firstOrNew(['user_id' => auth()->id()]);
        $access->access_count = ($access->access_count ?? 0) + 1;
        $access->last_accessed_at = now();
        $access->save();

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
}
