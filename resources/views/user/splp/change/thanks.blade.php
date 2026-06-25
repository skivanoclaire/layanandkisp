@extends('layouts.authenticated')

@section('title', '- Permohonan Terkirim')
@section('header-title', 'Permohonan Terkirim')

@section('content')
<div class="container mx-auto p-6 max-w-2xl">
    <div class="bg-white rounded-lg shadow-sm border p-8 text-center">
        <div class="mx-auto w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-4">
            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        </div>
        <h1 class="text-xl font-bold text-gray-800">Permohonan Diajukan</h1>
        <p class="text-gray-600 mt-2">Permohonan perubahan/perpanjangan untuk <span class="font-semibold">{{ $item->service->nama_layanan ?? '-' }}</span> tercatat dengan tiket:</p>
        <p class="text-2xl font-mono font-bold text-green-700 my-3">{{ $item->ticket_no }}</p>
        <p class="text-sm text-gray-500">Status:
            <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $item->status_badge_class }}">{{ $item->status_label }}</span>.
            Perubahan minor diproses cepat; perubahan signifikan memerlukan keputusan Kepala Dinas.
        </p>
        <div class="mt-6">
            <a href="{{ route('user.splp.change.index') }}" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2.5 rounded-lg font-semibold">Lihat Daftar Permohonan</a>
        </div>
    </div>
</div>
@endsection
