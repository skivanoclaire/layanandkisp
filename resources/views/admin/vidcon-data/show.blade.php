@extends('layouts.authenticated')

@section('title', '- Detail Data Vidcon')
@section('header-title', 'Detail Data Vidcon')

@section('content')
<div class="container mx-auto px-4">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Detail Data Fasilitasi Video Konferensi</h1>
        <div class="flex gap-2">
            <a href="{{ route('admin.vidcon-data.edit', $vidconData) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded font-semibold">
                Edit
            </a>
            <a href="{{ route('admin.vidcon-data.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded font-semibold">
                Kembali
            </a>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        {{-- SECTION: INFORMASI PEMOHON --}}
        <div class="mb-8 pb-6 border-b">
            <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                </svg>
                Informasi Pemohon
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Nama Pemohon</label>
                    <p class="text-gray-900 font-medium">{{ $vidconData->nama_pemohon ?? '-' }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">NIP Pemohon</label>
                    <p class="text-gray-900 font-medium">{{ $vidconData->nip_pemohon ?? '-' }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Email Pemohon</label>
                    <p class="text-gray-900 font-medium">{{ $vidconData->email_pemohon ?? '-' }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">No HP</label>
                    <p class="text-gray-900 font-medium">{{ $vidconData->no_hp ?? '-' }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Instansi</label>
                    <p class="text-gray-900 font-medium">{{ $vidconData->unitKerja->nama ?? $vidconData->nama_instansi ?? '-' }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Nomor Surat</label>
                    <p class="text-gray-900 font-medium">{{ $vidconData->nomor_surat ?? '-' }}</p>
                </div>
            </div>
        </div>

        {{-- SECTION: DETAIL KEGIATAN --}}
        <div class="mb-8 pb-6 border-b">
            <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                    <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
                </svg>
                Detail Kegiatan
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-500 mb-1">Nomor Kegiatan</label>
                    <p class="text-gray-900 font-medium">{{ $vidconData->no ?? '-' }}</p>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-500 mb-1">Judul Kegiatan</label>
                    <p class="text-gray-900 font-medium">{{ $vidconData->judul_kegiatan ?? '-' }}</p>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-500 mb-1">Deskripsi Kegiatan</label>
                    <p class="text-gray-900 whitespace-pre-line">{{ $vidconData->deskripsi_kegiatan ?? '-' }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Lokasi</label>
                    <p class="text-gray-900 font-medium">{{ $vidconData->lokasi ?? '-' }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Jumlah Peserta</label>
                    <p class="text-gray-900 font-medium">{{ $vidconData->jumlah_peserta ? number_format($vidconData->jumlah_peserta) . ' orang' : '-' }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Tanggal Mulai</label>
                    <p class="text-gray-900 font-medium">{{ $vidconData->tanggal_mulai ? $vidconData->tanggal_mulai->format('d F Y') : '-' }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Tanggal Selesai</label>
                    <p class="text-gray-900 font-medium">{{ $vidconData->tanggal_selesai ? $vidconData->tanggal_selesai->format('d F Y') : '-' }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Jam Mulai</label>
                    <p class="text-gray-900 font-medium">{{ $vidconData->jam_mulai ? $vidconData->jam_mulai->format('H:i') : '-' }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Jam Selesai</label>
                    <p class="text-gray-900 font-medium">{{ $vidconData->jam_selesai ? $vidconData->jam_selesai->format('H:i') : '-' }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Platform</label>
                    <p class="text-gray-900 font-medium">
                        {{ $vidconData->platform ?? '-' }}
                        @if($vidconData->platform_lainnya)
                            <span class="text-sm text-gray-500">({{ $vidconData->platform_lainnya }})</span>
                        @endif
                    </p>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-500 mb-1">Keperluan Khusus</label>
                    <p class="text-gray-900 whitespace-pre-line">{{ $vidconData->keperluan_khusus ?? '-' }}</p>
                </div>
            </div>
        </div>

        {{-- SECTION: INFORMASI MEETING & OPERATOR --}}
        <div class="mb-8 pb-6 border-b">
            <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M2 5a2 2 0 012-2h7a2 2 0 012 2v4a2 2 0 01-2 2H9l-3 3v-3H4a2 2 0 01-2-2V5z"/>
                    <path d="M15 7v2a4 4 0 01-4 4H9.828l-1.766 1.767c.28.149.599.233.938.233h2l3 3v-3h2a2 2 0 002-2V9a2 2 0 00-2-2h-1z"/>
                </svg>
                Informasi Meeting & Operator
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Link Meeting</label>
                    @if($vidconData->link_meeting)
                        <a href="{{ $vidconData->link_meeting }}" target="_blank" class="text-blue-600 hover:text-blue-800 font-medium break-all">
                            {{ $vidconData->link_meeting }}
                        </a>
                    @else
                        <p class="text-gray-900">-</p>
                    @endif
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Meeting ID</label>
                    <p class="text-gray-900 font-medium font-mono">{{ $vidconData->meeting_id ?? '-' }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Meeting Password</label>
                    <p class="text-gray-900 font-medium font-mono">{{ $vidconData->meeting_password ?? '-' }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Akun Zoom</label>
                    <p class="text-gray-900 font-medium">{{ $vidconData->akun_zoom ?? '-' }}</p>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-500 mb-1">Operator</label>
                    @if($vidconData->operators && $vidconData->operators->count() > 0)
                        <div class="flex flex-wrap gap-2 mt-2">
                            @foreach($vidconData->operators as $operator)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $operator->name }}
                                </span>
                            @endforeach
                        </div>
                    @elseif($vidconData->operator)
                        <p class="text-gray-900 font-medium">{{ $vidconData->operator }}</p>
                    @else
                        <p class="text-gray-900">-</p>
                    @endif
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-500 mb-1">Informasi Tambahan</label>
                    <p class="text-gray-900 whitespace-pre-line">{{ $vidconData->informasi_tambahan ?? '-' }}</p>
                </div>
            </div>
        </div>

        {{-- SECTION: CATATAN & INFORMASI LAINNYA --}}
        <div class="mb-8 pb-6 border-b">
            <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                    <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm9.707 5.707a1 1 0 00-1.414-1.414L9 12.586l-1.293-1.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                Catatan & Informasi Lainnya
            </h2>
            <div class="grid grid-cols-1 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Dokumentasi</label>
                    @if($vidconData->dokumentasi)
                        <a href="{{ $vidconData->dokumentasi }}" target="_blank" class="text-blue-600 hover:text-blue-800 font-medium break-all">
                            {{ $vidconData->dokumentasi }}
                        </a>
                    @else
                        <p class="text-gray-900">-</p>
                    @endif
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Informasi Pimpinan</label>
                    <p class="text-gray-900 whitespace-pre-line">{{ $vidconData->informasi_pimpinan ?? '-' }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Keterangan</label>
                    <p class="text-gray-900 whitespace-pre-line">{{ $vidconData->keterangan ?? '-' }}</p>
                </div>
            </div>
        </div>

        {{-- SECTION: TIMESTAMPS --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-600">
            <div>
                <label class="block text-sm font-medium text-gray-500 mb-1">Dibuat Pada</label>
                <p class="text-gray-900">{{ $vidconData->created_at->format('d F Y H:i') }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500 mb-1">Terakhir Diupdate</label>
                <p class="text-gray-900">{{ $vidconData->updated_at->format('d F Y H:i') }}</p>
            </div>
        </div>

        {{-- SECTION: DOKUMENTASI FOTO KEGIATAN --}}
        @if($vidconData->documentations && $vidconData->documentations->count() > 0)
        <div class="mb-8 pb-6 border-b">
            <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                Dokumentasi Kegiatan ({{ $vidconData->documentations->count() }} Foto)
            </h2>

            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mb-4">
                @foreach($vidconData->documentations as $doc)
                <div class="relative group">
                    <img src="{{ $doc->image_url }}"
                         alt="{{ $doc->caption ?? 'Dokumentasi' }}"
                         class="w-full h-48 object-cover rounded-lg shadow-md">

                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-70 transition-all duration-300 rounded-lg flex items-center justify-center" style="background-color: rgba(0, 0, 0, 0);">
                        <div class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 text-center">
                            <a href="{{ $doc->image_url }}" target="_blank"
                               class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                        </div>
                    </div>

                    @if($doc->caption)
                    <p class="mt-2 text-sm text-gray-700 font-medium">{{ $doc->caption }}</p>
                    @endif
                    <p class="text-xs text-gray-500">
                        Diupload oleh: {{ $doc->uploader->name }}<br>
                        {{ $doc->created_at->format('d/m/Y H:i') }}
                    </p>
                </div>
                @endforeach
            </div>

            @if($vidconData->documentations->first() && $vidconData->documentations->first()->keterangan)
            <div class="bg-gray-50 rounded-lg p-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Keterangan Pelaksanaan Kegiatan:</label>
                <p class="text-gray-900">{{ $vidconData->documentations->first()->keterangan }}</p>
            </div>
            @endif
        </div>
        @else
        <div class="mb-8 pb-6 border-b">
            <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                Dokumentasi Kegiatan
            </h2>
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-yellow-600 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <p class="text-sm text-yellow-700">
                        Belum ada dokumentasi foto yang diupload oleh operator untuk kegiatan ini.
                    </p>
                </div>
            </div>
        </div>
        @endif

        {{-- Action Buttons --}}
        <div class="flex justify-end gap-3 mt-6 pt-6 border-t">
            <form action="{{ route('admin.vidcon-data.destroy', $vidconData) }}" method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded">
                    Hapus
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
