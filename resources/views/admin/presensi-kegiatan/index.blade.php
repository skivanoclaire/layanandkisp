@extends('layouts.authenticated')

@section('title', '- Presensi Kegiatan')
@section('header-title', 'Presensi Kegiatan')

@section('content')
<div class="container mx-auto p-6">
    <div class="mb-6 flex flex-wrap justify-between items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Presensi Kegiatan</h1>
            <p class="text-gray-600 mt-1">Konfirmasi kehadiran &amp; daftar hadir kegiatan (workshop, rapat, sosialisasi)</p>
        </div>
        <a href="{{ route('admin.presensi-kegiatan.create') }}"
           class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-semibold inline-flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Buat Kegiatan
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-lg shadow-md overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-gray-600 text-left">
                <tr>
                    <th class="px-4 py-3">Kegiatan</th>
                    <th class="px-4 py-3">Tanggal</th>
                    <th class="px-4 py-3 text-center">Konfirmasi</th>
                    <th class="px-4 py-3 text-center">Hadir</th>
                    <th class="px-4 py-3 text-center">Konfirmasi</th>
                    <th class="px-4 py-3 text-center">Presensi</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($kegiatans as $k)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <a href="{{ route('admin.presensi-kegiatan.show', $k) }}" class="font-semibold text-gray-800 hover:text-green-700">{{ $k->judul }}</a>
                            <div class="text-xs text-gray-500">{{ $k->tempat }}</div>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">{{ $k->tanggal_mulai->locale('id')->isoFormat('D MMM YYYY') }}</td>
                        <td class="px-4 py-3 text-center font-semibold">{{ $k->konfirmasi_count }}</td>
                        <td class="px-4 py-3 text-center font-semibold text-green-700">{{ $k->hadir_count }}</td>
                        @foreach(['konfirmasi_aktif', 'presensi_aktif'] as $kolom)
                            <td class="px-4 py-3 text-center">
                                <form method="POST" action="{{ route('admin.presensi-kegiatan.toggle', $k) }}">
                                    @csrf
                                    <input type="hidden" name="kolom" value="{{ $kolom }}">
                                    <button type="submit" title="Klik untuk {{ $k->{$kolom} ? 'menonaktifkan' : 'mengaktifkan' }}"
                                            class="text-xs font-semibold px-2.5 py-1 rounded-full {{ $k->{$kolom} ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-gray-200 text-gray-600 hover:bg-gray-300' }}">
                                        {{ $k->{$kolom} ? 'Aktif' : 'Nonaktif' }}
                                    </button>
                                </form>
                            </td>
                        @endforeach
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <a href="{{ route('admin.presensi-kegiatan.show', $k) }}" class="text-green-700 font-semibold hover:underline">Dasbor →</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-8 text-center text-gray-500">Belum ada kegiatan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $kegiatans->links() }}</div>
</div>
@endsection
