@extends('layouts.authenticated')

@section('title', '- Impor Master Data Arsitektur')
@section('header-title', 'Master Data Arsitektur')

@php
    $hasil = session('hasil_impor');
    $modePerbarui = \App\Services\MasterArsitektur\Importer::MODE_PERBARUI;
    $modeGanti = \App\Services\MasterArsitektur\Importer::MODE_GANTI;
@endphp

@section('content')
<div class="container mx-auto p-6">
    <div class="max-w-5xl mx-auto">
        <div class="mb-6">
            <a href="{{ route('admin.master-arsitektur.index') }}" class="text-sm text-green-700 hover:underline">← Kembali ke dasbor</a>
            <h1 class="text-2xl font-bold text-gray-800 mt-2">Impor Excel/CSV</h1>
            <p class="text-gray-600 mt-1">Perbarui Master Data Arsitektur dari berkas Excel (.xlsx/.xls) atau CSV</p>
        </div>

        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <ul class="list-disc list-inside text-sm">
                    @foreach ($errors->all() as $galat)
                        <li>{{ $galat }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($hasil)
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h2 class="text-lg font-bold text-gray-800 mb-3">Hasil Impor</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm border-collapse">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="border border-gray-300 px-3 py-2 text-left">Jenis Data</th>
                                <th class="border border-gray-300 px-3 py-2 text-left">Sheet</th>
                                <th class="border border-gray-300 px-3 py-2 text-right">Dibaca</th>
                                <th class="border border-gray-300 px-3 py-2 text-right">Ditambah</th>
                                <th class="border border-gray-300 px-3 py-2 text-right">Diperbarui</th>
                                <th class="border border-gray-300 px-3 py-2 text-right">Dihapus</th>
                                <th class="border border-gray-300 px-3 py-2 text-right">Baris Kembar</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($hasil as $h)
                                <tr>
                                    <td class="border border-gray-300 px-3 py-2 font-semibold">{{ $h['label'] }}</td>
                                    <td class="border border-gray-300 px-3 py-2">{{ $h['sheet'] }}</td>
                                    @if ($h['galat'])
                                        <td colspan="5" class="border border-gray-300 px-3 py-2 text-red-600">{{ $h['galat'] }}</td>
                                    @else
                                        <td class="border border-gray-300 px-3 py-2 text-right">{{ $h['dibaca'] }}</td>
                                        <td class="border border-gray-300 px-3 py-2 text-right">{{ $h['ditambah'] }}</td>
                                        <td class="border border-gray-300 px-3 py-2 text-right">{{ $h['diperbarui'] }}</td>
                                        <td class="border border-gray-300 px-3 py-2 text-right">{{ $h['dihapus'] }}</td>
                                        <td class="border border-gray-300 px-3 py-2 text-right">{{ $h['duplikat'] }}</td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <p class="text-xs text-gray-500 mt-2">Baris kembar = baris pada berkas yang kolom kuncinya sama persis dengan baris lain; hanya baris terakhir yang disimpan.</p>

                @php
                    $tanpaInstansi = [];
                    foreach ($hasil as $h) {
                        foreach ($h['tanpa_instansi'] as $nama => $jumlah) {
                            $tanpaInstansi[$nama] = ($tanpaInstansi[$nama] ?? 0) + $jumlah;
                        }
                    }
                    arsort($tanpaInstansi);
                @endphp
                @if ($tanpaInstansi)
                    <div class="mt-4 bg-orange-50 border border-orange-200 rounded p-4">
                        <h3 class="font-semibold text-orange-800">Nama perangkat daerah yang tidak ditemukan di Master Data Unit Kerja</h3>
                        <p class="text-sm text-orange-700 mt-1">
                            Datanya tetap tersimpan dengan nama aslinya dan bertanda "belum terpetakan". Petakan lewat menu Edit,
                            atau daftarkan padanan namanya di <code>config/master_arsitektur.php</code> (alias_instansi) lalu impor ulang.
                        </p>
                        <ul class="mt-2 text-sm text-orange-900 list-disc list-inside">
                            @foreach ($tanpaInstansi as $nama => $jumlah)
                                <li>{{ $nama }} <span class="text-orange-600">({{ $jumlah }} baris)</span></li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        @endif

        <form method="POST" action="{{ route('admin.master-arsitektur.impor') }}" enctype="multipart/form-data" class="bg-white rounded-lg shadow-md p-6">
            @csrf

            <div class="mb-5">
                <label for="berkas" class="block text-sm font-medium text-gray-700 mb-1">Berkas <span class="text-red-500">*</span></label>
                <input type="file" name="berkas" id="berkas" accept=".xlsx,.xls,.csv" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                <p class="text-xs text-gray-500 mt-1">Maksimal 50 MB. Kolom dicocokkan lewat judul kolom, jadi urutan kolom bebas dan kolom yang tidak dikenali diabaikan.</p>
            </div>

            <div class="mb-5">
                <label for="jenis" class="block text-sm font-medium text-gray-700 mb-1">Jenis Data</label>
                <select name="jenis" id="jenis" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    <option value="">Otomatis — semua sheet yang dikenali (berkas Excel lengkap)</option>
                    @foreach ($semuaKategori as $k)
                        <optgroup label="{{ $k['label'] }}">
                            @foreach ($k['jenis'] as $j)
                                <option value="{{ $j }}" @selected(old('jenis') === $j)>{{ $semuaJenis[$j]['label'] }} (sheet: {{ $semuaJenis[$j]['sheet'] }})</option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
                <p class="text-xs text-gray-500 mt-1">Untuk CSV, atau Excel yang nama sheet-nya berbeda, pilih jenis datanya. Bila sheet bernama sesuai tidak ada, sheet pertama yang dipakai.</p>
            </div>

            <div class="mb-6">
                <span class="block text-sm font-medium text-gray-700 mb-2">Cara Impor <span class="text-red-500">*</span></span>
                <label class="flex items-start gap-2 mb-2 cursor-pointer">
                    <input type="radio" name="mode" value="{{ $modePerbarui }}" class="mt-1 text-green-600 focus:ring-green-500" @checked(old('mode', $modePerbarui) === $modePerbarui)>
                    <span class="text-sm">
                        <strong>Perbarui &amp; tambah</strong> — baris yang sudah ada (kode/ID dan nama sama) diperbarui, baris baru ditambahkan.
                        Data yang tidak ada di berkas tetap dipertahankan.
                    </span>
                </label>
                <label class="flex items-start gap-2 cursor-pointer">
                    <input type="radio" name="mode" value="{{ $modeGanti }}" class="mt-1 text-red-600 focus:ring-red-500" @checked(old('mode') === $modeGanti)>
                    <span class="text-sm">
                        <strong class="text-red-700">Ganti seluruhnya</strong> — data lama pada jenis data yang diimpor <u>dihapus</u>, lalu diisi ulang dari berkas.
                        Jenis data lain tidak tersentuh.
                    </span>
                </label>
            </div>

            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-semibold"
                    onclick="if (this.form.mode.value === '{{ $modeGanti }}' && !confirm('Data lama pada jenis data yang diimpor akan dihapus. Lanjutkan?')) return false;">
                Impor
            </button>
        </form>

        <div class="mt-6 bg-gray-50 border border-gray-200 rounded-lg p-4 text-sm text-gray-700">
            <h3 class="font-semibold text-gray-800 mb-1">Templat</h3>
            <p>Gunakan tombol <strong>Ekspor Excel</strong> pada tiap tab dasbor untuk mendapatkan berkas dengan nama sheet dan judul kolom yang sesuai, ubah isinya, lalu impor kembali di sini.</p>
        </div>
    </div>
</div>
@endsection
