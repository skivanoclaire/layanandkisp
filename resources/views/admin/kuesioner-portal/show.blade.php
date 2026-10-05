@extends('layouts.authenticated')

@section('title', '- Detail Respons Kuesioner Portal')
@section('header-title', 'Kuesioner Kualitas Layanan Portal')

@php
    $jawaban = $response->answers->keyBy('kode');
    $alasan = $eksklusi['semua'][$response->kode_responden] ?? [];
    $alasanKano = $eksklusi['kano'][$response->kode_responden] ?? null;
@endphp

@section('content')
    <div class="container mx-auto px-4 py-6 max-w-5xl">
        @include('user.kuesioner-portal._flash')

        <a href="{{ route('admin.kuesioner-portal.responses', $response->periode) }}" class="text-sm text-green-700 hover:underline">← Daftar respons</a>
        <h1 class="text-2xl font-semibold text-gray-900 mt-1">Respons {{ $response->kode_responden }}</h1>
        <p class="text-sm text-gray-600">{{ $response->periode->nama }} · dikirim {{ $response->submitted_at?->translatedFormat('d F Y H:i') }}
            · durasi {{ $response->durasi_detik !== null ? gmdate('H:i:s', $response->durasi_detik) : '-' }}</p>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mt-5">
            <div class="bg-white rounded-lg shadow-sm p-5 lg:col-span-2">
                <h2 class="font-semibold text-gray-900 mb-3">Profil</h2>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-2 text-sm">
                    <div><dt class="text-gray-500">Perangkat daerah</dt><dd>{{ $response->unitKerja?->nama ?? '-' }}</dd></div>
                    <div><dt class="text-gray-500">Status kepegawaian</dt><dd>{{ $profil['status_kepegawaian'][$response->status_kepegawaian] ?? '-' }}</dd></div>
                    <div><dt class="text-gray-500">Peran</dt><dd>{{ $profil['peran'][$response->peran] ?? '-' }}</dd></div>
                    <div><dt class="text-gray-500">Lama menggunakan</dt><dd>{{ $profil['lama_penggunaan'][$response->lama_penggunaan] ?? '-' }}</dd></div>
                    <div><dt class="text-gray-500">Frekuensi</dt><dd>{{ $profil['frekuensi'][$response->frekuensi] ?? '-' }}</dd></div>
                    <div><dt class="text-gray-500">Pernah menghubungi petugas</dt><dd>{{ $response->pernah_hubungi_petugas ? 'Ya' : 'Tidak' }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-gray-500">Layanan yang pernah diajukan</dt>
                        <dd>{{ collect($response->layanan_diajukan ?? [])->map(fn ($k) => $profil['layanan'][$k] ?? $k)->implode(', ') }}
                            @if ($response->layanan_lainnya) ({{ $response->layanan_lainnya }}) @endif</dd></div>
                </dl>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-5">
                <h2 class="font-semibold text-gray-900 mb-3">Pra-pemrosesan</h2>
                @forelse ($alasan as $a)
                    <p class="text-sm text-red-700">• {{ $a }}</p>
                @empty
                    <p class="text-sm text-green-700">Lolos seluruh aturan pra-pemrosesan.</p>
                @endforelse
                @if ($alasanKano)
                    <p class="text-sm text-yellow-700 mt-2">• Kano: {{ $alasanKano }}</p>
                @endif

                <form method="POST" action="{{ route('admin.kuesioner-portal.eksklusi', $response) }}" class="mt-4 border-t pt-4 space-y-2">
                    @csrf
                    @if ($response->dikecualikan)
                        <input type="hidden" name="dikecualikan" value="0">
                        <button class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 hover:bg-gray-50">Sertakan kembali dalam analisis</button>
                    @else
                        <input type="hidden" name="dikecualikan" value="1">
                        <label for="alasan_dikecualikan" class="block text-xs text-gray-600">Kecualikan secara manual (mis. tidak memenuhi kriteria inklusi)</label>
                        <input id="alasan_dikecualikan" name="alasan_dikecualikan" required maxlength="255" placeholder="Alasan"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        <button class="w-full px-3 py-2 text-sm rounded-lg bg-red-600 text-white hover:bg-red-700">Kecualikan dari analisis</button>
                    @endif
                </form>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-5 mt-4 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="text-left text-xs text-gray-500 border-b">
                        <th class="py-2 pr-3">Kode</th>
                        <th class="py-2 pr-3">Atribut</th>
                        <th class="py-2 px-2 text-center">Kepentingan</th>
                        <th class="py-2 px-2 text-center">Kinerja</th>
                        <th class="py-2 px-2 text-center">F</th>
                        <th class="py-2 px-2 text-center">D</th>
                        <th class="py-2 px-2 text-center">Kano</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach ($atribut as $kode => $a)
                        <tr>
                            <td class="py-2 pr-3 font-mono text-xs text-gray-500">{{ $kode }}</td>
                            <td class="py-2 pr-3">{{ $a['kinerja'] }}</td>
                            <td class="py-2 px-2 text-center">{{ $jawaban[$kode]->kepentingan ?? '-' }}</td>
                            <td class="py-2 px-2 text-center">{{ $jawaban[$kode]->kinerja ?? '-' }}</td>
                            <td class="py-2 px-2 text-center">{{ $jawaban[$kode]->kano_fungsional ?? '-' }}</td>
                            <td class="py-2 px-2 text-center">{{ $jawaban[$kode]->kano_disfungsional ?? '-' }}</td>
                            <td class="py-2 px-2 text-center font-semibold">
                                {{ \App\Services\KuesionerPortal\Kano::kategori($jawaban[$kode]->kano_fungsional ?? null, $jawaban[$kode]->kano_disfungsional ?? null) ?? '-' }}
                            </td>
                        </tr>
                    @endforeach
                    @foreach ($kepuasan as $kode => $teks)
                        <tr>
                            <td class="py-2 pr-3 font-mono text-xs text-gray-500">{{ $kode }}</td>
                            <td class="py-2 pr-3">{{ $teks }}</td>
                            <td class="py-2 px-2 text-center">-</td>
                            <td class="py-2 px-2 text-center">{{ $jawaban[$kode]->kinerja ?? '-' }}</td>
                            <td colspan="3"></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-5 mt-4 grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
            @foreach (['kelebihan' => 'Kelebihan', 'kekurangan' => 'Kekurangan/kendala', 'saran' => 'Saran perbaikan'] as $f => $judul)
                <div>
                    <h3 class="font-medium text-gray-900">{{ $judul }}</h3>
                    <p class="text-gray-700 mt-1 whitespace-pre-line">{{ $response->{$f} ?: '-' }}</p>
                </div>
            @endforeach
        </div>
    </div>
@endsection
