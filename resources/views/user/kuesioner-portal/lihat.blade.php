@extends('layouts.authenticated')

@section('title', '- Jawaban Kuesioner Saya')
@section('header-title', 'Kuesioner Kualitas Layanan Portal')

@php
    $atribut = \App\Services\KuesionerPortal\Instrumen::atribut();
    $kepuasan = config('kuesioner_portal.kepuasan');
    $profil = config('kuesioner_portal.profil');
    $jawaban = $response->answers->keyBy('kode');
@endphp

@section('content')
    <div class="container mx-auto px-4 py-6 max-w-4xl">
        @include('user.kuesioner-portal._flash')

        <div class="bg-white rounded-lg shadow-sm p-6">
            <h1 class="text-lg font-semibold text-gray-900">Jawaban Anda — {{ $periode->nama }}</h1>
            <p class="text-sm text-gray-500 mt-1">Dikirim {{ $response->submitted_at->translatedFormat('d F Y H:i') }} · Kode responden {{ $response->kode_responden }}</p>

            <dl class="mt-5 grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                <div><dt class="text-gray-500">Perangkat daerah</dt><dd class="text-gray-900">{{ $response->unitKerja?->nama ?? '-' }}</dd></div>
                <div><dt class="text-gray-500">Status kepegawaian</dt><dd class="text-gray-900">{{ $profil['status_kepegawaian'][$response->status_kepegawaian] ?? '-' }}</dd></div>
                <div><dt class="text-gray-500">Peran</dt><dd class="text-gray-900">{{ $profil['peran'][$response->peran] ?? '-' }}</dd></div>
                <div><dt class="text-gray-500">Lama menggunakan portal</dt><dd class="text-gray-900">{{ $profil['lama_penggunaan'][$response->lama_penggunaan] ?? '-' }}</dd></div>
                <div><dt class="text-gray-500">Frekuensi penggunaan</dt><dd class="text-gray-900">{{ $profil['frekuensi'][$response->frekuensi] ?? '-' }}</dd></div>
                <div><dt class="text-gray-500">Pernah menghubungi petugas</dt><dd class="text-gray-900">{{ $response->pernah_hubungi_petugas ? 'Ya' : 'Tidak' }}</dd></div>
            </dl>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6 mt-4 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="text-left text-xs text-gray-500 border-b">
                        <th class="py-2 pr-3">Kode</th>
                        <th class="py-2 pr-3">Pernyataan</th>
                        <th class="py-2 px-2 text-center">Kepentingan</th>
                        <th class="py-2 px-2 text-center">Kinerja</th>
                        <th class="py-2 px-2 text-center">Terpenuhi</th>
                        <th class="py-2 px-2 text-center">Tidak terpenuhi</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach ($atribut as $kode => $a)
                        <tr>
                            <td class="py-2 pr-3 font-mono text-xs text-gray-500">{{ $kode }}</td>
                            <td class="py-2 pr-3 text-gray-800">{{ $a['kinerja'] }}</td>
                            <td class="py-2 px-2 text-center">{{ $jawaban[$kode]->kepentingan ?? '-' }}</td>
                            <td class="py-2 px-2 text-center">{{ $jawaban[$kode]->kinerja ?? '-' }}</td>
                            <td class="py-2 px-2 text-center">{{ $jawaban[$kode]->kano_fungsional ?? '-' }}</td>
                            <td class="py-2 px-2 text-center">{{ $jawaban[$kode]->kano_disfungsional ?? '-' }}</td>
                        </tr>
                    @endforeach
                    @foreach ($kepuasan as $kode => $teks)
                        <tr>
                            <td class="py-2 pr-3 font-mono text-xs text-gray-500">{{ $kode }}</td>
                            <td class="py-2 pr-3 text-gray-800">{{ $teks }}</td>
                            <td class="py-2 px-2 text-center">-</td>
                            <td class="py-2 px-2 text-center">{{ $jawaban[$kode]->kinerja ?? '-' }}</td>
                            <td class="py-2 px-2 text-center">-</td>
                            <td class="py-2 px-2 text-center">-</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
