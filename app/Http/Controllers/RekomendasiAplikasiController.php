<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RekomendasiAplikasiForm;
use App\Models\RekomendasiRisikoItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RekomendasiAplikasiController extends Controller
{
    public function index()
    {
        $forms = RekomendasiAplikasiForm::where('user_id', auth()->id())->latest()->get();
        return view('user.rekomendasi.index', compact('forms'));
    }

    public function create()
    {
        return view('user.rekomendasi.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'judul_aplikasi' => 'required|string|max:255',
            'dasar_hukum' => 'required|string',
            'maksud_tujuan' => 'required|string',
            'ruang_lingkup' => 'required|string',
            'target_waktu' => 'required|string',
            'sasaran_pengguna' => 'required|string',
            'lokasi_implementasi' => 'required|string',

            'risiko' => 'required|array|min:1',
            'risiko.*.jenis_risiko' => 'required|string',
            'risiko.*.uraian_risiko' => 'nullable|string',
        ]);

        // Simpan form utama
        $form = RekomendasiAplikasiForm::create([
            'user_id' => Auth::id(),
            'judul_aplikasi' => $request->judul_aplikasi,
            'dasar_hukum' => $request->dasar_hukum,
            'permasalahan_kebutuhan' => $request->permasalahan_kebutuhan,
            'pihak_terkait' => $request->pihak_terkait,
            'maksud_tujuan' => $request->maksud_tujuan,
            'ruang_lingkup' => $request->ruang_lingkup,
            'analisis_biaya_manfaat' => $request->analisis_biaya_manfaat,
            'analisis_risiko' => $request->analisis_risiko,
            'target_waktu' => $request->target_waktu,
            'sasaran_pengguna' => $request->sasaran_pengguna,
            'lokasi_implementasi' => $request->lokasi_implementasi,
            'perencanaan_ruang_lingkup' => $request->perencanaan_ruang_lingkup,
            'perencanaan_proses_bisnis' => $request->perencanaan_proses_bisnis,
            'kerangka_kerja' => $request->kerangka_kerja,
            'pelaksana_pembangunan' => $request->pelaksana_pembangunan,
            'peran_tanggung_jawab' => $request->peran_tanggung_jawab,
            'jadwal_pelaksanaan' => $request->jadwal_pelaksanaan,
            'rencana_aksi' => $request->rencana_aksi,
            'keamanan_informasi' => $request->keamanan_informasi,
            'sumber_daya' => $request->sumber_daya,
            'indikator_keberhasilan' => $request->indikator_keberhasilan,
            'alih_pengetahuan' => $request->alih_pengetahuan,
            'pemantauan_pelaporan' => $request->pemantauan_pelaporan,
        ]);

        // Simpan risiko
        foreach ($request->risiko as $item) {
            $form->risikoItems()->create([
                'jenis_risiko' => $item['jenis_risiko'],
                'uraian_risiko' => $item['uraian_risiko'] ?? null,
                'penyebab' => $item['penyebab'] ?? null,
                'dampak' => $item['dampak'] ?? null,
                'level_kemungkinan' => $item['level_kemungkinan'] ?? null,
                'level_dampak' => $item['level_dampak'] ?? null,
                'besaran_risiko' => $item['besaran_risiko'] ?? null,
                'perlu_penanganan' => isset($item['perlu_penanganan']) ? (bool)$item['perlu_penanganan'] : true,
                'opsi_penanganan' => $item['opsi_penanganan'] ?? null,
                'rencana_aksi' => $item['rencana_aksi'] ?? null,
                'jadwal_implementasi' => $item['jadwal_implementasi'] ?? null,
                'penanggung_jawab' => $item['penanggung_jawab'] ?? null,
                'risiko_residual' => isset($item['risiko_residual']) ? (bool)$item['risiko_residual'] : false,
            ]);
        }

        return redirect()->route('user.rekomendasi.aplikasi.index')
                         ->with('success', 'Permohonan rekomendasi berhasil dikirim.');
    }

    public function show($id)
    {
        $form = RekomendasiAplikasiForm::with('risikoItems')
                ->where('user_id', Auth::id())
                ->findOrFail($id);

        return view('user.rekomendasi.show', compact('form'));
    }

    public function edit($id)
    {
        $form = RekomendasiAplikasiForm::with('risikoItems')
                ->where('user_id', Auth::id())
                ->findOrFail($id);
        
        return view('user.rekomendasi.edit', compact('form'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'judul_aplikasi' => 'required|string|max:255',
            'dasar_hukum' => 'required|string',
            'maksud_tujuan' => 'required|string',
            'ruang_lingkup' => 'required|string',
            'target_waktu' => 'required|string',
            'sasaran_pengguna' => 'required|string',
            'lokasi_implementasi' => 'required|string',

            'risiko' => 'required|array|min:1',
            'risiko.*.jenis_risiko' => 'required|string',
            'risiko.*.uraian_risiko' => 'nullable|string',
        ]);

        $form = RekomendasiAplikasiForm::where('user_id', Auth::id())->findOrFail($id);

        // Gunakan database transaction untuk memastikan konsistensi data
        DB::transaction(function () use ($request, $form) {
            // Update data form utama
            $form->update([
                'judul_aplikasi' => $request->judul_aplikasi,
                'dasar_hukum' => $request->dasar_hukum,
                'permasalahan_kebutuhan' => $request->permasalahan_kebutuhan,
                'pihak_terkait' => $request->pihak_terkait,
                'maksud_tujuan' => $request->maksud_tujuan,
                'ruang_lingkup' => $request->ruang_lingkup,
                'analisis_biaya_manfaat' => $request->analisis_biaya_manfaat,
                'analisis_risiko' => $request->analisis_risiko,
                'target_waktu' => $request->target_waktu,
                'sasaran_pengguna' => $request->sasaran_pengguna,
                'lokasi_implementasi' => $request->lokasi_implementasi,
                'perencanaan_ruang_lingkup' => $request->perencanaan_ruang_lingkup,
                'perencanaan_proses_bisnis' => $request->perencanaan_proses_bisnis,
                'kerangka_kerja' => $request->kerangka_kerja,
                'pelaksana_pembangunan' => $request->pelaksana_pembangunan,
                'peran_tanggung_jawab' => $request->peran_tanggung_jawab,
                'jadwal_pelaksanaan' => $request->jadwal_pelaksanaan,
                'rencana_aksi' => $request->rencana_aksi,
                'keamanan_informasi' => $request->keamanan_informasi,
                'sumber_daya' => $request->sumber_daya,
                'indikator_keberhasilan' => $request->indikator_keberhasilan,
                'alih_pengetahuan' => $request->alih_pengetahuan,
                'pemantauan_pelaporan' => $request->pemantauan_pelaporan,
            ]);

            // Handle penghapusan risiko yang ditandai untuk dihapus
            if ($request->has('deleted_risiko')) {
                $deletedIds = $request->deleted_risiko;
                RekomendasiRisikoItem::whereIn('id', $deletedIds)
                    ->where('rekomendasi_aplikasi_form_id', $form->id)
                    ->delete();
            }

            // Update atau create risiko items
            $existingRisikoIds = [];
            
            foreach ($request->risiko as $risikoData) {
                $risikoItem = [
                    'jenis_risiko' => $risikoData['jenis_risiko'],
                    'uraian_risiko' => $risikoData['uraian_risiko'] ?? null,
                    'penyebab' => $risikoData['penyebab'] ?? null,
                    'dampak' => $risikoData['dampak'] ?? null,
                    'level_kemungkinan' => $risikoData['level_kemungkinan'] ?? null,
                    'level_dampak' => $risikoData['level_dampak'] ?? null,
                    'besaran_risiko' => $risikoData['besaran_risiko'] ?? null,
                    'perlu_penanganan' => isset($risikoData['perlu_penanganan']) ? (bool)$risikoData['perlu_penanganan'] : true,
                    'opsi_penanganan' => $risikoData['opsi_penanganan'] ?? null,
                    'rencana_aksi' => $risikoData['rencana_aksi'] ?? null,
                    'jadwal_implementasi' => $risikoData['jadwal_implementasi'] ?? null,
                    'penanggung_jawab' => $risikoData['penanggung_jawab'] ?? null,
                    'risiko_residual' => isset($risikoData['risiko_residual']) ? (bool)$risikoData['risiko_residual'] : false,
                ];

                // Jika ada ID dan tidak kosong, berarti update existing risiko
                if (!empty($risikoData['id'])) {
                    $existingRisiko = RekomendasiRisikoItem::where('id', $risikoData['id'])
                        ->where('rekomendasi_aplikasi_form_id', $form->id)
                        ->first();
                    
                    if ($existingRisiko) {
                        $existingRisiko->update($risikoItem);
                        $existingRisikoIds[] = $existingRisiko->id;
                    }
                } else {
                    // Jika tidak ada ID, berarti risiko baru
                    $newRisiko = $form->risikoItems()->create($risikoItem);
                    $existingRisikoIds[] = $newRisiko->id;
                }
            }

            // Hapus risiko yang tidak ada lagi dalam form (orphaned records)
            // Ini untuk handle kasus dimana risiko dihapus tapi tidak masuk ke deleted_risiko[]
            $form->risikoItems()
                 ->whereNotIn('id', $existingRisikoIds)
                 ->delete();
        });

        return redirect()->route('user.rekomendasi.aplikasi.index')
                         ->with('success', 'Form berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $form = RekomendasiAplikasiForm::where('user_id', Auth::id())->findOrFail($id);
        $form->delete(); // Akan menghapus semua risiko juga karena relasi onDelete cascade

        return redirect()->route('user.rekomendasi.aplikasi.index')
                         ->with('success', 'Form berhasil dihapus.');
    }
}