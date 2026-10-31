@extends('layouts.authenticated')

@section('title', '- ' . ($kegiatan->exists ? 'Ubah' : 'Buat') . ' Kegiatan')
@section('header-title', 'Presensi Kegiatan')

@section('content')
@php
    $input = 'w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500';
    $label = 'block text-sm font-semibold text-gray-700 mb-1';
@endphp
<div class="container mx-auto p-6 max-w-4xl">
    <a href="{{ $kegiatan->exists ? route('admin.presensi-kegiatan.show', $kegiatan) : route('admin.presensi-kegiatan.index') }}" class="text-sm text-green-700 hover:underline">← Kembali</a>
    <h1 class="text-2xl font-bold text-gray-800 mt-2 mb-6">{{ $kegiatan->exists ? 'Ubah Kegiatan' : 'Buat Kegiatan Baru' }}</h1>

    @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" action="{{ $kegiatan->exists ? route('admin.presensi-kegiatan.update', $kegiatan) : route('admin.presensi-kegiatan.store') }}"
          class="bg-white rounded-lg shadow-md p-6 space-y-6">
        @csrf
        @if($kegiatan->exists) @method('PUT') @endif

        <section class="space-y-4">
            <h2 class="font-bold text-gray-800 border-b pb-2">Informasi Kegiatan</h2>
            <div>
                <label class="{{ $label }}">Nama Kegiatan <span class="text-red-500">*</span></label>
                <input name="judul" value="{{ old('judul', $kegiatan->judul) }}" required class="{{ $input }}">
            </div>
            <div>
                <label class="{{ $label }}">Alamat Tautan</label>
                <div class="flex items-center">
                    <span class="px-3 py-2 bg-gray-100 border border-r-0 border-gray-300 rounded-l-lg text-sm text-gray-500 whitespace-nowrap">{{ url('kegiatan') }}/</span>
                    <input name="slug" value="{{ old('slug', $kegiatan->slug) }}" placeholder="dibuat otomatis dari nama kegiatan" class="{{ $input }} rounded-l-none">
                </div>
                @if($kegiatan->exists)
                    <p class="text-xs text-amber-600 mt-1">Mengubah alamat membuat QR Code dan tautan yang sudah disebar tidak berlaku lagi.</p>
                @endif
            </div>
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="{{ $label }}">Nomor Surat Undangan</label>
                    <input name="nomor_surat" value="{{ old('nomor_surat', $kegiatan->nomor_surat) }}" class="{{ $input }}">
                </div>
                <div>
                    <label class="{{ $label }}">Waktu</label>
                    <input name="waktu" value="{{ old('waktu', $kegiatan->waktu) }}" placeholder="09.00 WITA – Selesai" class="{{ $input }}">
                </div>
                <div>
                    <label class="{{ $label }}">Tanggal Mulai <span class="text-red-500">*</span></label>
                    <input type="date" name="tanggal_mulai" value="{{ old('tanggal_mulai', $kegiatan->tanggal_mulai?->format('Y-m-d')) }}" required class="{{ $input }}">
                </div>
                <div>
                    <label class="{{ $label }}">Tanggal Selesai <span class="text-gray-400 font-normal">(bila lebih dari sehari)</span></label>
                    <input type="date" name="tanggal_selesai" value="{{ old('tanggal_selesai', $kegiatan->tanggal_selesai?->format('Y-m-d')) }}" class="{{ $input }}">
                </div>
            </div>
            <div>
                <label class="{{ $label }}">Tempat <span class="text-red-500">*</span></label>
                <input name="tempat" value="{{ old('tempat', $kegiatan->tempat) }}" required class="{{ $input }}">
            </div>
            <div>
                <label class="{{ $label }}">Keterangan untuk Peserta</label>
                <textarea name="deskripsi" rows="3" class="{{ $input }}">{{ old('deskripsi', $kegiatan->deskripsi) }}</textarea>
            </div>
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="{{ $label }}">Penyelenggara</label>
                    <input name="penyelenggara" value="{{ old('penyelenggara', $kegiatan->penyelenggara) }}" class="{{ $input }}">
                </div>
                <div>
                    <label class="{{ $label }}">Narahubung</label>
                    <input name="kontak" value="{{ old('kontak', $kegiatan->kontak) }}" placeholder="Nama (No. HP)" class="{{ $input }}">
                </div>
            </div>
        </section>

        <section class="space-y-4">
            <h2 class="font-bold text-gray-800 border-b pb-2">Rapat Online</h2>
            <p class="text-sm text-gray-600 -mt-2">Dikirim lewat WhatsApp (Fonnte Aptika) ke peserta yang memilih <strong>Hadir online</strong>. Bila diisi, tautan langsung terkirim saat peserta mengonfirmasi.</p>
            <div>
                <label class="{{ $label }}">Tautan Rapat Online</label>
                <input type="url" name="tautan_online" value="{{ old('tautan_online', $kegiatan->tautan_online) }}" placeholder="https://zoom.us/j/..." class="{{ $input }}">
            </div>
            <div>
                <label class="{{ $label }}">Keterangan Tambahan</label>
                <textarea name="keterangan_online" rows="2" placeholder="Meeting ID: ... &#10;Passcode: ..." class="{{ $input }}">{{ old('keterangan_online', $kegiatan->keterangan_online) }}</textarea>
            </div>
        </section>

        <section class="space-y-4">
            <h2 class="font-bold text-gray-800 border-b pb-2">Pengaturan</h2>
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="{{ $label }}">Kuota Peserta per Instansi</label>
                    <input type="number" min="1" name="kuota_per_instansi" value="{{ old('kuota_per_instansi', $kegiatan->kuota_per_instansi) }}" placeholder="Tanpa batas" class="{{ $input }}">
                    <p class="text-xs text-gray-500 mt-1">Dibatasi saat konfirmasi kehadiran; presensi di lokasi tidak dibatasi.</p>
                </div>
            </div>
            <div class="space-y-2">
                @foreach([
                    'konfirmasi_aktif' => 'Buka halaman konfirmasi kehadiran',
                    'presensi_aktif' => 'Buka presensi (daftar hadir di lokasi)',
                    'wajib_tanda_tangan' => 'Wajibkan tanda tangan saat presensi',
                ] as $name => $text)
                    <label class="flex items-center gap-2">
                        <input type="hidden" name="{{ $name }}" value="0">
                        <input type="checkbox" name="{{ $name }}" value="1" @checked(old($name, $kegiatan->{$name})) class="rounded text-green-600">
                        <span class="text-sm">{{ $text }}</span>
                    </label>
                @endforeach
            </div>
        </section>

        <section class="space-y-4">
            <h2 class="font-bold text-gray-800 border-b pb-2">Penanda Tangan Daftar Hadir (SPJ)</h2>
            <div class="grid md:grid-cols-3 gap-4">
                <div>
                    <label class="{{ $label }}">Jabatan</label>
                    <input name="pj_jabatan" value="{{ old('pj_jabatan', $kegiatan->pj_jabatan) }}" placeholder="Pejabat Pelaksana Teknis Kegiatan" class="{{ $input }}">
                </div>
                <div>
                    <label class="{{ $label }}">Nama</label>
                    <input name="pj_nama" value="{{ old('pj_nama', $kegiatan->pj_nama) }}" class="{{ $input }}">
                </div>
                <div>
                    <label class="{{ $label }}">NIP</label>
                    <input name="pj_nip" value="{{ old('pj_nip', $kegiatan->pj_nip) }}" class="{{ $input }}">
                </div>
            </div>
        </section>

        <div class="flex justify-end gap-2">
            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg font-semibold">Simpan</button>
        </div>
    </form>

    @if($kegiatan->exists)
        <form method="POST" action="{{ route('admin.presensi-kegiatan.destroy', $kegiatan) }}" class="mt-6 text-right"
              onsubmit="return confirm('Hapus kegiatan ini beserta SELURUH data peserta & tanda tangan? Tindakan ini tidak dapat dibatalkan.')">
            @csrf @method('DELETE')
            <button type="submit" class="text-sm text-red-600 hover:underline">Hapus kegiatan</button>
        </form>
    @endif
</div>
@endsection
