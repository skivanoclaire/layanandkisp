<?php

namespace App\Http\Controllers\Admin;

use App\Exports\KuesionerPortalExport;
use App\Http\Controllers\Controller;
use App\Http\Controllers\User\KuesionerPortalController;
use App\Models\KuesionerPortalPeriode;
use App\Models\KuesionerPortalResponse;
use App\Services\KuesionerPortal\Analisis;
use App\Services\KuesionerPortal\Instrumen;
use App\Services\KuesionerPortal\Kelayakan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Pengelolaan Kuesioner Kualitas Layanan Portal: periode, pemantauan respons,
 * eksklusi, analisis IPA-Kano, dan ekspor data untuk Jupyter.
 * Identitas akun tidak ditampilkan — responden hanya dikenali dari kode acak.
 */
class KuesionerPortalAdminController extends Controller
{
    public function index(Kelayakan $kelayakan)
    {
        $periodes = KuesionerPortalPeriode::withCount([
            'responses as jumlah_selesai' => fn ($q) => $q->where('status', KuesionerPortalResponse::STATUS_SELESAI),
            'responses as jumlah_menolak' => fn ($q) => $q->where('status', KuesionerPortalResponse::STATUS_MENOLAK),
            'responses as jumlah_belum_selesai' => fn ($q) => $q->where('status', KuesionerPortalResponse::STATUS_PERSETUJU),
        ])->latest('id')->get();

        $populasi = $kelayakan->rekapPopulasi();
        $labelLayanan = config('kuesioner_portal.profil.layanan');

        return view('admin.kuesioner-portal.index', compact('periodes', 'populasi', 'labelLayanan'));
    }

    public function periodeStore(Request $request)
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'keterangan' => ['nullable', 'string', 'max:2000'],
        ]);

        KuesionerPortalPeriode::create($data);

        return back()->with('success', 'Periode kuesioner dibuat. Buka periode agar responden dapat mengisi.');
    }

    public function periodeBuka(KuesionerPortalPeriode $periode)
    {
        DB::transaction(function () use ($periode) {
            KuesionerPortalPeriode::where('is_active', true)->where('id', '!=', $periode->id)
                ->update(['is_active' => false, 'ditutup_at' => now()]);
            $periode->update(['is_active' => true, 'dibuka_at' => $periode->dibuka_at ?? now(), 'ditutup_at' => null]);
        });

        return back()->with('success', "Periode \"{$periode->nama}\" dibuka.");
    }

    public function periodeTutup(KuesionerPortalPeriode $periode)
    {
        $periode->update(['is_active' => false, 'ditutup_at' => now()]);

        return back()->with('success', "Periode \"{$periode->nama}\" ditutup.");
    }

    public function responses(KuesionerPortalPeriode $periode, Analisis $analisis)
    {
        $responses = $periode->responses()->with(['answers', 'unitKerja'])->latest('submitted_at')->get();
        $eksklusi = $analisis->eksklusi($responses->where('status', KuesionerPortalResponse::STATUS_SELESAI));

        return view('admin.kuesioner-portal.responses', compact('periode', 'responses', 'eksklusi'));
    }

    public function show(KuesionerPortalResponse $response, Analisis $analisis)
    {
        $response->load(['answers', 'unitKerja', 'periode']);
        $eksklusi = $response->status === KuesionerPortalResponse::STATUS_SELESAI
            ? $analisis->eksklusi(collect([$response]))
            : ['semua' => [], 'kano' => []];

        return view('admin.kuesioner-portal.show', [
            'response' => $response,
            'eksklusi' => $eksklusi,
            'atribut' => Instrumen::atribut(),
            'kepuasan' => config('kuesioner_portal.kepuasan'),
            'profil' => config('kuesioner_portal.profil'),
        ]);
    }

    public function eksklusi(Request $request, KuesionerPortalResponse $response)
    {
        $data = $request->validate([
            'dikecualikan' => ['required', 'boolean'],
            'alasan_dikecualikan' => ['nullable', 'required_if:dikecualikan,1', 'string', 'max:255'],
        ]);

        $response->update([
            'dikecualikan' => (bool) $data['dikecualikan'],
            'alasan_dikecualikan' => $data['dikecualikan'] ? $data['alasan_dikecualikan'] : null,
        ]);

        return back()->with('success', $data['dikecualikan']
            ? "Respons {$response->kode_responden} dikecualikan dari analisis."
            : "Respons {$response->kode_responden} disertakan kembali.");
    }

    public function analisis(KuesionerPortalPeriode $periode, Analisis $analisis)
    {
        return view('admin.kuesioner-portal.analisis', [
            'periode' => $periode,
            'hasil' => $analisis->jalankan($periode),
            'profilLabel' => config('kuesioner_portal.profil'),
        ]);
    }

    public function export(KuesionerPortalPeriode $periode)
    {
        return Excel::download(
            new KuesionerPortalExport($periode),
            'kuesioner-portal-' . Str::slug($periode->nama) . '-' . now()->format('Ymd') . '.xlsx'
        );
    }

    public function pratinjau()
    {
        return view('user.kuesioner-portal.isi', KuesionerPortalController::dataFormulir() + [
            'response' => null,
            'pratinjau' => true,
            'unitKerjaDefault' => null,
        ]);
    }
}
