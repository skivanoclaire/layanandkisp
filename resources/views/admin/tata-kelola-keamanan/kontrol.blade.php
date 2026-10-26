@extends('layouts.authenticated')

@section('title', '- Status Kontrol')
@section('header-title', 'Tata Kelola Keamanan: Status Penerapan Kontrol ISO/IEC 27001:2022')

@section('content')
    <div class="container mx-auto px-4 py-6">
        @include('admin.tata-kelola-keamanan._nav')

        @include('admin.tata-kelola-keamanan._petunjuk', [
            'judul' => 'Tahap 5b — Status penerapan kontrol',
            'poin' => [
                'Diterapkan' => 'Kontrol berjalan dan ada bukti. Contoh A.8.7: antimalware terpasang di semua perangkat dan ada log pembaruan.',
                'Sebagian' => 'Kontrol ada tetapi belum lengkap atau belum konsisten. Contoh A.5.15: kebijakan akses ada tetapi belum ditinjau berkala.',
                'Belum diterapkan' => 'Belum ada kegiatan atau dokumen sama sekali. Contoh A.5.7: belum ada pemantauan informasi ancaman.',
                'Tidak berlaku' => 'Kontrol memang tidak relevan dengan lingkup instansi; tulis alasannya di kolom bukti. Contoh A.8.30 bila tidak ada pengembangan aplikasi yang dialihdayakan.',
                'Bukti' => 'Tulis singkat nama dokumen atau sumber bukti, tanpa detail sensitif (alamat IP, konfigurasi).',
            ],
        ])

        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900">Tahap 5b — Status penerapan kontrol Annex A</h2>
            <p class="text-sm text-gray-600 mt-1">Daftar kontrol diambil otomatis dari butir pemetaan (kecuali butir yang dikeluarkan dari model). Kategori status mengikuti
                Nurani dan Legowo (2026, hlm. 17): diterapkan, sebagian, belum diterapkan, atau tidak berlaku. ★ = kontrol baru versi 2022.</p>
        </div>

        @if (empty($cakupan))
            <div class="bg-white rounded-lg shadow-sm p-6 text-sm text-gray-600">Belum ada kontrol Annex A dalam pemetaan.</div>
        @else
            <form method="POST" action="{{ route('admin.tata-kelola-keamanan.kontrol.simpan', $evaluasi) }}">
                @csrf
                <div class="bg-white rounded-lg shadow-sm p-6 mb-4 overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-left text-xs text-gray-500 border-b">
                                <th class="py-2 pr-3">Kontrol</th>
                                <th class="py-2 px-3">Objektif / praktik</th>
                                <th class="py-2 px-3 text-center">Disepakati FGD</th>
                                <th class="py-2 px-3">Status</th>
                                <th class="py-2 px-3">Bukti / catatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y align-top">
                            @foreach ($cakupan as $kode => $c)
                                <tr>
                                    <td class="py-2 pr-3">{{ \App\Services\TataKelolaKeamanan\Istilah::isoRingkas($kode) }} {{ $c['baru'] ? '★' : '' }}<br><span class="text-xs text-gray-500">{{ \App\Services\TataKelolaKeamanan\Istilah::iso($kode)['penjelasan'] }}</span></td>
                                    <td class="py-2 px-3 text-xs">{{ implode(', ', $c['objektif']) }}<br><span class="text-gray-500">{{ implode(', ', $c['praktik']) }}</span></td>
                                    <td class="py-2 px-3 text-center text-xs">{{ $c['disepakati'] ? 'ya' : 'belum' }}</td>
                                    <td class="py-2 px-3">
                                        <select name="status[{{ $kode }}]" class="rounded border-gray-300 text-sm">
                                            <option value="">belum dinilai</option>
                                            @foreach (config('tata_kelola_keamanan.status_kontrol') as $k => $label)
                                                <option value="{{ $k }}" @selected(($status[$kode]->status ?? null) === $k)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="py-2 px-3"><textarea name="bukti[{{ $kode }}]" rows="1" class="w-full min-w-[16rem] rounded border-gray-300 text-sm">{{ $status[$kode]->bukti ?? '' }}</textarea></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <button class="px-5 py-2 text-sm font-medium rounded-lg bg-green-600 text-white hover:bg-green-700">Simpan status kontrol</button>
            </form>
        @endif
    </div>
@endsection
