@extends('layouts.authenticated')

@section('title', '- Detail Tugas Vidcon')
@section('header-title', 'Detail Tugas & Upload Dokumentasi')

@section('content')
<div class="container mx-auto px-4">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">{{ $vidconData->judul_kegiatan }}</h1>
        <a href="{{ route('operator.vidcon.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded font-semibold">
            Kembali
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif

    {{-- Detail Kegiatan --}}
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4 pb-2 border-b-2 border-purple-500">Detail Kegiatan</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <p class="mb-3"><strong class="text-gray-700">Instansi:</strong><br>{{ $vidconData->unitKerja->nama ?? '-' }}</p>
                <p class="mb-3"><strong class="text-gray-700">Pemohon:</strong><br>{{ $vidconData->nama_pemohon }}</p>
                <p class="mb-3"><strong class="text-gray-700">Kontak:</strong><br>{{ $vidconData->no_hp }}</p>
                <p class="mb-3"><strong class="text-gray-700">Lokasi:</strong><br>{{ $vidconData->lokasi ?? '-' }}</p>
            </div>
            <div>
                <p class="mb-3">
                    <strong class="text-gray-700">Tanggal:</strong><br>
                    {{ $vidconData->tanggal_mulai ? $vidconData->tanggal_mulai->format('d F Y') : '-' }}
                    @if($vidconData->tanggal_selesai && $vidconData->tanggal_mulai != $vidconData->tanggal_selesai)
                        <br>s/d {{ $vidconData->tanggal_selesai->format('d F Y') }}
                    @endif
                </p>
                <p class="mb-3">
                    <strong class="text-gray-700">Waktu:</strong><br>
                    {{ $vidconData->jam_mulai ? $vidconData->jam_mulai->format('H:i') : '-' }} -
                    {{ $vidconData->jam_selesai ? $vidconData->jam_selesai->format('H:i') : '-' }} WIB
                </p>
                <p class="mb-3"><strong class="text-gray-700">Platform:</strong><br>{{ $vidconData->platform }}</p>
                @if($vidconData->platform === 'Zoom' && $vidconData->akun_zoom)
                    <p class="mb-3">
                        <strong class="text-gray-700">Akun Zoom:</strong><br>
                        <span class="inline-block px-3 py-1 text-sm font-semibold rounded bg-orange-100 text-orange-800">
                            Akun {{ $vidconData->akun_zoom }}
                        </span>
                    </p>
                @endif
            </div>
        </div>

        @if($vidconData->deskripsi_kegiatan)
            <div class="mt-4 pt-4 border-t border-gray-200">
                <strong class="text-gray-700">Deskripsi Kegiatan:</strong>
                <p class="mt-2 text-gray-600">{{ $vidconData->deskripsi_kegiatan }}</p>
            </div>
        @endif

        @if($vidconData->informasi_pimpinan)
            <div class="mt-4 pt-4 border-t border-gray-200">
                <strong class="text-gray-700">Informasi Pimpinan:</strong>
                <p class="mt-2 text-gray-600">{{ $vidconData->informasi_pimpinan }}</p>
            </div>
        @endif

        <div class="mt-4 pt-4 border-t border-gray-200">
            <strong class="text-gray-700">Operator yang Ditugaskan:</strong>
            <div class="mt-2">
                @foreach($vidconData->operators as $op)
                    <span class="inline-block px-3 py-1 text-sm rounded-full bg-blue-100 text-blue-800 mr-2 mb-2">
                        {{ $op->name }}
                    </span>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Upload Dokumentasi Form --}}
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4 pb-2 border-b-2 border-purple-500">Upload Foto Dokumentasi</h2>

        <form action="{{ route('operator.vidcon.documentation.store', $vidconData->id) }}" method="POST" enctype="multipart/form-data" id="uploadForm">
            @csrf

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Foto (Multiple)</label>
                <input type="file" name="photos[]" id="photos" multiple
                       accept="image/*,image/heic,image/heif"
                       capture="environment"
                       class="w-full border-gray-300 rounded-md shadow-sm @error('photos') border-red-500 @enderror"
                       onchange="validateFiles(event)" required>
                @error('photos')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
                <p class="text-sm text-gray-500 mt-1">
                    Max 10MB per foto. Format: JPG, JPEG, PNG, HEIC (iPhone).
                    <span class="text-blue-600 font-medium">Tip: Bisa langsung foto dengan kamera!</span>
                </p>
            </div>

            <div id="preview-container" class="mb-6 grid grid-cols-2 md:grid-cols-4 gap-4"></div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Keterangan Kegiatan</label>
                <textarea name="keterangan" rows="3"
                          class="w-full border-gray-300 rounded-md shadow-sm @error('keterangan') border-red-500 @enderror"
                          placeholder="Catatan atau keterangan tentang pelaksanaan kegiatan...">{{ old('keterangan') }}</textarea>
                @error('keterangan')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded font-semibold inline-flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
                Upload Dokumentasi
            </button>
        </form>
    </div>

    {{-- Dokumentasi yang Sudah Diupload --}}
    @if($vidconData->documentations->count() > 0)
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4 pb-2 border-b-2 border-purple-500">
            Dokumentasi yang Sudah Diupload ({{ $vidconData->documentations->count() }} Foto)
        </h2>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            @foreach($vidconData->documentations as $doc)
            <div class="relative group">
                <img src="{{ $doc->image_url }}"
                     alt="{{ $doc->caption ?? 'Dokumentasi' }}"
                     class="w-full h-48 object-cover rounded-lg shadow-md">

                <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-70 transition-all duration-300 rounded-lg flex items-center justify-center" style="background-color: rgba(0, 0, 0, 0);">
                    <div class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 text-center">
                        <a href="{{ $doc->image_url }}" target="_blank"
                           class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded mr-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </a>
                        <form action="{{ route('operator.vidcon.documentation.delete', $doc->id) }}" method="POST" class="inline-block"
                              onsubmit="return confirm('Yakin ingin menghapus foto ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>

                @if($doc->caption)
                <p class="mt-2 text-sm text-gray-600">{{ $doc->caption }}</p>
                @endif
                <p class="text-xs text-gray-400">Oleh: {{ $doc->uploader->name }} - {{ $doc->created_at->format('d/m/Y H:i') }}</p>
            </div>
            @endforeach
        </div>

        @if($vidconData->documentations->first() && $vidconData->documentations->first()->keterangan)
        <div class="mt-6 pt-4 border-t border-gray-200">
            <strong class="text-gray-700">Keterangan:</strong>
            <p class="mt-2 text-gray-600">{{ $vidconData->documentations->first()->keterangan }}</p>
        </div>
        @endif
    </div>
    @endif
