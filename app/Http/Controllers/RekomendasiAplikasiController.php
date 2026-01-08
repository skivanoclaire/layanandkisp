<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RekomendasiAplikasiForm;
use App\Models\RekomendasiRisikoItem;
use App\Models\UnitKerja;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Dompdf\Dompdf;
use Dompdf\Options;

class RekomendasiAplikasiController extends Controller
{
    public function index()
    {
        $forms = RekomendasiAplikasiForm::where('user_id', auth()->id())->latest()->get();
        return view('user.rekomendasi.index', compact('forms'));
    }

    public function create()
    {
        $unitKerjas = UnitKerja::active()->orderBy('nama')->get();
        return view('user.rekomendasi.create', compact('unitKerjas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'judul_aplikasi' => 'required|string|max:255',
            'dasar_hukum' => 'required|string',
            'permasalahan_kebutuhan' => 'required|string',
            'pemilik_proses_bisnis_id' => 'required|exists:unit_kerjas,id',
            'stakeholder_internal' => 'required|array|min:1',
            'stakeholder_eksternal' => 'required|string',
            'maksud_tujuan' => 'required|string',
            'ruang_lingkup' => 'required|string',
            'analisis_biaya_manfaat' => 'required|string',
            'analisis_risiko' => 'required|string',
            'target_waktu' => 'required|string',
            'sasaran_pengguna' => 'required|string',
            'lokasi_implementasi' => 'required|string',
            'perencanaan_ruang_lingkup' => 'required|string',
            'perencanaan_proses_bisnis' => 'required|string',
            'kerangka_kerja' => 'required|string',
            'pelaksana_pembangunan' => 'required|string',
            'peran_tanggung_jawab' => 'required|string',
            'jadwal_pelaksanaan' => 'required|string',
            'rencana_aksi' => 'required|string',
            'keamanan_informasi' => 'required|string',
            'sumber_daya' => 'required|string',
            'indikator_keberhasilan' => 'required|string',
            'alih_pengetahuan' => 'required|string',
            'pemantauan_pelaporan' => 'required|string',

            'risiko' => 'required|array|min:1',
            'risiko.*.jenis_risiko' => 'required|string',
            'risiko.*.uraian_risiko' => 'nullable|string',
        ]);

        // Simpan form utama
        $form = RekomendasiAplikasiForm::create([
            'user_id' => Auth::id(),
            'status' => 'diajukan',
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
            'pemilik_proses_bisnis_id' => $request->pemilik_proses_bisnis_id,
            'stakeholder_internal' => $request->stakeholder_internal,
            'stakeholder_eksternal' => $request->stakeholder_eksternal,
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

        // Only allow editing if status is 'draft' or 'perlu_revisi'
        if (!in_array($form->status, ['draft', 'perlu_revisi'])) {
            return redirect()->route('user.rekomendasi.aplikasi.show', $id)
                           ->with('error', 'Usulan tidak dapat diedit. Status saat ini: ' . $form->status);
        }

        $unitKerjas = UnitKerja::active()->orderBy('nama')->get();
        return view('user.rekomendasi.edit', compact('form', 'unitKerjas'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'judul_aplikasi' => 'required|string|max:255',
            'dasar_hukum' => 'required|string',
            'permasalahan_kebutuhan' => 'required|string',
            'pemilik_proses_bisnis_id' => 'required|exists:unit_kerjas,id',
            'stakeholder_internal' => 'required|array|min:1',
            'stakeholder_eksternal' => 'required|string',
            'maksud_tujuan' => 'required|string',
            'ruang_lingkup' => 'required|string',
            'analisis_biaya_manfaat' => 'required|string',
            'analisis_risiko' => 'required|string',
            'target_waktu' => 'required|string',
            'sasaran_pengguna' => 'required|string',
            'lokasi_implementasi' => 'required|string',
            'perencanaan_ruang_lingkup' => 'required|string',
            'perencanaan_proses_bisnis' => 'required|string',
            'kerangka_kerja' => 'required|string',
            'pelaksana_pembangunan' => 'required|string',
            'peran_tanggung_jawab' => 'required|string',
            'jadwal_pelaksanaan' => 'required|string',
            'rencana_aksi' => 'required|string',
            'keamanan_informasi' => 'required|string',
            'sumber_daya' => 'required|string',
            'indikator_keberhasilan' => 'required|string',
            'alih_pengetahuan' => 'required|string',
            'pemantauan_pelaporan' => 'required|string',

            'risiko' => 'required|array|min:1',
            'risiko.*.jenis_risiko' => 'required|string',
            'risiko.*.uraian_risiko' => 'nullable|string',
        ]);

        $form = RekomendasiAplikasiForm::where('user_id', Auth::id())->findOrFail($id);

        // Check if editing is allowed
        if (!in_array($form->status, ['draft', 'perlu_revisi'])) {
            return redirect()->route('user.rekomendasi.aplikasi.show', $id)
                           ->with('error', 'Usulan tidak dapat diedit. Status saat ini: ' . $form->status);
        }

        // Store original status to check if it was 'perlu_revisi'
        $wasRevisionRequested = $form->status === 'perlu_revisi';

        // Gunakan database transaction untuk memastikan konsistensi data
        DB::transaction(function () use ($request, $form, $wasRevisionRequested) {
            // Update data form utama
            $updateData = [
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
            ];

            // If this was a revision request, change status back to 'diajukan' (resubmit)
            if ($wasRevisionRequested) {
                $updateData['status'] = 'diajukan';
            }

            $form->update($updateData);

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

    /**
     * Download PDF for approved recommendation (User)
     */
    public function downloadPDF($id)
    {
        $form = RekomendasiAplikasiForm::with(['user', 'risikoItems', 'approvedBy', 'pemilikProsesBisnis'])
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        if ($form->status !== 'disetujui') {
            return back()->with('error', 'Hanya usulan yang sudah disetujui yang dapat diunduh.');
        }

        // Ensure verification code exists
        if (!$form->verification_code) {
            $verificationCode = 'VRF-' . strtoupper(Str::random(32));
            $form->update(['verification_code' => $verificationCode]);
        }

        // Generate QR Code
        $verificationUrl = url('/verify/' . $form->verification_code);
        $qrCode = base64_encode(QrCode::format('png')->size(200)->generate($verificationUrl));

        // Encode logo to base64
        $logoPath = public_path('logokaltarafix.png');
        $logoBase64 = '';
        if (file_exists($logoPath)) {
            $logoBase64 = base64_encode(file_get_contents($logoPath));
        }

        // Render the view to HTML
        $html = view('admin.rekomendasi.pdf', [
            'form' => $form,
            'letterNumber' => $form->letter_number,
            'date' => $form->approved_at->format('d F Y'),
            'qrCode' => $qrCode,
            'logoBase64' => $logoBase64,
            'nipKepalaDinas' => '197312311993021001',
            'signerName' => 'Dr. H. ISKANDAR S.IP, M.Si',
            'signerRank' => 'Pembina Utama Muda / IV c',
        ])->render();

        // Configure Dompdf
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Download PDF
        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="Surat_Rekomendasi_' . $form->ticket_number . '.pdf"',
        ]);
    }
}