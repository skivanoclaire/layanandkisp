<?php

namespace App\Http\Requests\Rekomendasi;

use Illuminate\Foundation\Http\FormRequest;

class UploadDokumenRequest extends FormRequest
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
            'file' => 'required|file|mimes:pdf|max:10240', // 10MB max
            'jenis_dokumen' => 'required|in:analisis_kebutuhan,perencanaan,manajemen_risiko',
            'keterangan' => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'file.required' => 'File dokumen wajib diupload',
            'file.mimes' => 'File harus berformat PDF',
            'file.max' => 'Ukuran file maksimal 10MB',
            'jenis_dokumen.required' => 'Jenis dokumen wajib dipilih',
            'jenis_dokumen.in' => 'Jenis dokumen tidak valid',
        ];
    }
}
