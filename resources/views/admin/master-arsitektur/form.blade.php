@extends('layouts.authenticated')

@php
    $labelJenis = \App\Services\MasterArsitektur\Skema::label($jenis);
    $judul = ($item->exists ? 'Edit ' : 'Tambah ') . $labelJenis;
    $inti = \App\Services\MasterArsitektur\Skema::INTI;
    $kelasInput = 'w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500';
@endphp

@section('title', '- ' . $judul)
@section('header-title', 'Master Data Arsitektur')

@section('content')
<div class="container mx-auto p-6">
    <div class="max-w-5xl mx-auto">
        <div class="mb-6">
            <a href="{{ $item->exists ? route('admin.master-arsitektur.show', $item) : route('admin.master-arsitektur.kategori', $kategori) }}"
               class="text-sm text-green-700 hover:underline">← Kembali</a>
            <h1 class="text-2xl font-bold text-gray-800 mt-2">{{ $judul }}</h1>
            <p class="text-gray-600 mt-1">Kategori {{ $konfigKategori['label'] }}</p>
        </div>

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
              action="{{ $item->exists ? route('admin.master-arsitektur.update', $item) : route('admin.master-arsitektur.store', $kategori) }}">
            @csrf
            @if ($item->exists)
                @method('PUT')
            @else
                <input type="hidden" name="jenis" value="{{ $jenis }}">
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                @foreach ($kolom as $kunci => $definisi)
                    @php
                        $tipe = $definisi['tipe'] ?? 'text';
                        $kolomInti = in_array($kunci, $inti, true);
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
                            <select name="unit_kerja_id" id="f-{{ $kunci }}" class="{{ $kelasInput }}">
                                <option value="">— Pilih dari Master Data Unit Kerja —</option>
                                @foreach ($instansi as $tipeInstansi => $daftar)
                                    <optgroup label="{{ $tipeInstansi ?: 'Lainnya' }}">
                                        @foreach ($daftar as $unit)
                                            <option value="{{ $unit->id }}" @selected((string) old('unit_kerja_id', $item->unit_kerja_id) === (string) $unit->id)>{{ $unit->nama }}</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                            @if ($item->exists && $item->unit_kerja_id === null && $item->unit_kerja_teks !== null)
                                <p class="text-xs text-orange-600 mt-1">
                                    Tertulis pada sumber: <strong>{{ $item->unit_kerja_teks }}</strong> — belum terpetakan ke Master Data Unit Kerja.
                                </p>
                            @endif
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
            </div>

            <div class="mt-6 flex gap-3">
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg font-semibold">Simpan</button>
                <a href="{{ $item->exists ? route('admin.master-arsitektur.show', $item) : route('admin.master-arsitektur.kategori', $kategori) }}"
                   class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-6 py-2 rounded-lg font-semibold">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
