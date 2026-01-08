@extends('layouts.authenticated')

@section('title', '- Detail Data Vidcon')
@section('header-title', 'Detail Data Vidcon')

@section('content')
<div class="container mx-auto px-4">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Detail Data Fasilitasi Video Konferensi</h1>
        <div class="flex gap-2">
            <a href="{{ route('op.tik.schedule.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded font-semibold">
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
    </div>
</div>
@endsection
