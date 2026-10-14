@extends('layouts.authenticated')

@section('title', '- Usulan Data Arsitektur')
@section('header-title', 'Pembaruan Data Arsitektur')

@section('content')
<div class="container mx-auto p-6">
    <div class="max-w-5xl mx-auto">
        <div class="mb-6 flex flex-wrap justify-between items-start gap-4">
            <div>
                <a href="{{ route('user.arsitektur.permohonan') }}" class="text-sm text-green-700 hover:underline">← Kembali ke riwayat usulan</a>
                <h1 class="text-2xl font-bold text-gray-800 mt-2">{{ $permohonan->nama_entri }}</h1>
            </div>
            @if ($permohonan->menunggu() && (int) $permohonan->user_id === (int) auth()->id())
                <form method="POST" action="{{ route('user.arsitektur.permohonan.batal', $permohonan) }}"
                      onsubmit="return confirm('Batalkan usulan ini?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-semibold">Batalkan Usulan</button>
                </form>
            @endif
        </div>

        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">{{ session('error') }}</div>
        @endif

        @include('admin.master-arsitektur.permohonan._rincian')
    </div>
</div>
@endsection
