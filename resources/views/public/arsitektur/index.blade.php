@extends('public.arsitektur.layout')

@section('title', 'Arsitektur SPBE - ' . $konfig['label'])

@php
    $panel = $konfig['panel'];
    $kolomKatalog = $konfig['katalog'];
    $urlKategori = fn (array $ubah = []) => route('arsitektur.kategori', ['kategori' => $kategori] + array_filter($ubah + $filter, fn ($v) => $v !== null && $v !== ''));
    $batasGrafik = 15;
    $grafik = [];
    foreach ($sebaran as $kolom => $baris) {
        $grafik[$kolom] = [
            'label' => $baris->take($batasGrafik)->pluck('label')->all(),
            'jumlah' => $baris->take($batasGrafik)->pluck('jumlah')->all(),
            'aktif' => $filter[$kolom] ?? null,
            'bisaFilter' => isset($daftarFilter[$kolom]),
        ];
    }
@endphp

@section('content')
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Arsitektur SPBE</h1>
        <p class="text-gray-600 mt-1">Arsitektur Sistem Pemerintahan Berbasis Elektronik Pemerintah Provinsi Kalimantan Utara</p>
    </div>

    {{-- Tab kategori --}}
    <div class="mb-6 border-b border-gray-200 overflow-x-auto">
        <nav class="flex gap-1 min-w-max">
            @foreach ($semuaKategori as $kunci => $k)
                <a href="{{ route('arsitektur.kategori', $kunci) }}"
                   class="px-4 py-2.5 text-sm font-semibold rounded-t-lg border-b-2 whitespace-nowrap
                          {{ $kunci === $kategori ? 'border-green-600 text-green-700 bg-green-50' : 'border-transparent text-gray-600 hover:text-green-700 hover:bg-gray-50' }}">
                    {{ $k['label'] }}
                    <span class="ml-1 inline-block px-2 py-0.5 rounded-full text-xs {{ $kunci === $kategori ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-700' }}">{{ number_format($jumlahKategori[$kunci], 0, ',', '.') }}</span>
                </a>
            @endforeach
        </nav>
    </div>

    {{-- Filter --}}
    <form method="GET" action="{{ route('arsitektur.kategori', $kategori) }}" class="bg-white rounded-lg shadow-md p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
            @foreach ($daftarFilter as $kunci => $label)
                <div>
                    <label for="filter-{{ $kunci }}" class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">{{ $label }}</label>
                    <select name="{{ $kunci }}" id="filter-{{ $kunci }}" onchange="this.form.submit()"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        <option value="">Semua</option>
                        @foreach ($pilihan[$kunci] as $nilai => $opsi)
                            <option value="{{ $nilai }}" @selected(($filter[$kunci] ?? null) === (string) $nilai)>{{ $opsi['label'] }} ({{ $opsi['jumlah'] }})</option>
                        @endforeach
                        @if (isset($filter[$kunci]) && ! array_key_exists($filter[$kunci], $pilihan[$kunci]))
                            <option value="{{ $filter[$kunci] }}" selected>{{ $filter[$kunci] }} (0)</option>
                        @endif
                    </select>
                </div>
            @endforeach
            <div>
                <label for="filter-q" class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">Cari</label>
                <div class="flex gap-2">
                    <input type="search" name="q" id="filter-q" value="{{ $filter['q'] ?? '' }}" placeholder="Nama, kode, atau OPD"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded-lg text-sm font-semibold">Cari</button>
                </div>
            </div>
        </div>
        @if ($filter)
            <div class="mt-3 text-sm">
                <a href="{{ route('arsitektur.kategori', $kategori) }}" class="text-red-600 hover:text-red-800 font-semibold">✕ Hapus semua filter</a>
            </div>
        @endif
    </form>

    {{-- Ringkasan --}}
    <div class="grid grid-cols-2 {{ $indikator ? 'lg:grid-cols-5' : 'lg:grid-cols-4' }} gap-4 mb-6">
        <div class="bg-white rounded-lg shadow-md p-4">
            <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Jumlah {{ $konfig['label'] }}</div>
            <div class="text-3xl font-bold text-gray-800 mt-1">{{ number_format($total, 0, ',', '.') }}</div>
        </div>
        @if (isset($panel['opd']))
            <div class="bg-white rounded-lg shadow-md p-4">
                <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Jumlah OPD</div>
                <div class="text-3xl font-bold text-gray-800 mt-1">{{ $daftarOpd->count() }}</div>
            </div>
        @endif
        @foreach ($indikator as $i)
            <div class="bg-white rounded-lg shadow-md p-4 border-l-4 border-green-600">
                <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide">{{ $i['label'] }}</div>
                <div class="mt-1 flex items-baseline gap-2">
                    <span class="text-3xl font-bold text-gray-800">{{ number_format($i['jumlah'], 0, ',', '.') }}</span>
                    <span class="text-lg font-semibold text-green-700">{{ number_format($i['persen'], 1, ',', '.') }}%</span>
                </div>
                <div class="mt-2 h-2 bg-gray-200 rounded-full overflow-hidden">
                    <div class="h-full bg-green-600" style="width: {{ $i['persen'] }}%"></div>
                </div>
                <div class="text-xs text-gray-500 mt-1">
                    dari {{ number_format($i['total'], 0, ',', '.') }} layanan
                    @if ($i['keterangan']) · {{ $i['keterangan'] }} @endif
                </div>
            </div>
        @endforeach
        @foreach ($status->take(2) as $s)
            <div class="bg-white rounded-lg shadow-md p-4">
                <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide">{{ $s['label'] }}</div>
                <div class="text-3xl font-bold text-gray-800 mt-1">{{ number_format($s['jumlah'], 0, ',', '.') }}</div>
            </div>
        @endforeach
    </div>

    {{-- Panel --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">
        @if (isset($panel['opd']))
            <div class="bg-white rounded-lg shadow-md p-4">
                <h2 class="text-lg font-bold text-gray-800 mb-3">{{ $panel['opd'] }}</h2>
                <div class="overflow-y-auto" style="max-height: 26rem;">
                    <table class="min-w-full text-sm">
                        <thead class="sticky top-0 bg-gray-100">
                            <tr>
                                <th class="px-3 py-2 text-left">OPD</th>
                                <th class="px-3 py-2 text-right w-20">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($daftarOpd as $opd)
                                <tr class="border-t border-gray-100 hover:bg-green-50 {{ $opd['id'] !== null && ($filter['opd'] ?? null) === (string) $opd['id'] ? 'bg-green-50 font-semibold' : '' }}">
                                    <td class="px-3 py-1.5">
                                        @if ($opd['id'] !== null)
                                            <a href="{{ $urlKategori(['opd' => $opd['id']]) }}" class="text-gray-800 hover:text-green-700">{{ $opd['nama'] }}</a>
                                        @else
                                            {{ $opd['nama'] }}
                                        @endif
                                    </td>
                                    <td class="px-3 py-1.5 text-right">{{ $opd['jumlah'] }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="2" class="px-3 py-6 text-center text-gray-500">Tidak ada data</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        @foreach ($sebaran as $kolom => $baris)
            <div class="bg-white rounded-lg shadow-md p-4">
                <h2 class="text-lg font-bold text-gray-800">{{ $panel[$kolom] }}</h2>
                <p class="text-xs text-gray-500 mb-3">
                    @if ($baris->count() > $batasGrafik)
                        {{ $batasGrafik }} terbanyak dari {{ $baris->count() }}
                    @else
                        {{ $baris->count() }} kelompok
                    @endif
                    @if ($grafik[$kolom]['bisaFilter']) · klik batang untuk memfilter @endif
                </p>
                @if ($baris->isEmpty())
                    <div class="py-10 text-center text-gray-500 text-sm">Tidak ada data</div>
                @else
                    <div style="height: {{ max(160, min($baris->count(), $batasGrafik) * 30 + 40) }}px;">
                        <canvas id="grafik-{{ $kolom }}"></canvas>
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    {{-- Katalog --}}
    <div class="bg-white rounded-lg shadow-md p-4">
        <div class="flex flex-wrap justify-between items-center gap-2 mb-3">
            <h2 class="text-lg font-bold text-gray-800">{{ $panel['katalog'] }}</h2>
            <span class="text-sm text-gray-500">{{ number_format($katalog->total(), 0, ',', '.') }} entri</span>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full table-auto border-collapse text-sm">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border border-gray-300 px-3 py-2 text-left w-12">No</th>
                        @foreach ($kolomKatalog as $label)
                            <th class="border border-gray-300 px-3 py-2 text-left">{{ $label }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse ($katalog as $item)
                        <tr class="hover:bg-gray-50 align-top">
                            <td class="border border-gray-300 px-3 py-2 text-center">{{ $katalog->firstItem() + $loop->index }}</td>
                            @foreach ($kolomKatalog as $kolom => $label)
                                <td class="border border-gray-300 px-3 py-2 {{ $kolom === 'kode' ? 'whitespace-nowrap' : '' }}">
                                    @if ($kolom === 'nama')
                                        <a href="{{ route('arsitektur.show', $item) }}" class="font-semibold text-green-700 hover:underline">{{ $item->nama }}</a>
                                    @elseif ($kolom === 'jenis')
                                        {{ $item->label_jenis }}
                                    @elseif ($kolom === 'unit_kerja')
                                        {{ $item->nama_unit_kerja ?? '-' }}
                                    @else
                                        {{ $item->getAttribute($kolom) ?? '-' }}
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($kolomKatalog) + 1 }}" class="border border-gray-300 px-3 py-8 text-center text-gray-500">Tidak ada data yang sesuai filter</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $katalog->links() }}</div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        (function () {
            const grafik = @json($grafik);
            const potong = (teks) => teks.length > 42 ? teks.slice(0, 40) + '…' : teks;

            Object.entries(grafik).forEach(([kolom, data]) => {
                const kanvas = document.getElementById('grafik-' + kolom);
                if (!kanvas) return;

                new Chart(kanvas, {
                    type: 'bar',
                    data: {
                        labels: data.label,
                        datasets: [{
                            data: data.jumlah,
                            backgroundColor: data.label.map((l) => l === data.aktif ? '#15803d' : '#4ade80'),
                            borderRadius: 4,
                        }],
                    },
                    options: {
                        indexAxis: 'y',
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: { callbacks: { title: (item) => data.label[item[0].dataIndex] } },
                        },
                        scales: {
                            x: { beginAtZero: true, ticks: { precision: 0 } },
                            y: { ticks: { callback: function (nilai) { return potong(this.getLabelForValue(nilai)); }, font: { size: 11 } } },
                        },
                        onHover: (e, el) => { e.native.target.style.cursor = el.length && data.bisaFilter ? 'pointer' : 'default'; },
                        onClick: (e, el) => {
                            if (!el.length || !data.bisaFilter) return;
                            const url = new URL(window.location.href);
                            const nilai = data.label[el[0].index];
                            if (url.searchParams.get(kolom) === nilai) {
                                url.searchParams.delete(kolom);
                            } else {
                                url.searchParams.set(kolom, nilai);
                            }
                            url.searchParams.delete('page');
                            window.location.href = url.toString();
                        },
                    },
                });
            });
        })();
    </script>
@endpush
