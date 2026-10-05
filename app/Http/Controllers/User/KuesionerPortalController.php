<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\KuesionerPortalPeriode;
use App\Models\KuesionerPortalResponse;
use App\Models\UnitKerja;
use App\Services\KuesionerPortal\Instrumen;
use App\Services\KuesionerPortal\Kelayakan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Pengisian Kuesioner Kualitas Layanan Portal oleh responden (ASN/operator OPD).
 * Alur: persetujuan → isi Bagian A–E → selesai. Satu akun satu kali per periode.
 */
class KuesionerPortalController extends Controller
{
    public function __construct(private Kelayakan $kelayakan)
    {
    }

    public function index()
    {
        $periode = KuesionerPortalPeriode::aktif();
        $response = $periode
            ? KuesionerPortalResponse::where('periode_id', $periode->id)->where('user_id', Auth::id())->first()
            : null;
        $memenuhi = $this->kelayakan->memenuhi(Auth::user());

        return view('user.kuesioner-portal.index', compact('periode', 'response', 'memenuhi'));
    }

    public function persetujuan(Request $request)
    {
        $periode = $this->periodeAktifAtau404();
        abort_unless($this->kelayakan->memenuhi(Auth::user()), 403, 'Akun Anda belum memenuhi kriteria responden.');

        $request->validate(['setuju' => ['required', Rule::in(['ya', 'tidak'])]]);

        $response = KuesionerPortalResponse::firstOrNew([
            'periode_id' => $periode->id,
            'user_id' => Auth::id(),
        ]);

        if ($response->status === KuesionerPortalResponse::STATUS_SELESAI) {
            return redirect()->route('kuesioner-portal.lihat');
        }

        $response->kode_responden ??= KuesionerPortalResponse::kodeBaru();

        if ($request->setuju === 'tidak') {
            $response->fill(['status' => KuesionerPortalResponse::STATUS_MENOLAK, 'persetujuan_at' => null])->save();

            return redirect()->route('kuesioner-portal.index')
                ->with('success', 'Terima kasih. Anda dapat berubah pikiran dan mengisi kuesioner selama periode masih dibuka.');
        }

        // Waktu mulai diambil saat persetujuan untuk menghitung durasi pengisian.
        $response->fill([
            'status' => KuesionerPortalResponse::STATUS_PERSETUJU,
            'persetujuan_at' => now(),
        ])->save();

        return redirect()->route('kuesioner-portal.isi');
    }

    public function isi()
    {
        $periode = $this->periodeAktifAtau404();
        $response = $this->responseMilikSendiri($periode);

        if ($response?->status === KuesionerPortalResponse::STATUS_SELESAI) {
            return redirect()->route('kuesioner-portal.lihat');
        }
        if ($response?->status !== KuesionerPortalResponse::STATUS_PERSETUJU) {
            return redirect()->route('kuesioner-portal.index');
        }

        return view('user.kuesioner-portal.isi', $this->dataFormulir() + [
            'response' => $response,
            'pratinjau' => false,
            'unitKerjaDefault' => Auth::user()->unit_kerja_id,
        ]);
    }

