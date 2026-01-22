<?php

namespace App\Http\Requests\Rekomendasi;

use Illuminate\Foundation\Http\FormRequest;

class StoreUsulanV2Request extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $fieldsToDecodeHtml = [
            // Basic information
            'deskripsi',
            'tujuan',
            'manfaat',
            // Integration & special needs
            'detail_integrasi',
            'kebutuhan_khusus',
            'dampak_tidak_dibangun',
            // Analisis Kebutuhan (Permenkomdigi No. 6 Tahun 2025)
            'dasar_hukum',
            'uraian_permasalahan',
            'pihak_terkait',
            'ruang_lingkup',
            'analisis_biaya_manfaat',
            // Perencanaan (Permenkomdigi No. 6 Tahun 2025)
            'uraian_ruang_lingkup',
            'proses_bisnis',
            'kerangka_kerja',
            'peran_tanggung_jawab',
            'jadwal_pelaksanaan',
            'rencana_aksi',
            'keamanan_informasi',
            'sumber_daya_manusia',
            'sumber_daya_anggaran',
            'sumber_daya_sarana',
            'indikator_keberhasilan',
            'alih_pengetahuan',
            'pemantauan_pelaporan',
        ];

        $decodedData = [];
        foreach ($fieldsToDecodeHtml as $field) {
            if ($this->has($field) && !empty($this->input($field))) {
                $decodedData[$field] = html_entity_decode($this->input($field), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            }
        }

        if (!empty($decodedData)) {
            $this->merge($decodedData);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'nama_aplikasi' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'tujuan' => 'required|string',
            'manfaat' => 'required|string',
            'pemilik_proses_bisnis_id' => 'required|exists:unit_kerjas,id',
            'jenis_layanan' => 'required|in:publik,internal',
            'target_pengguna' => 'required|string|max:255',
            'estimasi_pengguna' => 'required|integer|min:1',
            'lingkup_aplikasi' => 'required|in:lokal,regional,nasional',
            'platform' => 'required|array|min:1',
            'platform.*' => 'in:web,mobile,desktop',
            'teknologi_diusulkan' => 'nullable|string|max:500',
            'estimasi_waktu_pengembangan' => 'required|integer|min:1',
            'estimasi_biaya' => 'required|numeric|min:0',
            'sumber_pendanaan' => 'required|in:apbd,apbn,hibah,swasta,lainnya',
            'integrasi_sistem_lain' => 'nullable|in:ya,tidak',
            'detail_integrasi' => 'nullable|string',
            'kebutuhan_khusus' => 'nullable|string',
            'dampak_tidak_dibangun' => 'nullable|string',
            'prioritas' => 'required|in:rendah,sedang,tinggi,sangat_tinggi',

            // Manajemen Risiko SPBE (Permenkomdigi No. 6 Tahun 2025)
            'risiko_items' => 'nullable|array',
            'risiko_items.*.jenis_risiko' => 'nullable|in:positif,negatif',
            'risiko_items.*.kategori_risiko' => 'nullable|string',
            'risiko_items.*.area_dampak' => 'nullable|string',
            'risiko_items.*.uraian_kejadian' => 'nullable|string',
            'risiko_items.*.penyebab' => 'nullable|string',
            'risiko_items.*.dampak' => 'nullable|string',
            'risiko_items.*.level_kemungkinan' => 'nullable|integer|min:1|max:5',
            'risiko_items.*.level_dampak' => 'nullable|integer|min:1|max:5',
            'risiko_items.*.besaran_risiko' => 'nullable|string',
            'risiko_items.*.besaran_risiko_nilai' => 'nullable|integer|min:1|max:25',
            'risiko_items.*.perlu_penanganan' => 'nullable|in:ya,tidak',
            'risiko_items.*.opsi_penanganan' => 'nullable|string',
            'risiko_items.*.rencana_aksi' => 'nullable|string',
            'risiko_items.*.jadwal_implementasi' => 'nullable|string',
            'risiko_items.*.penanggung_jawab' => 'nullable|string',
            'risiko_items.*.risiko_residual' => 'nullable|in:ya,tidak',

            // Permenkomdigi No. 6 Tahun 2025 - Analisis Kebutuhan
            'dasar_hukum' => 'nullable|string',
            'uraian_permasalahan' => 'nullable|string',
            'pihak_terkait' => 'nullable|string',
            'ruang_lingkup' => 'nullable|string',
            'analisis_biaya_manfaat' => 'nullable|string',
            'lokasi_implementasi' => 'nullable|string|max:255',

            // Permenkomdigi No. 6 Tahun 2025 - Perencanaan
            'uraian_ruang_lingkup' => 'nullable|string',
            'proses_bisnis' => 'nullable|string',
            'proses_bisnis_file' => 'nullable|file|mimes:pdf,doc,docx,png,jpg,jpeg,svg,zip,rar,7z,vsdx,vsd,drawio,bpmn|max:10240',
            'kerangka_kerja' => 'nullable|string',
            'pelaksana_pembangunan' => 'nullable|in:menteri,swakelola,pihak_ketiga',
            'peran_tanggung_jawab' => 'nullable|string',
            'jadwal_pelaksanaan' => 'nullable|string',
            'rencana_aksi' => 'nullable|string',
            'keamanan_informasi' => 'nullable|string',
            'sumber_daya_manusia' => 'nullable|string',
            'sumber_daya_anggaran' => 'nullable|string',
            'sumber_daya_sarana' => 'nullable|string',
            'indikator_keberhasilan' => 'nullable|string',
            'alih_pengetahuan' => 'nullable|string',
            'pemantauan_pelaporan' => 'nullable|string',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'nama_aplikasi.required' => 'Nama aplikasi wajib diisi',
            'deskripsi.required' => 'Deskripsi aplikasi wajib diisi',
            'tujuan.required' => 'Tujuan pengembangan wajib diisi',
            'manfaat.required' => 'Manfaat yang diharapkan wajib diisi',
            'pemilik_proses_bisnis_id.required' => 'Pemilik proses bisnis wajib dipilih',
            'pemilik_proses_bisnis_id.exists' => 'Pemilik proses bisnis tidak valid',
            'jenis_layanan.required' => 'Jenis layanan wajib dipilih',
            'jenis_layanan.in' => 'Jenis layanan harus Layanan Publik atau Layanan Internal',
            'target_pengguna.required' => 'Target pengguna wajib diisi',
            'estimasi_pengguna.required' => 'Estimasi jumlah pengguna wajib diisi',
            'estimasi_pengguna.integer' => 'Estimasi jumlah pengguna harus berupa angka',
            'estimasi_pengguna.min' => 'Estimasi jumlah pengguna minimal 1',
            'lingkup_aplikasi.required' => 'Lingkup aplikasi wajib dipilih',
            'lingkup_aplikasi.in' => 'Lingkup aplikasi tidak valid',
            'platform.required' => 'Minimal satu platform harus dipilih',
            'platform.array' => 'Format platform tidak valid',
            'platform.min' => 'Minimal satu platform harus dipilih',
            'platform.*.in' => 'Platform yang dipilih tidak valid',
            'estimasi_waktu_pengembangan.required' => 'Estimasi waktu pengembangan wajib diisi',
            'estimasi_waktu_pengembangan.integer' => 'Estimasi waktu pengembangan harus berupa angka',
            'estimasi_waktu_pengembangan.min' => 'Estimasi waktu pengembangan minimal 1 bulan',
            'estimasi_biaya.required' => 'Estimasi biaya wajib diisi',
            'estimasi_biaya.numeric' => 'Estimasi biaya harus berupa angka',
            'estimasi_biaya.min' => 'Estimasi biaya tidak boleh negatif',
            'sumber_pendanaan.required' => 'Sumber pendanaan wajib dipilih',
            'sumber_pendanaan.in' => 'Sumber pendanaan tidak valid',
            'integrasi_sistem_lain.in' => 'Pilihan integrasi tidak valid',
            'prioritas.required' => 'Prioritas wajib dipilih',
            'prioritas.in' => 'Prioritas tidak valid',
        ];
    }
}
