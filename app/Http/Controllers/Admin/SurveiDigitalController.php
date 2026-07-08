<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SurveiDigitalSetting;
use App\Services\SurveiDigitalService;
use Illuminate\Http\Request;

class SurveiDigitalController extends Controller
{
    public function index()
    {
        $setting = SurveiDigitalSetting::current();

        // Pratinjau URL embed lengkap per layanan (untuk verifikasi admin)
        $previews = [];
        foreach (SurveiDigitalService::SERVICES as $slug => $service) {
            $previews[$slug] = [
                'nama' => $service['nama'],
                'jenis_layanan' => $service['jenis_layanan'],
                'url' => SurveiDigitalService::urlFor($slug),
            ];
        }

        return view('admin.survei-digital.index', [
            'setting' => $setting,
            'previews' => $previews,
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'embed_base_url' => ['nullable', 'string', 'max:2000', 'url'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'embed_base_url.url' => 'Base URL embed harus berupa URL yang valid.',
        ]);

        $setting = SurveiDigitalSetting::current();

        // Normalisasi: buang query string / fragment agar hanya menyisakan bagian token.
        $base = trim($validated['embed_base_url'] ?? '');
        if ($base !== '') {
            $base = preg_replace('/[?#].*$/', '', $base);
        }

        $setting->embed_base_url = $base !== '' ? $base : null;
        $setting->is_active = (bool) ($validated['is_active'] ?? false);
        $setting->updated_by = auth()->id();
        $setting->save();

        return redirect()
            ->route('admin.survei-digital.index')
            ->with('success', 'Pengaturan survei digital berhasil diperbarui.');
    }
}
