@extends('layouts.authenticated')

@section('title', '- Pembaruan Data Arsitektur')
@section('header-title', 'Pembaruan Data Arsitektur')

@section('content')
<div class="container mx-auto p-6">
    <div class="mb-6 flex flex-wrap justify-between items-start gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Pembaruan Data Arsitektur</h1>
            <p class="text-gray-600 mt-1">
                @if ($unit)
                    Data Arsitektur SPBE milik <strong>{{ $unit->nama }}</strong>. Usulan tambah atau ubah data berlaku setelah disetujui admin.
                @else
                    Data Arsitektur SPBE milik instansi Anda.
                @endif
            </p>
        </div>
        @if ($unit)
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('user.arsitektur.permohonan') }}"
                   class="bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 px-4 py-2 rounded-lg font-semibold">
                    Riwayat Usulan
                    @if ($jumlahMenunggu)
                        <span class="ml-1 inline-block px-2 py-0.5 rounded-full text-xs bg-yellow-100 text-yellow-800">{{ $jumlahMenunggu }} menunggu</span>
                    @endif
                </a>
                @if (count($konfig['jenis']) === 1)
                    <a href="{{ route('user.arsitektur.tambah', $kategori) }}"
                       class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-semibold">+ Usulkan {{ $konfig['label'] }} Baru</a>
                @else
                    <div x-data="{ buka: false }" class="relative">
                        <button type="button" @click="buka = !buka" @click.outside="buka = false"
                                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-semibold">+ Usulkan Data Baru ▾</button>
                        <div x-show="buka" x-cloak class="absolute right-0 mt-1 w-72 bg-white border border-gray-200 rounded-lg shadow-lg z-20 py-1">
                            @foreach ($konfig['jenis'] as $j)
                                <a href="{{ route('user.arsitektur.tambah', ['kategori' => $kategori, 'jenis' => $j]) }}"
                                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-green-50">{{ \App\Services\MasterArsitektur\Skema::label($j) }}</a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @endif
    </div>

    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">{{ session('error') }}</div>
    @endif

    @if (! $unit)
        <div class="bg-yellow-50 border border-yellow-300 text-yellow-800 px-4 py-3 rounded">
            Akun Anda belum terhubung ke instansi, sehingga data arsitektur belum dapat ditampilkan.
            <a href="{{ route('profile.edit') }}" class="font-semibold underline">Lengkapi unit kerja pada profil</a> terlebih dahulu.
        </div>
    @else
        {{-- Tab kategori --}}
        <div class="mb-6 border-b border-gray-200 overflow-x-auto">
            <nav class="flex gap-1 min-w-max">
                @foreach ($semuaKategori as $kunci => $k)
                    <a href="{{ route('user.arsitektur.index', ['kategori' => $kunci]) }}"
                       class="px-4 py-2.5 text-sm font-semibold rounded-t-lg border-b-2 whitespace-nowrap
                              {{ $kunci === $kategori ? 'border-green-600 text-green-700 bg-green-50' : 'border-transparent text-gray-600 hover:text-green-700 hover:bg-gray-50' }}">
                        {{ $k['label'] }}
                        <span class="ml-1 inline-block px-2 py-0.5 rounded-full text-xs {{ $kunci === $kategori ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-700' }}">{{ number_format($jumlahKategori[$kunci], 0, ',', '.') }}</span>
                    </a>
                @endforeach
            </nav>
        </div>

        <form method="GET" action="{{ route('user.arsitektur.index') }}" class="bg-white rounded-lg shadow-md p-4 mb-6 flex gap-2 max-w-xl">
            <input type="hidden" name="kategori" value="{{ $kategori }}">
            <input type="search" name="q" value="{{ $cari }}" placeholder="Cari nama atau kode"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500">
            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-semibold">Cari</button>
        </form>

        <div class="bg-white rounded-lg shadow-md p-4">
            <div class="flex flex-wrap justify-between items-center gap-2 mb-3">
                <h2 class="text-lg font-bold text-gray-800">{{ $konfig['panel']['katalog'] }}</h2>
                <span class="text-sm text-gray-500">{{ number_format($items->total(), 0, ',', '.') }} entri</span>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full table-auto border-collapse text-sm">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="border border-gray-300 px-3 py-2 text-left w-12">No</th>
                            @foreach ($konfig['katalog'] as $kolom => $label)
                                @continue($kolom === 'unit_kerja')
                                <th class="border border-gray-300 px-3 py-2 text-left">{{ $label }}</th>
                            @endforeach
                            <th class="border border-gray-300 px-3 py-2 text-center w-40">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($items as $item)
                            <tr class="hover:bg-gray-50 align-top">
                                <td class="border border-gray-300 px-3 py-2 text-center">{{ $items->firstItem() + $loop->index }}</td>
                                @foreach ($konfig['katalog'] as $kolom => $label)
                                    @continue($kolom === 'unit_kerja')
                                    <td class="border border-gray-300 px-3 py-2 {{ $kolom === 'kode' ? 'whitespace-nowrap' : '' }}">
                                        @if ($kolom === 'nama')
                                            <span class="font-semibold text-gray-800">{{ $item->nama }}</span>
                                        @elseif ($kolom === 'jenis')
                                            {{ $item->label_jenis }}
                                        @else
                                            {{ $item->getAttribute($kolom) ?? '-' }}
                                        @endif
                                    </td>
                                @endforeach
                                <td class="border border-gray-300 px-3 py-2 text-center">
                                    @if (isset($sedangDiusulkan[$item->id]))
                                        <a href="{{ route('user.arsitektur.permohonan.show', $sedangDiusulkan[$item->id]) }}"
                                           class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800 hover:underline">Menunggu persetujuan</a>
                                    @else
                                        <a href="{{ route('user.arsitektur.ubah', $item) }}" class="text-blue-600 hover:text-blue-800 font-semibold">Usulkan Perubahan</a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($konfig['katalog']) + 2 }}" class="border border-gray-300 px-3 py-8 text-center text-gray-500">
                                    Belum ada data {{ $konfig['label'] }} untuk instansi Anda
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $items->links() }}</div>
        </div>
    @endif
</div>
@endsection
