@extends('layouts.authenticated')
@section('title', '- Master Ruang Rapat')
@section('header-title', 'Master Ruang Rapat')

@section('content')
<div class="container mx-auto px-4">
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-teal-700">Master Ruang Rapat</h1>
            <p class="text-gray-600 mt-1">Daftar ruang yang tersedia untuk dipinjam. Ruang yang dinonaktifkan tidak dapat dipilih pemohon.</p>
        </div>
        <a href="{{ route('admin.peminjaman-ruang-rapat.index') }}"
           class="px-4 py-2 rounded-lg font-semibold bg-gray-200 hover:bg-gray-300 text-gray-800 text-center">
            &larr; Permohonan Peminjaman
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded border border-green-300 bg-green-50 p-3 text-sm text-green-800">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="mb-4 rounded border border-red-300 bg-red-50 p-3 text-sm text-red-800">{{ session('error') }}</div>
    @endif

    @if($errors->any())
        <div class="mb-4 rounded border border-red-300 bg-red-50 p-3 text-sm text-red-800">
            <ul class="list-disc pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Tambah ruang baru -->
    <div class="bg-white rounded shadow p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Tambah Ruang Rapat</h2>
        <form action="{{ route('admin.ruang-rapat.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-12 gap-3">
            @csrf
            <input type="text" name="nama" value="{{ old('nama') }}" required placeholder="Nama ruang"
                   class="md:col-span-3 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500">
            <input type="text" name="lokasi" value="{{ old('lokasi') }}" placeholder="Lokasi (gedung/lantai)"
                   class="md:col-span-3 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500">
            <input type="number" name="kapasitas" value="{{ old('kapasitas') }}" min="1" required placeholder="Kapasitas"
                   class="md:col-span-2 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500">
            <input type="text" name="fasilitas" value="{{ old('fasilitas') }}" placeholder="Fasilitas (mis. proyektor, AC)"
                   class="md:col-span-3 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500">
            <button type="submit" class="md:col-span-1 bg-teal-600 hover:bg-teal-700 text-white px-4 py-2 rounded-lg font-semibold">
                Tambah
            </button>
            <label class="md:col-span-12 flex items-center gap-2 text-sm text-gray-700">
                <input type="checkbox" name="is_active" value="1" checked class="rounded border-gray-300 text-teal-600">
                Aktif (dapat dipinjam)
            </label>
        </form>
    </div>

    @if($rooms->isEmpty())
        <div class="bg-white rounded shadow p-8 text-center text-gray-600">Belum ada ruang rapat.</div>
    @else
        <div class="space-y-4">
            @foreach($rooms as $room)
            <div class="bg-white rounded shadow p-4">
                <form action="{{ route('admin.ruang-rapat.update', $room->id) }}" method="POST"
                      class="grid grid-cols-1 md:grid-cols-12 gap-3 items-center">
                    @csrf
                    @method('PUT')
                    <input type="text" name="nama" value="{{ $room->nama }}" required
                           class="md:col-span-3 px-3 py-2 border border-gray-300 rounded-lg">
                    <input type="text" name="lokasi" value="{{ $room->lokasi }}" placeholder="Lokasi"
                           class="md:col-span-3 px-3 py-2 border border-gray-300 rounded-lg">
                    <input type="number" name="kapasitas" value="{{ $room->kapasitas }}" min="1" required
                           class="md:col-span-1 px-3 py-2 border border-gray-300 rounded-lg">
                    <input type="text" name="fasilitas" value="{{ $room->fasilitas }}" placeholder="Fasilitas"
                           class="md:col-span-3 px-3 py-2 border border-gray-300 rounded-lg">
                    <label class="md:col-span-1 flex items-center gap-1 text-sm">
                        <input type="checkbox" name="is_active" value="1" {{ $room->is_active ? 'checked' : '' }} class="rounded border-gray-300 text-teal-600">
                        Aktif
                    </label>
                    <div class="md:col-span-1 flex gap-2">
                        <button type="submit" class="bg-teal-600 hover:bg-teal-700 text-white px-3 py-2 rounded-lg text-sm font-semibold">Simpan</button>
                    </div>
                </form>
                <div class="mt-2 flex items-center justify-between text-xs text-gray-500">
                    <span>{{ $room->bookings_count }} riwayat peminjaman</span>
                    <form action="{{ route('admin.ruang-rapat.destroy', $room->id) }}" method="POST"
                          onsubmit="return confirm('Hapus ruang {{ addslashes($room->nama) }}? Tindakan ini tidak dapat dibatalkan.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-900 font-medium">Hapus</button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
