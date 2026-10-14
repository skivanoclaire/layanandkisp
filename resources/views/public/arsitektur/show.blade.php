@extends('public.arsitektur.layout')

@section('title', $item->nama)

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('arsitektur.kategori', $kategori) }}" class="text-sm text-green-700 hover:underline">← Kembali ke daftar</a>
        <h1 class="text-2xl font-bold text-gray-800 mt-2">{{ $item->nama }}</h1>
        <p class="text-gray-600 mt-1">{{ $item->label_jenis }}@if ($item->kode) · {{ $item->kode }}@endif</p>
    </div>

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <table class="min-w-full text-sm">
            <tbody>
                @foreach ($kolom as $kunci => $definisi)
                    <tr class="border-b border-gray-100 align-top">
                        <th class="px-4 py-3 text-left font-semibold text-gray-600 bg-gray-50 w-1/3">{{ $definisi['label'] }}</th>
                        <td class="px-4 py-3 text-gray-800 whitespace-pre-line break-words">@if ($item->nilai($kunci) === null)<span class="text-gray-400">-</span>@else{{ $item->nilai($kunci) }}@endif</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <p class="text-xs text-gray-500 mt-3">
        Diperbarui {{ $item->updated_at?->locale('id')->isoFormat('D MMMM YYYY') }}
    </p>
</div>
@endsection
