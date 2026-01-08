@extends('layouts.authenticated')
@section('title', '- Edit Permohonan Video Conference')
@section('header-title', 'Edit Permohonan Video Conference')

@section('content')
<div class="max-w-4xl mx-auto">
    <h1 class="text-2xl font-bold mb-6 text-purple-700">
        Edit Permohonan Video Conference
        @if($vidconRequest->status === 'selesai')
            - Detail & Link Meeting
        @endif
    </h1>

    @if($vidconRequest->status === 'selesai')
        <!-- Meeting Information (Read-only for completed requests) -->
        <div class="bg-green-50 border-l-4 border-green-500 rounded-lg p-6 mb-6">
            <h2 class="text-lg font-semibold text-green-800 mb-4">Informasi Meeting</h2>

            @if($vidconRequest->link_meeting)
                <div class="mb-3">
                    <label class="block text-sm font-semibold text-green-700 mb-1">Link Meeting:</label>
                    <a href="{{ $vidconRequest->link_meeting }}" target="_blank"
                       class="text-blue-600 hover:text-blue-800 underline break-all">
                        {{ $vidconRequest->link_meeting }}
                    </a>
                </div>
            @endif

            @if($vidconRequest->meeting_id)
                <div class="mb-3">
                    <label class="block text-sm font-semibold text-green-700 mb-1">Meeting ID:</label>
                    <p class="text-gray-800">{{ $vidconRequest->meeting_id }}</p>
                </div>
            @endif

            @if($vidconRequest->meeting_password)
                <div class="mb-3">
                    <label class="block text-sm font-semibold text-green-700 mb-1">Password:</label>
                    <p class="text-gray-800">{{ $vidconRequest->meeting_password }}</p>
                </div>
            @endif

            @if($vidconRequest->informasi_tambahan)
                <div class="mb-3">
                    <label class="block text-sm font-semibold text-green-700 mb-1">Informasi Tambahan:</label>
                    <p class="text-gray-800">{{ $vidconRequest->informasi_tambahan }}</p>
                </div>
            @endif
            @if($vidconRequest->operators->count() > 0)
                <div class="mb-3">
                    <label class="block text-sm font-semibold text-green-700 mb-1">Operator Ditugaskan:</label>
                    <ul class="list-disc list-inside text-gray-800">
                        @foreach($vidconRequest->operators as $operator)
                            <li>{{ $operator->name }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-md p-6">
        @if($vidconRequest->status === 'selesai')
            <!-- Read-only view for completed requests -->
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Tiket:</label>
                    <p class="text-gray-800">{{ $vidconRequest->ticket_no }}</p>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Judul Kegiatan:</label>
                    <p class="text-gray-800">{{ $vidconRequest->judul_kegiatan }}</p>
                </div>

                @if($vidconRequest->deskripsi_kegiatan)
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Deskripsi:</label>
                        <p class="text-gray-800">{{ $vidconRequest->deskripsi_kegiatan }}</p>
                    </div>
                @endif

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Tanggal Mulai:</label>
                        <p class="text-gray-800">{{ $vidconRequest->tanggal_mulai->format('d/m/Y') }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Tanggal Selesai:</label>
                        <p class="text-gray-800">{{ $vidconRequest->tanggal_selesai->format('d/m/Y') }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Jam Mulai:</label>
                        <p class="text-gray-800">{{ $vidconRequest->jam_mulai }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Jam Selesai:</label>
                        <p class="text-gray-800">{{ $vidconRequest->jam_selesai }}</p>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Platform:</label>
                    <p class="text-gray-800">{{ $vidconRequest->platform_display }}</p>
                </div>

                @if($vidconRequest->jumlah_peserta)
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Jumlah Peserta:</label>
                        <p class="text-gray-800">{{ $vidconRequest->jumlah_peserta }} orang</p>
                    </div>
                @endif

                @if($vidconRequest->keperluan_khusus)
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Keperluan Khusus:</label>
                        <p class="text-gray-800">{{ $vidconRequest->keperluan_khusus }}</p>
                    </div>
                @endif
            </div>

            <div class="mt-6">
                <a href="{{ route('user.vidcon.index') }}"
                   class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-2 rounded-lg font-semibold">
                    Kembali
                </a>
            </div>
        @else
            <!-- Editable form for pending requests -->
            <form action="{{ route('user.vidcon.update', $vidconRequest) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label for="unit_kerja_id" class="block text-sm font-semibold text-gray-700 mb-2">
                        Instansi <span class="text-red-500">*</span>
                    </label>
                    <select id="unit_kerja_id" name="unit_kerja_id" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                        <option value="">-- Pilih Instansi --</option>
                        @foreach($unitKerjaList as $uk)
                            <option value="{{ $uk->id }}" {{ old('unit_kerja_id', $vidconRequest->unit_kerja_id) == $uk->id ? 'selected' : '' }}>
                                {{ $uk->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-4">
                    <label for="no_hp" class="block text-sm font-semibold text-gray-700 mb-2">
                        No. HP/WhatsApp <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="no_hp" name="no_hp" required
                           value="{{ old('no_hp', $vidconRequest->no_hp) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                </div>

                <div class="mb-4">
                    <label for="judul_kegiatan" class="block text-sm font-semibold text-gray-700 mb-2">
                        Judul Kegiatan <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="judul_kegiatan" name="judul_kegiatan" required
                           value="{{ old('judul_kegiatan', $vidconRequest->judul_kegiatan) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                </div>

                <div class="mb-4">
                    <label for="deskripsi_kegiatan" class="block text-sm font-semibold text-gray-700 mb-2">
                        Deskripsi Kegiatan
                    </label>
                    <textarea id="deskripsi_kegiatan" name="deskripsi_kegiatan" rows="3"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">{{ old('deskripsi_kegiatan', $vidconRequest->deskripsi_kegiatan) }}</textarea>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="tanggal_mulai" class="block text-sm font-semibold text-gray-700 mb-2">
                            Tanggal Mulai <span class="text-red-500">*</span>
                        </label>
                        <input type="date" id="tanggal_mulai" name="tanggal_mulai" required
                               value="{{ old('tanggal_mulai', $vidconRequest->tanggal_mulai->format('Y-m-d')) }}"
                               min="{{ date('Y-m-d') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div>
                        <label for="tanggal_selesai" class="block text-sm font-semibold text-gray-700 mb-2">
                            Tanggal Selesai <span class="text-red-500">*</span>
                        </label>
                        <input type="date" id="tanggal_selesai" name="tanggal_selesai" required
                               value="{{ old('tanggal_selesai', $vidconRequest->tanggal_selesai->format('Y-m-d')) }}"
                               min="{{ date('Y-m-d') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="jam_mulai" class="block text-sm font-semibold text-gray-700 mb-2">
                            Jam Mulai <span class="text-red-500">*</span>
                        </label>
                        <input type="time" id="jam_mulai" name="jam_mulai" required
                               value="{{ old('jam_mulai', $vidconRequest->jam_mulai) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div>
                        <label for="jam_selesai" class="block text-sm font-semibold text-gray-700 mb-2">
                            Jam Selesai <span class="text-red-500">*</span>
                        </label>
                        <input type="time" id="jam_selesai" name="jam_selesai" required
                               value="{{ old('jam_selesai', $vidconRequest->jam_selesai) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                    </div>
                </div>

                <div class="mb-4">
                    <label for="platform" class="block text-sm font-semibold text-gray-700 mb-2">
                        Platform <span class="text-red-500">*</span>
                    </label>
                    <select id="platform" name="platform" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                        <option value="">-- Pilih Platform --</option>
                        @foreach(['Zoom', 'Google Meet', 'Microsoft Teams', 'YouTube Live', 'Lainnya'] as $plat)
                            <option value="{{ $plat }}" {{ old('platform', $vidconRequest->platform) == $plat ? 'selected' : '' }}>
                                {{ $plat }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div id="platform_lainnya_wrapper" style="display: none;" class="mb-4">
                    <label for="platform_lainnya" class="block text-sm font-semibold text-gray-700 mb-2">
                        Nama Platform Lain
                    </label>
                    <input type="text" id="platform_lainnya" name="platform_lainnya"
                           value="{{ old('platform_lainnya', $vidconRequest->platform_lainnya) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                </div>

                <div class="mb-4">
                    <label for="jumlah_peserta" class="block text-sm font-semibold text-gray-700 mb-2">
                        Estimasi Jumlah Peserta
                    </label>
                    <input type="number" id="jumlah_peserta" name="jumlah_peserta" min="1"
                           value="{{ old('jumlah_peserta', $vidconRequest->jumlah_peserta) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                </div>

                <div class="mb-4">
                    <label for="keperluan_khusus" class="block text-sm font-semibold text-gray-700 mb-2">
                        Keperluan Khusus
                    </label>
                    <textarea id="keperluan_khusus" name="keperluan_khusus" rows="3"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">{{ old('keperluan_khusus', $vidconRequest->keperluan_khusus) }}</textarea>
                </div>

                <div class="flex gap-3">
                    <button type="submit"
                            class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded-lg font-semibold">
                        Update
                    </button>
                    <a href="{{ route('user.vidcon.index') }}"
                       class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-2 rounded-lg font-semibold">
                        Batal
                    </a>
                </div>
            </form>

            <script>
            document.addEventListener('DOMContentLoaded', function() {
                const platformSelect = document.getElementById('platform');
                const platformLainnyaWrapper = document.getElementById('platform_lainnya_wrapper');

                function togglePlatformLainnya() {
                    platformLainnyaWrapper.style.display = platformSelect.value === 'Lainnya' ? 'block' : 'none';
                }

                platformSelect.addEventListener('change', togglePlatformLainnya);
                togglePlatformLainnya();
            });
            </script>
        @endif
    </div>
</div>
@endsection
