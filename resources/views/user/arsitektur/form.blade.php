@extends('layouts.authenticated')

@php
    $labelJenis = \App\Services\MasterArsitektur\Skema::label($jenis);
    $judul = ($item->exists ? 'Usulkan Perubahan ' : 'Usulkan ') . $labelJenis . ($item->exists ? '' : ' Baru');
    $batas = \App\Services\MasterArsitektur\IsianItem::BATAS;
    $kelasInput = 'w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500';
@endphp

@section('title', '- ' . $judul)
@section('header-title', 'Pembaruan Data Arsitektur')

@section('content')
<div class="container mx-auto p-6">
    <div class="max-w-5xl mx-auto">
        <div class="mb-6">
            <a href="{{ route('user.arsitektur.index', ['kategori' => $kategori]) }}" class="text-sm text-green-700 hover:underline">← Kembali</a>
            <h1 class="text-2xl font-bold text-gray-800 mt-2">{{ $judul }}</h1>
            <p class="text-gray-600 mt-1">Kategori {{ $konfigKategori['label'] }} · {{ $unit->nama }}</p>
        </div>

        <div class="bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3 rounded mb-4 text-sm">
            Isian ini dikirim sebagai usulan. Data arsitektur baru berubah setelah usulan disetujui admin.
        </div>

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

        <form method="POST" class="bg-white rounded-lg shadow-md p-6"
              action="{{ $item->exists ? route('user.arsitektur.ubah.simpan', $item) : route('user.arsitektur.tambah.simpan', $kategori) }}">
            @csrf
            @unless ($item->exists)
                <input type="hidden" name="jenis" value="{{ $jenis }}">
            @endunless

            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                @foreach ($kolom as $kunci => $definisi)
                    @php
                        $tipe = $definisi['tipe'] ?? 'text';
                        $kolomInti = isset($batas[$kunci]);
                        $name = $kolomInti ? $kunci : "atribut[$kunci]";
                        $kunciOld = $kolomInti ? $kunci : "atribut.$kunci";
                        $nilai = old($kunciOld, $kolomInti ? $item->getAttribute($kunci) : ($item->atribut[$kunci] ?? null));
                        $lebar = $tipe === 'textarea' || $kunci === 'nama' || $kunci === 'unit_kerja';
                    @endphp
                    <div class="{{ $lebar ? 'md:col-span-2' : '' }}">
                        <label for="f-{{ $kunci }}" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ $definisi['label'] }}
                            @if ($kunci === 'nama') <span class="text-red-500">*</span> @endif
                        </label>

                        @if ($kunci === 'unit_kerja')
                            <input type="text" id="f-{{ $kunci }}" value="{{ $unit->nama }}" disabled
                                   class="{{ $kelasInput }} bg-gray-100 text-gray-600">
                            <p class="text-xs text-gray-500 mt-1">Mengikuti unit kerja akun Anda dan tidak dapat diubah.</p>
                        @elseif ($tipe === 'pilihan')
                            @include('admin.master-arsitektur._pilihan')
                        @elseif ($tipe === 'textarea')
                            <textarea name="{{ $name }}" id="f-{{ $kunci }}" rows="3" class="{{ $kelasInput }}">{{ $nilai }}</textarea>
                        @else
                            <input type="text" name="{{ $name }}" id="f-{{ $kunci }}" value="{{ $nilai }}"
                                   @if (isset($saran[$kunci])) list="saran-{{ $kunci }}" autocomplete="off" @endif
                                   @if ($kunci === 'nama') required @endif
                                   class="{{ $kelasInput }}">
                            @if (isset($saran[$kunci]))
                                <datalist id="saran-{{ $kunci }}">
                                    @foreach ($saran[$kunci] as $s)
                                        <option value="{{ $s }}"></option>
                                    @endforeach
                                </datalist>
                            @endif
                        @endif
                    </div>
                @endforeach

                <div class="md:col-span-2 border-t border-gray-200 pt-4">
                    <label for="f-catatan-pemohon" class="block text-sm font-medium text-gray-700 mb-1">Catatan untuk admin (opsional)</label>
                    <textarea name="catatan_pemohon" id="f-catatan-pemohon" rows="2" maxlength="1000"
                              placeholder="Mis. alasan perubahan atau dasar pembaruan data"
                              class="{{ $kelasInput }}">{{ old('catatan_pemohon') }}</textarea>
                </div>
            </div>

            <div class="mt-6 flex gap-3">
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg font-semibold">Kirim Usulan</button>
                <a href="{{ route('user.arsitektur.index', ['kategori' => $kategori]) }}"
                   class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-6 py-2 rounded-lg font-semibold">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
