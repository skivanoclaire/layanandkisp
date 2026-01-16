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
            'jenis_layanan' => 'required|in:internal,eksternal,hybrid',
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
            'risiko_items' => 'nullable|array',
            'risiko_items.*.jenis' => 'nullable|string',
            'risiko_items.*.tingkat' => 'nullable|in:rendah,sedang,tinggi',
            'risiko_items.*.mitigasi' => 'nullable|string',

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
            'jenis_layanan.in' => 'Jenis layanan tidak valid',
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
