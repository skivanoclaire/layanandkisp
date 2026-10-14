@extends('layouts.authenticated')

@section('title', '- Kesenjangan & Rekomendasi')
@section('header-title', 'Tata Kelola Keamanan: Kesenjangan dan Peta Jalan')

@section('content')
    <div class="container mx-auto px-4 py-6">
        @include('admin.tata-kelola-keamanan._nav')

        @include('admin.tata-kelola-keamanan._petunjuk', [
            'judul' => 'Tahap 6 — Menyusun rekomendasi dan peta jalan',
            'poin' => [
                'Dasar' => 'Gunakan tabel kesenjangan dan daftar kontrol yang belum diterapkan di bawah. Mulai dari objektif dengan kesenjangan terbesar.',
                'Rekomendasi yang baik' => 'Spesifik dan dapat ditindaklanjuti: apa yang dibuat/dilakukan, oleh siapa, dan keluarannya. Contoh: "Menyusun daftar risiko keamanan informasi instansi sesuai Pergub 20/2024 Pasal 14 dan menetapkannya dengan keputusan Kepala Dinas" (APO12, horizon jangka pendek).',
                'Horizon' => 'Jangka pendek ≤ 1 tahun (tanpa anggaran besar, mis. kebijakan dan prosedur); menengah 1–3 tahun (perlu anggaran/SDM, mis. pemantauan terpusat); panjang 3–5 tahun (perubahan besar, mis. sertifikasi ISO/IEC 27001).',
                'Prioritas' => 'Angka 1 = paling didahulukan dalam setiap horizon.',
                'Berikutnya' => 'Konfirmasikan rekomendasi bersama pimpinan instansi, lalu cetak Laporan atau ekspor Excel sebagai bahan laporan.',
            ],
        ])

        <div class="bg-white rounded-lg shadow-sm p-6 mb-6 overflow-x-auto">
            <h2 class="text-lg font-semibold text-gray-900">Tahap 6 — Kesenjangan</h2>
            <p class="text-sm text-gray-600 mt-1 mb-3">Kesenjangan = target − tingkat saat ini. Diurutkan dari kesenjangan terbesar, lalu skor kepentingan.</p>
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="text-left text-xs text-gray-500 border-b">
                        <th class="py-2 pr-3">Objektif</th>
                        <th class="py-2 px-3 text-center">Skor</th>
                        <th class="py-2 px-3 text-center">Target</th>
                        <th class="py-2 px-3 text-center">Saat ini</th>
                        <th class="py-2 px-3 text-center">Kesenjangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach ($kesenjangan as $r)
                        <tr>
                            <td class="py-2 pr-3"><span class="font-mono">{{ $r['objektif']->kode }}</span> {{ $r['objektif']->nama }}<br><span class="text-xs text-gray-600">{{ $r['objektif']->nama_id }}</span></td>
                            <td class="py-2 px-3 text-center">{{ $r['objektif']->skor_kepentingan ?? '-' }}</td>
                            <td class="py-2 px-3 text-center">{{ $r['target'] ?? '-' }}</td>
                            <td class="py-2 px-3 text-center">{{ $r['hasil']['level'] ?? '-' }}</td>
                            <td class="py-2 px-3 text-center font-semibold {{ ($r['gap'] ?? 0) >= 2 ? 'text-red-700' : '' }}">{{ $r['gap'] ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($kontrolBelum->isNotEmpty())
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6 overflow-x-auto">
                <h3 class="font-semibold text-gray-900 mb-2">Kontrol yang belum atau baru sebagian diterapkan ({{ $kontrolBelum->count() }})</h3>
                <table class="min-w-full text-sm">
                    <tbody class="divide-y">
                        @foreach ($kontrolBelum as $k)
                            <tr>
                                <td class="py-1 pr-3 font-mono">{{ $k['kode'] }}</td>
                                <td class="py-1 px-3">{{ \App\Services\TataKelolaKeamanan\Istilah::isoJudul($k['kode']) }} ({{ \App\Services\TataKelolaKeamanan\Istilah::isoId($k['kode']) }}) {{ $k['baru'] ? '★' : '' }}</td>
                                <td class="py-1 px-3 text-xs">{{ implode(', ', $k['objektif']) }}</td>
                                <td class="py-1 px-3 text-xs">{{ config("tata_kelola_keamanan.status_kontrol.{$k['status']}") }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <h3 class="font-semibold text-gray-900 mb-3">Peta jalan rekomendasi</h3>
            @foreach (config('tata_kelola_keamanan.horizon') as $h => $labelH)
                @php $items = $rekomendasis->where('horizon', $h); @endphp
                <p class="mt-4 text-sm font-semibold text-gray-700">{{ $labelH }} ({{ $items->count() }})</p>
                @foreach ($items as $rk)
                    <details class="mt-2 p-3 rounded-lg border text-sm">
                        <summary class="cursor-pointer">
                            <span class="font-semibold">#{{ $rk->prioritas }}</span>
                            <span class="font-mono text-xs text-gray-500">{{ $rk->praktik?->kode ?? $rk->objektif_kode }} {{ $rk->iso_kode }}</span>
                            {{ $rk->uraian }}
                            @if ($rk->penanggung_jawab)
                                <span class="text-xs text-gray-500">— {{ $rk->penanggung_jawab }}</span>
                            @endif
                        </summary>
                        <form method="POST" action="{{ route('admin.tata-kelola-keamanan.rekomendasi.update', [$evaluasi, $rk]) }}" class="mt-2">
                            @csrf @method('PUT')
                            @include('admin.tata-kelola-keamanan._form-rekomendasi', ['rk' => $rk])
                            <button class="mt-2 px-3 py-1 text-sm rounded-lg bg-green-600 text-white">Simpan</button>
                        </form>
                        <form method="POST" action="{{ route('admin.tata-kelola-keamanan.rekomendasi.destroy', [$evaluasi, $rk]) }}" class="mt-2">
                            @csrf @method('DELETE')
                            <button class="text-xs text-red-600 hover:underline">hapus</button>
                        </form>
                    </details>
                @endforeach
            @endforeach
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="font-semibold text-gray-900 mb-3">Tambah rekomendasi</h3>
            <form method="POST" action="{{ route('admin.tata-kelola-keamanan.rekomendasi.store', $evaluasi) }}">
                @csrf
                @include('admin.tata-kelola-keamanan._form-rekomendasi', ['rk' => null])
                <button class="mt-3 px-4 py-2 text-sm font-medium rounded-lg bg-green-600 text-white hover:bg-green-700">Tambah</button>
            </form>
        </div>
    </div>
@endsection