</div>

@push('scripts')
<script>
    function validateFiles(event) {
        const files = event.target.files;
        const maxSize = 10 * 1024 * 1024; // 10MB
        let hasError = false;
        let errorMessages = [];

        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            if (file.size > maxSize) {
                const sizeMB = (file.size / 1024 / 1024).toFixed(2);
                errorMessages.push(`File "${file.name}" terlalu besar (${sizeMB}MB). Maksimal 10MB.`);
                hasError = true;
            }
        }

        if (hasError) {
            alert(errorMessages.join('\n'));
            event.target.value = ''; // Clear input
            document.getElementById('preview-container').innerHTML = '';
        } else {
            previewImages(event);
        }
    }

    function previewImages(event) {
        const container = document.getElementById('preview-container');
        container.innerHTML = '';

        const files = event.target.files;
        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            // Support HEIC files as well
            if (file.type.startsWith('image/') || file.name.toLowerCase().endsWith('.heic') || file.name.toLowerCase().endsWith('.heif')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const div = document.createElement('div');
                    div.className = 'relative';
                    const sizeMB = (file.size / 1024 / 1024).toFixed(2);
                    div.innerHTML = `
                        <img src="${e.target.result}" class="w-full h-32 object-cover rounded-lg shadow-md">
                        <p class="mt-1 text-xs text-gray-600 truncate">${file.name}</p>
                        <p class="text-xs text-gray-500">${sizeMB}MB</p>
                    `;
                    container.appendChild(div);
                };
                reader.readAsDataURL(file);
            }
        }
    }
</script>
@endpush
@endsection
