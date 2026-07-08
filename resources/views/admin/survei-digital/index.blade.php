@extends('layouts.authenticated')

@section('title', '- Manajemen Survei Digital')
@section('header-title', 'Manajemen Survei Digital')

@section('content')
    <div class="max-w-5xl mx-auto">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-blue-700">Manajemen Survei Digital</h1>
            <p class="text-sm text-gray-600 mt-1">
                Kelola token/URL embed survei kepuasan dari portal
                <span class="font-semibold">surveidigital.spbe.go.id</span>. Token dipakai bersama seluruh layanan;
                bila token berganti, cukup perbarui satu kali di sini dan seluruh tombol
                <em>"Beri Penilaian"</em> akan otomatis menggunakan token terbaru.
            </p>
        </div>

        {{-- Alerts --}}
        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif
        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Form pengaturan --}}
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <form action="{{ route('admin.survei-digital.update') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-5">
                    <label for="embed_base_url" class="block text-sm font-semibold text-gray-700 mb-1">
                        Base URL Embed Survei (mengandung token)
                    </label>
                    <textarea id="embed_base_url" name="embed_base_url" rows="4"
                        class="w-full border border-gray-300 rounded px-3 py-2 text-sm font-mono break-all focus:ring-blue-500 focus:border-blue-500"
                        placeholder="https://surveidigital.spbe.go.id/embed/survey/<token>/embed/view/">{{ old('embed_base_url', $setting->embed_base_url) }}</textarea>
                    <p class="text-xs text-gray-500 mt-1">
                        Tempelkan URL embed dari portal SPBE hingga bagian <code>/embed/view/</code>. Bagian
                        <code>?jenis_layanan=...</code> tidak perlu disertakan — sistem menambahkannya otomatis per layanan
                        (jika ada, akan dihapus saat disimpan).
                    </p>
                </div>

                <div class="mb-5">
                    <label class="inline-flex items-center gap-2">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1"
                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                            {{ old('is_active', $setting->is_active) ? 'checked' : '' }}>
                        <span class="text-sm font-medium text-gray-700">Aktifkan survei digital</span>
                    </label>
                    <p class="text-xs text-gray-500 mt-1">
                        Jika dinonaktifkan, tombol "Beri Penilaian" tetap muncul namun halaman survei menampilkan pesan
                        "Survei belum tersedia".
                    </p>
                </div>

                @if ($setting->updated_by && $setting->updatedBy)
                    <p class="text-xs text-gray-400 mb-4">
                        Terakhir diperbarui oleh {{ $setting->updatedBy->name ?? 'Admin' }}
                        {{ $setting->updated_at?->diffForHumans() }}.
                    </p>
                @endif

                <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-5 py-2 rounded">
                    Simpan Pengaturan
                </button>
            </form>
        </div>

        {{-- Pratinjau URL per layanan --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-1">Pratinjau URL per Layanan</h2>
            <p class="text-sm text-gray-500 mb-4">
                URL final yang dipakai tiap layanan (base URL + <code>jenis_layanan</code>). Gunakan untuk memverifikasi
                token sebelum disebarkan ke pengguna.
            </p>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500 border-b">
                            <th class="py-2 pr-4 font-semibold">Layanan</th>
                            <th class="py-2 pr-4 font-semibold">jenis_layanan</th>
                            <th class="py-2 font-semibold">URL Embed</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach ($previews as $slug => $p)
                            <tr>
                                <td class="py-2 pr-4 align-top whitespace-nowrap font-medium text-gray-700">{{ $p['nama'] }}</td>
                                <td class="py-2 pr-4 align-top whitespace-nowrap text-gray-600">{{ $p['jenis_layanan'] }}</td>
                                <td class="py-2 align-top">
                                    @if ($p['url'])
                                        <a href="{{ $p['url'] }}" target="_blank"
                                            class="text-blue-600 hover:underline break-all font-mono text-xs">{{ $p['url'] }}</a>
                                    @else
                                        <span class="text-gray-400 italic">Nonaktif / base URL belum diisi</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
