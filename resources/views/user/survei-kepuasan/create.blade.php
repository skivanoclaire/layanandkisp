@extends('layouts.authenticated')

@section('title', '- Isi Survei Kepuasan Layanan')
@section('header-title', 'Isi Survei Kepuasan Layanan')

@section('content')
    <div class="container mx-auto px-4 py-6 max-w-4xl">
        {{-- Header --}}
        <div class="mb-6">
            <h1 class="text-2xl font-semibold text-gray-900">Survei Kepuasan Layanan Digital</h1>
            <p class="text-sm text-gray-600 mt-1">Berikan penilaian Anda untuk membantu kami meningkatkan kualitas layanan digital di lingkungan Pemprov Kaltara</p>
            <div class="mt-3 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                <p class="text-sm text-blue-800">
                    <svg class="w-5 h-5 inline mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    <strong>Info:</strong> Anda dapat memberikan penilaian terhadap semua subdomain/layanan yang tersedia, tidak harus milik Anda.
                </p>
            </div>
        </div>

        <form method="POST" action="{{ route('survei-kepuasan.store') }}" class="space-y-6">
            @csrf

            {{-- Subdomain Selection --}}
            <div class="bg-white rounded-lg shadow-sm p-6">
                <label for="web_monitor_id" class="block text-sm font-medium text-gray-700 mb-2">
                    Pilih Subdomain/Layanan <span class="text-red-600">*</span>
                </label>
                <select name="web_monitor_id" id="web_monitor_id" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 @error('web_monitor_id') border-red-500 @enderror">
                    <option value="">-- Pilih Subdomain --</option>
                    @foreach ($webMonitors as $monitor)
                        <option value="{{ $monitor->id }}" {{ old('web_monitor_id') == $monitor->id ? 'selected' : '' }}>
                            {{ $monitor->subdomain }}.kaltaraprov.go.id
                            @if($monitor->nama_aplikasi) - {{ $monitor->nama_aplikasi }} @endif
                            @if($monitor->nama_instansi) ({{ $monitor->nama_instansi }}) @endif
                        </option>
                    @endforeach
                </select>
                @error('web_monitor_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">Pilih subdomain/layanan yang pernah atau sering Anda gunakan</p>
            </div>

            {{-- Rating Questions --}}
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Penilaian Layanan</h2>
                <p class="text-sm text-gray-600 mb-6">Berikan rating dari 1 (Buruk) sampai 5 (Sangat Baik)</p>

                <div class="space-y-6">
                    {{-- Kecepatan --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3">
                            1. Kecepatan Akses Layanan <span class="text-red-600">*</span>
                        </label>
                        <div class="flex items-center space-x-4">
                            @for ($i = 1; $i <= 5; $i++)
                                <label class="flex items-center cursor-pointer">
                                    <input type="radio" name="rating_kecepatan" value="{{ $i }}" required
                                           class="w-4 h-4 text-green-600 focus:ring-green-500 @error('rating_kecepatan') border-red-500 @enderror"
                                           {{ old('rating_kecepatan') == $i ? 'checked' : '' }}>
                                    <span class="ml-2 text-sm text-gray-700">{{ $i }}</span>
                                </label>
                            @endfor
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Seberapa cepat layanan dapat diakses</p>
                        @error('rating_kecepatan')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Kemudahan --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3">
                            2. Kemudahan Penggunaan <span class="text-red-600">*</span>
                        </label>
                        <div class="flex items-center space-x-4">
                            @for ($i = 1; $i <= 5; $i++)
                                <label class="flex items-center cursor-pointer">
                                    <input type="radio" name="rating_kemudahan" value="{{ $i }}" required
                                           class="w-4 h-4 text-green-600 focus:ring-green-500"
                                           {{ old('rating_kemudahan') == $i ? 'checked' : '' }}>
                                    <span class="ml-2 text-sm text-gray-700">{{ $i }}</span>
                                </label>
                            @endfor
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Seberapa mudah menggunakan layanan ini</p>
                        @error('rating_kemudahan')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Kualitas --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3">
                            3. Kualitas Layanan <span class="text-red-600">*</span>
                        </label>
                        <div class="flex items-center space-x-4">
                            @for ($i = 1; $i <= 5; $i++)
                                <label class="flex items-center cursor-pointer">
                                    <input type="radio" name="rating_kualitas" value="{{ $i }}" required
                                           class="w-4 h-4 text-green-600 focus:ring-green-500"
                                           {{ old('rating_kualitas') == $i ? 'checked' : '' }}>
                                    <span class="ml-2 text-sm text-gray-700">{{ $i }}</span>
                                </label>
                            @endfor
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Kualitas fitur dan fungsi yang disediakan</p>
                        @error('rating_kualitas')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Responsif --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3">
                            4. Responsivitas Layanan <span class="text-red-600">*</span>
                        </label>
                        <div class="flex items-center space-x-4">
                            @for ($i = 1; $i <= 5; $i++)
                                <label class="flex items-center cursor-pointer">
                                    <input type="radio" name="rating_responsif" value="{{ $i }}" required
                                           class="w-4 h-4 text-green-600 focus:ring-green-500"
                                           {{ old('rating_responsif') == $i ? 'checked' : '' }}>
                                    <span class="ml-2 text-sm text-gray-700">{{ $i }}</span>
                                </label>
                            @endfor
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Kecepatan respon sistem terhadap input pengguna</p>
                        @error('rating_responsif')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Keamanan --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3">
                            5. Keamanan Layanan <span class="text-red-600">*</span>
                        </label>
                        <div class="flex items-center space-x-4">
                            @for ($i = 1; $i <= 5; $i++)
                                <label class="flex items-center cursor-pointer">
                                    <input type="radio" name="rating_keamanan" value="{{ $i }}" required
                                           class="w-4 h-4 text-green-600 focus:ring-green-500"
                                           {{ old('rating_keamanan') == $i ? 'checked' : '' }}>
                                    <span class="ml-2 text-sm text-gray-700">{{ $i }}</span>
                                </label>
                            @endfor
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Tingkat keamanan dan privasi data</p>
                        @error('rating_keamanan')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Keseluruhan --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3">
                            6. Kepuasan Keseluruhan <span class="text-red-600">*</span>
                        </label>
                        <div class="flex items-center space-x-4">
                            @for ($i = 1; $i <= 5; $i++)
                                <label class="flex items-center cursor-pointer">
                                    <input type="radio" name="rating_keseluruhan" value="{{ $i }}" required
                                           class="w-4 h-4 text-green-600 focus:ring-green-500"
                                           {{ old('rating_keseluruhan') == $i ? 'checked' : '' }}>
                                    <span class="ml-2 text-sm text-gray-700">{{ $i }}</span>
                                </label>
                            @endfor
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Penilaian secara keseluruhan terhadap layanan</p>
                        @error('rating_keseluruhan')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Feedback --}}
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Masukan dan Saran (Opsional)</h2>

                <div class="space-y-4">
                    {{-- Kelebihan --}}
                    <div>
                        <label for="kelebihan" class="block text-sm font-medium text-gray-700 mb-2">
                            Kelebihan Layanan
                        </label>
                        <textarea name="kelebihan" id="kelebihan" rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                  placeholder="Apa yang Anda sukai dari layanan ini?">{{ old('kelebihan') }}</textarea>
                        @error('kelebihan')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Kekurangan --}}
                    <div>
                        <label for="kekurangan" class="block text-sm font-medium text-gray-700 mb-2">
                            Kekurangan Layanan
                        </label>
                        <textarea name="kekurangan" id="kekurangan" rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                  placeholder="Apa yang perlu diperbaiki?">{{ old('kekurangan') }}</textarea>
                        @error('kekurangan')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Saran --}}
                    <div>
                        <label for="saran" class="block text-sm font-medium text-gray-700 mb-2">
                            Saran Perbaikan
                        </label>
                        <textarea name="saran" id="saran" rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                  placeholder="Saran untuk meningkatkan layanan">{{ old('saran') }}</textarea>
                        @error('saran')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex justify-end space-x-3">
                <a href="{{ route('survei-kepuasan.index') }}"
                   class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium transition">
                    Batal
                </a>
                <button type="submit"
                        class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition">
                    Kirim Survei
                </button>
            </div>
        </form>
    </div>
@endsection