    public function simpan(Request $request)
    {
        $periode = $this->periodeAktifAtau404();
        $response = $this->responseMilikSendiri($periode);

        if (! $response || $response->status !== KuesionerPortalResponse::STATUS_PERSETUJU) {
            return redirect()->route('kuesioner-portal.index')
                ->with('warning', 'Kuesioner sudah pernah dikirim atau persetujuan belum diberikan.');
        }

        $data = $request->validate($this->aturan(), [], $this->namaAtribut());
        $pernahHubungi = (bool) $data['pernah_hubungi_petugas'];
        $bersyarat = Instrumen::kodeKinerjaBersyarat();

        DB::transaction(function () use ($response, $data, $request, $pernahHubungi, $bersyarat) {
            $response->answers()->delete();

            foreach (Instrumen::kodeAtribut() as $kode) {
                $response->answers()->create([
                    'kode' => $kode,
                    'kepentingan' => $data['kepentingan'][$kode],
                    'kinerja' => (! in_array($kode, $bersyarat, true) || $pernahHubungi) ? $data['kinerja'][$kode] : null,
                    'kano_fungsional' => $data['kano'][$kode]['f'],
                    'kano_disfungsional' => $data['kano'][$kode]['d'],
                ]);
            }
            foreach (Instrumen::kodeKepuasan() as $kode) {
                $response->answers()->create([
                    'kode' => $kode,
                    'kinerja' => $data['kepuasan'][$kode],
                ]);
            }

            $response->update([
                'status' => KuesionerPortalResponse::STATUS_SELESAI,
                'submitted_at' => now(),
                'durasi_detik' => $response->persetujuan_at ? (int) $response->persetujuan_at->diffInSeconds(now(), true) : null,
                'unit_kerja_id' => $data['unit_kerja_id'],
                'status_kepegawaian' => $data['status_kepegawaian'],
                'peran' => $data['peran'],
                'lama_penggunaan' => $data['lama_penggunaan'],
                'frekuensi' => $data['frekuensi'],
                'layanan_diajukan' => array_values($data['layanan_diajukan']),
                'layanan_lainnya' => in_array('lainnya', $data['layanan_diajukan'], true) ? ($data['layanan_lainnya'] ?? null) : null,
                'pernah_hubungi_petugas' => $pernahHubungi,
                'kelebihan' => $data['kelebihan'] ?? null,
                'kekurangan' => $data['kekurangan'] ?? null,
                'saran' => $data['saran'] ?? null,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        });

        return redirect()->route('kuesioner-portal.lihat')
            ->with('success', 'Terima kasih! Jawaban kuesioner Anda telah tersimpan.');
    }

    public function lihat()
    {
        $periode = $this->periodeAktifAtau404();
        $response = $this->responseMilikSendiri($periode);

        if ($response?->status !== KuesionerPortalResponse::STATUS_SELESAI) {
            return redirect()->route('kuesioner-portal.index');
        }

        $response->load(['answers', 'unitKerja']);

        return view('user.kuesioner-portal.lihat', compact('periode', 'response'));
    }

    private function aturan(): array
    {
        $profil = config('kuesioner_portal.profil');
        $skala = ['required', 'integer', 'between:1,5'];
        $bersyarat = Instrumen::kodeKinerjaBersyarat();

        $aturan = [
            'unit_kerja_id' => ['required', 'exists:unit_kerjas,id'],
            'status_kepegawaian' => ['required', Rule::in(array_keys($profil['status_kepegawaian']))],
            'peran' => ['required', Rule::in(array_keys($profil['peran']))],
            'lama_penggunaan' => ['required', Rule::in(array_keys($profil['lama_penggunaan']))],
            'frekuensi' => ['required', Rule::in(array_keys($profil['frekuensi']))],
            'layanan_diajukan' => ['required', 'array', 'min:1'],
            'layanan_diajukan.*' => [Rule::in(array_keys($profil['layanan']))],
            'layanan_lainnya' => ['nullable', 'string', 'max:255'],
            'pernah_hubungi_petugas' => ['required', 'boolean'],
            'kelebihan' => ['nullable', 'string', 'max:2000'],
            'kekurangan' => ['nullable', 'string', 'max:2000'],
            'saran' => ['nullable', 'string', 'max:2000'],
        ];

        foreach (Instrumen::kodeAtribut() as $kode) {
            $aturan["kepentingan.$kode"] = $skala;
            $aturan["kinerja.$kode"] = in_array($kode, $bersyarat, true)
                ? ['nullable', 'required_if:pernah_hubungi_petugas,1', 'integer', 'between:1,5']
                : $skala;
            $aturan["kano.$kode.f"] = $skala;
            $aturan["kano.$kode.d"] = $skala;
        }
        foreach (Instrumen::kodeKepuasan() as $kode) {
            $aturan["kepuasan.$kode"] = $skala;
        }

        return $aturan;
    }

    private function namaAtribut(): array
    {
        $nama = [];
        foreach (Instrumen::kodeAtribut() as $kode) {
            $nama["kepentingan.$kode"] = "kepentingan $kode";
            $nama["kinerja.$kode"] = "kinerja $kode";
            $nama["kano.$kode.f"] = "$kode (terpenuhi)";
            $nama["kano.$kode.d"] = "$kode (tidak terpenuhi)";
        }
        foreach (Instrumen::kodeKepuasan() as $kode) {
            $nama["kepuasan.$kode"] = $kode;
        }

        return $nama;
    }

    public static function dataFormulir(): array
    {
        return [
            'atribut' => Instrumen::atribut(),
            'kinerjaBersyarat' => Instrumen::kodeKinerjaBersyarat(),
            'kepuasan' => config('kuesioner_portal.kepuasan'),
            'profil' => config('kuesioner_portal.profil'),
            'skalaKepentingan' => config('kuesioner_portal.skala_kepentingan'),
            'skalaKinerja' => config('kuesioner_portal.skala_kinerja'),
            'skalaKano' => config('kuesioner_portal.skala_kano'),
            'unitKerjas' => UnitKerja::where('is_active', true)->orderBy('nama')->get(['id', 'nama']),
        ];
    }

    private function periodeAktifAtau404(): KuesionerPortalPeriode
    {
        $periode = KuesionerPortalPeriode::aktif();
        abort_unless($periode, 404, 'Belum ada periode kuesioner yang dibuka.');

        return $periode;
    }

    private function responseMilikSendiri(KuesionerPortalPeriode $periode): ?KuesionerPortalResponse
    {
        return KuesionerPortalResponse::where('periode_id', $periode->id)
            ->where('user_id', Auth::id())
            ->first();
    }
}
