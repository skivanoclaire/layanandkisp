@extends('layouts.authenticated')

@section('title', '- Pengaturan SLA')
@section('header-title', 'Manajemen SLA')

@section('content')
    <div class="max-w-5xl mx-auto">
        <div class="flex items-center gap-2 text-sm mb-4">
            <a href="{{ route('admin.sla.index') }}" class="text-blue-600 hover:underline">&larr; Kembali ke Dashboard SLA</a>
        </div>

        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Pengaturan SLA</h1>
            <p class="text-sm text-gray-600 mt-1">
                Atur target SLA per layanan, jam/hari kerja, dan daftar libur nasional yang dipakai untuk menghitung durasi kerja.
            </p>
        </div>

        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                {{ session('success') }}
            </div>
        @endif
        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Jam & Hari Kerja --}}
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-1">Jam & Hari Kerja</h2>
            <p class="text-sm text-gray-500 mb-4">
                Dipakai untuk menghitung durasi kerja seluruh layanan (mengecualikan di luar jam ini & hari libur).
            </p>
            <form action="{{ route('admin.sla.pengaturan.jam-kerja') }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div class="flex flex-wrap gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Jam Mulai</label>
                        <input type="time" name="jam_mulai"
                            value="{{ old('jam_mulai', substr($workingHours->jam_mulai, 0, 5)) }}"
                            class="border border-gray-300 rounded px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Jam Selesai</label>
                        <input type="time" name="jam_selesai"
                            value="{{ old('jam_selesai', substr($workingHours->jam_selesai, 0, 5)) }}"
                            class="border border-gray-300 rounded px-3 py-2 text-sm">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-2">Hari Kerja</label>
                    <div class="flex flex-wrap gap-3">
                        @foreach (['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'] as $i => $nama)
                            @php $dayNum = $i + 1; @endphp
                            <label class="inline-flex items-center gap-1 text-sm">
                                <input type="checkbox" name="hari_kerja[]" value="{{ $dayNum }}"
                                    {{ in_array($dayNum, old('hari_kerja', $workingHours->hari_kerja ?? [1,2,3,4,5])) ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                {{ $nama }}
                            </label>
                        @endforeach
                    </div>
                </div>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2 rounded">
                    Simpan Jam Kerja
                </button>
            </form>
        </div>

        {{-- Libur Nasional --}}
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-1">Libur Nasional & Cuti Bersama</h2>
            <p class="text-sm text-gray-500 mb-4">
                Tanggal di daftar ini dikecualikan dari perhitungan durasi kerja SLA.
            </p>

            <form action="{{ route('admin.sla.pengaturan.libur.store') }}" method="POST" class="flex flex-wrap items-end gap-3 mb-5">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Tanggal</label>
                    <input type="date" name="tanggal" value="{{ old('tanggal') }}" required
                        class="border border-gray-300 rounded px-3 py-2 text-sm">
                </div>
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Keterangan</label>
                    <input type="text" name="keterangan" value="{{ old('keterangan') }}" placeholder="mis. Hari Kemerdekaan RI"
                        class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
                </div>
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white text-sm font-semibold px-4 py-2 rounded">
                    Tambah
                </button>
            </form>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500 border-b">
                            <th class="py-2 pr-4 font-semibold">Tanggal</th>
                            <th class="py-2 pr-4 font-semibold">Keterangan</th>
                            <th class="py-2 font-semibold text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse ($holidays as $holiday)
                            <tr>
                                <td class="py-2 pr-4">{{ $holiday->tanggal->format('d M Y') }}</td>
                                <td class="py-2 pr-4 text-gray-600">{{ $holiday->keterangan ?? '—' }}</td>
                                <td class="py-2 text-right">
                                    <form action="{{ route('admin.sla.pengaturan.libur.destroy', $holiday) }}" method="POST"
                                        onsubmit="return confirm('Hapus tanggal libur ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:underline text-xs font-semibold">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-4 text-center text-gray-400">Belum ada tanggal libur terdaftar.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Target SLA per layanan --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-1">Target SLA per Layanan</h2>
            <p class="text-sm text-gray-500 mb-4">
                Target waktu penyelesaian end-to-end (dari permohonan masuk sampai selesai/ditolak) per layanan.
            </p>

            @foreach ($settings as $groupName => $groupSettings)
                <div class="mb-6 last:mb-0">
                    <h3 class="text-sm font-semibold text-gray-700 mb-2">{{ $groupName }}</h3>
                    <div class="divide-y border rounded">
                        @foreach ($groupSettings as $setting)
                            <form action="{{ route('admin.sla.pengaturan.update', $setting->service_key) }}" method="POST"
                                class="flex flex-wrap items-center gap-3 px-4 py-3">
                                @csrf
                                @method('PUT')
                                <div class="flex-1 min-w-[180px] font-medium text-gray-700 text-sm">
                                    {{ $setting->label }}
                                </div>
                                <input type="number" name="target_value" value="{{ $setting->target_value }}" min="1" max="999"
                                    class="w-20 border border-gray-300 rounded px-2 py-1 text-sm">
                                <select name="target_unit" class="border border-gray-300 rounded px-2 py-1 text-sm">
                                    <option value="hari_kerja" {{ $setting->target_unit === 'hari_kerja' ? 'selected' : '' }}>Hari Kerja</option>
                                    <option value="jam" {{ $setting->target_unit === 'jam' ? 'selected' : '' }}>Jam</option>
                                </select>
                                <label class="inline-flex items-center gap-1 text-xs text-gray-600">
                                    <input type="hidden" name="is_active" value="0">
                                    <input type="checkbox" name="is_active" value="1" {{ $setting->is_active ? 'checked' : '' }}
                                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    Aktif
                                </label>
                                <button type="submit"
                                    class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-3 py-1.5 rounded">
                                    Simpan
                                </button>
                            </form>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
