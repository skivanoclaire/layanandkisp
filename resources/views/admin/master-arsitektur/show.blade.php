@extends('layouts.authenticated')

@section('title', '- ' . $item->label_jenis)
@section('header-title', 'Master Data Arsitektur')

@section('content')
<div class="container mx-auto p-6">
    <div class="max-w-5xl mx-auto">
        <div class="mb-6 flex flex-wrap justify-between items-start gap-4">
            <div>
                <a href="{{ route('admin.master-arsitektur.kategori', $kategori) }}" class="text-sm text-green-700 hover:underline">← Kembali ke dasbor</a>
                <h1 class="text-2xl font-bold text-gray-800 mt-2">{{ $item->nama }}</h1>
                <p class="text-gray-600 mt-1">{{ $item->label_jenis }}@if ($item->kode) · {{ $item->kode }}@endif</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.master-arsitektur.edit', $item) }}"
                   class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold">Edit</a>
                <form action="{{ route('admin.master-arsitektur.destroy', $item) }}" method="POST"
                      onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-semibold">Hapus</button>
                </form>
            </div>
        </div>

        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">{{ session('success') }}</div>
        @endif

        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <table class="min-w-full text-sm">
                <tbody>
                    @foreach ($kolom as $kunci => $definisi)
                        <tr class="border-b border-gray-100 align-top">
                            <th class="px-4 py-3 text-left font-semibold text-gray-600 bg-gray-50 w-1/3">{{ $definisi['label'] }}</th>
                            <td class="px-4 py-3 text-gray-800 whitespace-pre-line">@if ($item->nilai($kunci) === null)<span class="text-gray-400">-</span>@else{{ $item->nilai($kunci) }}@endif
                                @if ($kunci === 'unit_kerja' && $item->unit_kerja_id === null && $item->unit_kerja_teks !== null)
                                    <span class="inline-block px-1.5 py-0.5 rounded bg-orange-100 text-orange-700 text-[10px] font-semibold">belum terpetakan ke Master Data Unit Kerja</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <p class="text-xs text-gray-500 mt-3">
            Diperbarui {{ $item->updated_at?->locale('id')->isoFormat('D MMMM YYYY, HH:mm') }}
        </p>
    </div>
</div>
@endsection
