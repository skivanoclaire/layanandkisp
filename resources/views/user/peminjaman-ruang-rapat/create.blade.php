@extends('layouts.authenticated')
@section('title', '- Ajukan Peminjaman Ruang Rapat')
@section('header-title', 'Ajukan Peminjaman Ruang Rapat')

@section('content')
<div class="max-w-4xl mx-auto">
    <h1 class="text-2xl font-bold mb-6 text-teal-700">Formulir Peminjaman Ruang Rapat</h1>

    <div class="mb-6 bg-blue-50 border-l-4 border-blue-500 rounded-lg p-4 text-sm text-blue-700">
        Permohonan hanya dapat diajukan untuk tanggal hari ini atau setelahnya. Ruang yang sudah disetujui pada jam yang sama tidak dapat dipilih.
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <form action="{{ route('user.peminjaman-ruang-rapat.store') }}" method="POST">
            @csrf

            <!-- Informasi Pemohon -->
            <div class="mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Informasi Pemohon</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Lengkap</label>
                        <input type="text" value="{{ auth()->user()->name }}" disabled
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">NIP</label>
                        <input type="text" value="{{ auth()->user()->nip }}" disabled
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Unit Kerja</label>
                    <input type="text" value="{{ auth()->user()->unitKerja?->nama ?? '-' }}" disabled
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100">
                    <p class="text-xs text-gray-500 mt-1">Unit Kerja diambil dari data akun Anda</p>
                </div>

                <div class="mb-4">
                    <label for="no_hp" class="block text-sm font-semibold text-gray-700 mb-2">
                        No. HP/WhatsApp <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="no_hp" name="no_hp" value="{{ old('no_hp', auth()->user()->phone) }}" required
                           placeholder="Contoh: 081234567890"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 @error('no_hp') border-red-500 @enderror">
                    @error('no_hp')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Detail Peminjaman -->
            <div class="mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Detail Peminjaman</h2>

                <div class="mb-4">
                    <label for="meeting_room_id" class="block text-sm font-semibold text-gray-700 mb-2">
                        Ruang Rapat <span class="text-red-500">*</span>
                    </label>
                    <select id="meeting_room_id" name="meeting_room_id" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 @error('meeting_room_id') border-red-500 @enderror">
                        <option value="">-- Pilih ruang rapat --</option>
                        @foreach($rooms as $room)
                            <option value="{{ $room->id }}" data-kapasitas="{{ $room->kapasitas }}" @selected(old('meeting_room_id') == $room->id)>
                                {{ $room->nama }} (kapasitas {{ $room->kapasitas }} orang)
                            </option>
                        @endforeach
                    </select>
                    <div id="room-info" class="hidden mt-2 text-sm text-gray-600 bg-gray-50 border rounded-lg p-3"></div>
                    @error('meeting_room_id')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="keperluan" class="block text-sm font-semibold text-gray-700 mb-2">
                        Keperluan <span class="text-red-500">*</span>
                    </label>
                    <textarea id="keperluan" name="keperluan" rows="3" required
                              placeholder="Contoh: Rapat koordinasi pengembangan aplikasi bersama tim"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 @error('keperluan') border-red-500 @enderror">{{ old('keperluan') }}</textarea>
                    @error('keperluan')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="jumlah_peserta" class="block text-sm font-semibold text-gray-700 mb-2">
                        Jumlah Peserta <span class="text-red-500">*</span>
                    </label>
                    <input type="number" id="jumlah_peserta" name="jumlah_peserta" value="{{ old('jumlah_peserta') }}" min="1" required
                           class="w-full md:w-1/2 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 @error('jumlah_peserta') border-red-500 @enderror">
                    @error('jumlah_peserta')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div>
                        <label for="tanggal" class="block text-sm font-semibold text-gray-700 mb-2">
                            Tanggal <span class="text-red-500">*</span>
                        </label>
                        <input type="date" id="tanggal" name="tanggal" value="{{ old('tanggal') }}" min="{{ date('Y-m-d') }}" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 @error('tanggal') border-red-500 @enderror">
                        @error('tanggal')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="jam_mulai" class="block text-sm font-semibold text-gray-700 mb-2">
                            Jam Mulai <span class="text-red-500">*</span>
                        </label>
                        <input type="time" id="jam_mulai" name="jam_mulai" value="{{ old('jam_mulai') }}" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 @error('jam_mulai') border-red-500 @enderror">
                        @error('jam_mulai')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="jam_selesai" class="block text-sm font-semibold text-gray-700 mb-2">
                            Jam Selesai <span class="text-red-500">*</span>
                        </label>
                        <input type="time" id="jam_selesai" name="jam_selesai" value="{{ old('jam_selesai') }}" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 @error('jam_selesai') border-red-500 @enderror">
                        @error('jam_selesai')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="bg-teal-600 hover:bg-teal-700 text-white px-6 py-2 rounded-lg font-semibold">
                    Kirim Permohonan
                </button>
                <a href="{{ route('user.peminjaman-ruang-rapat.index') }}"
                   class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-2 rounded-lg font-semibold">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const select = document.getElementById('meeting_room_id');
    const info = document.getElementById('room-info');
    const rooms = @json($rooms->mapWithKeys(fn ($r) => [$r->id => ['nama' => $r->nama, 'lokasi' => $r->lokasi, 'fasilitas' => $r->fasilitas, 'kapasitas' => $r->kapasitas]]));

    function tampilkanInfo() {
        const room = rooms[select.value];
        if (!room) {
            info.classList.add('hidden');
            info.textContent = '';
            return;
        }
        const baris = ['Lokasi: ' + (room.lokasi || '-'), 'Kapasitas: ' + room.kapasitas + ' orang'];
        if (room.fasilitas) {
            baris.push('Fasilitas: ' + room.fasilitas);
        }
        info.textContent = baris.join(' · ');
        info.classList.remove('hidden');
    }

    select.addEventListener('change', tampilkanInfo);
    tampilkanInfo();
});
</script>
@endsection
