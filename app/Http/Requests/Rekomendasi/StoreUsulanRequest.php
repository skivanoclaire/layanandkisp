<?php

namespace App\Http\Requests\Rekomendasi;

use Illuminate\Foundation\Http\FormRequest;

class StoreUsulanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // Basic Information
            'judul_aplikasi' => 'required|string|max:255',
            'dasar_hukum' => 'required|string',
            'permasalahan_kebutuhan' => 'required|string',
            'pihak_terkait' => 'required|string',
            'pemilik_proses_bisnis_id' => 'nullable|exists:unit_kerjas,id',
            'stakeholder_internal' => 'nullable|array',
            'stakeholder_eksternal' => 'nullable|string',
            'maksud_tujuan' => 'required|string',
            'ruang_lingkup' => 'required|string',
            'analisis_biaya_manfaat' => 'required|string',
            'analisis_risiko' => 'required|string',
            'target_waktu' => 'required|string',
            'sasaran_pengguna' => 'required|string',
            'lokasi_implementasi' => 'required|string',

            // Planning
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

            // Risk Management (handled separately in RekomendasiRisikoItem)
            'risiko_items' => 'nullable|array',
            'risiko_items.*.jenis_risiko' => 'required|string',
            'risiko_items.*.uraian_risiko' => 'required|string',
            'risiko_items.*.penyebab' => 'nullable|string',
            'risiko_items.*.dampak' => 'nullable|string',
            'risiko_items.*.level_kemungkinan' => 'nullable|string',
            'risiko_items.*.level_dampak' => 'nullable|string',
            'risiko_items.*.besaran_risiko' => 'nullable|string',
            'risiko_items.*.perlu_penanganan' => 'nullable|boolean',
            'risiko_items.*.opsi_penanganan' => 'nullable|string',
            'risiko_items.*.rencana_aksi' => 'nullable|string',
            'risiko_items.*.jadwal_implementasi' => 'nullable|string',
            'risiko_items.*.penanggung_jawab' => 'nullable|string',
            'risiko_items.*.risiko_residual' => 'nullable|string',
            'risiko_items.*.kategori_risiko_spbe' => 'nullable|string',
            'risiko_items.*.area_dampak_risiko_spbe' => 'nullable|string',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'judul_aplikasi.required' => 'Judul aplikasi wajib diisi',
            'dasar_hukum.required' => 'Dasar hukum wajib diisi',
            'permasalahan_kebutuhan.required' => 'Uraian permasalahan wajib diisi',
            'pihak_terkait.required' => 'Pihak terkait wajib diisi',
            'maksud_tujuan.required' => 'Maksud dan tujuan wajib diisi',
            'ruang_lingkup.required' => 'Ruang lingkup wajib diisi',
            'analisis_biaya_manfaat.required' => 'Analisis biaya dan manfaat wajib diisi',
            'analisis_risiko.required' => 'Analisis risiko wajib diisi',
            'target_waktu.required' => 'Target waktu kesiapan wajib diisi',
            'sasaran_pengguna.required' => 'Sasaran pengguna wajib diisi',
            'lokasi_implementasi.required' => 'Lokasi implementasi wajib diisi',
            'perencanaan_ruang_lingkup.required' => 'Perencanaan ruang lingkup wajib diisi',
            'perencanaan_proses_bisnis.required' => 'Proses bisnis wajib diisi',
            'kerangka_kerja.required' => 'Kerangka kerja wajib diisi',
            'pelaksana_pembangunan.required' => 'Pemilihan pelaksana wajib diisi',
            'peran_tanggung_jawab.required' => 'Peran dan tanggung jawab wajib diisi',
            'jadwal_pelaksanaan.required' => 'Jadwal pelaksanaan wajib diisi',
            'rencana_aksi.required' => 'Rencana aksi wajib diisi',
            'keamanan_informasi.required' => 'Persyaratan keamanan informasi wajib diisi',
            'sumber_daya.required' => 'Sumber daya wajib diisi',
            'indikator_keberhasilan.required' => 'Indikator keberhasilan wajib diisi',
            'alih_pengetahuan.required' => 'Mekanisme alih pengetahuan wajib diisi',
            'pemantauan_pelaporan.required' => 'Mekanisme pemantauan dan pelaporan wajib diisi',
        ];
    }
}
