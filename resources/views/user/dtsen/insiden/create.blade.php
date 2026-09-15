@extends('layouts.authenticated')

@section('title', '- Lapor Insiden Keamanan DTSEN')
@section('header-title', 'Laporan Insiden Keamanan Data DTSEN')

@section('content')
<div class="container mx-auto p-6 max-w-4xl">
    <div class="mb-6">
        <a href="{{ route('user.dtsen.insiden.index') }}" class="text-blue-600 hover:underline text-sm">&larr; Kembali ke daftar</a>
        <h1 class="text-2xl font-bold text-gray-800 mt-2">Laporan Insiden Keamanan Data</h1>
        <p class="text-gray-600 mt-1">Bab VI huruf C Juknis.</p>
    </div>

    @include('partials.dtsen.errors')

    <div class="mb-6 rounded-lg border border-red-300 bg-red-50 p-4 text-sm text-red-900">
        <p class="font-semibold">Batas waktu pelaporan</p>
        <p class="mt-1">
            Indikasi kebocoran, penyalahgunaan, atau insiden keamanan lain wajib dilaporkan paling lambat
            <strong>3x24 jam hari kerja</strong> sejak diketahui. Pelaporan yang melewati batas akan otomatis
            dieskalasi ke Petugas Pelindung DTSEN (Kepala DKISP) dan Prosesor.
        </p>
    </div>

    <form action="{{ route('user.dtsen.insiden.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Permohonan / BAST Terkait</label>
                    <select name="dtsen_data_request_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        <option value="">-- Tidak terkait permohonan tertentu --</option>
                        @foreach ($requests as $r)
                            <option value="{{ $r->id }}" @selected(old('dtsen_data_request_id') == $r->id)>
                                {{ $r->ticket_no }} — {{ $r->nama_program }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Insiden <span class="text-red-500">*</span></label>
                    <select name="jenis_insiden" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        @foreach (\App\Models\DtsenIncidentReport::jenisLabels() as $val => $label)
                            <option value="{{ $val }}" @selected(old('jenis_insiden') === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Waktu Insiden Diketahui <span class="text-red-500">*</span></label>
                    <input type="datetime-local" name="waktu_diketahui" required
                           value="{{ old('waktu_diketahui', now()->format('Y-m-d\TH:i')) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kronologi <span class="text-red-500">*</span></label>
                    <textarea name="kronologi" rows="5" required class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                              placeholder="Uraikan urutan kejadian, cara insiden terdeteksi, dan pihak yang terlibat.">{{ old('kronologi') }}</textarea>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Dampak / Dugaan Data Terdampak <span class="text-red-500">*</span></label>
                    <textarea name="dampak" rows="3" required class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                              placeholder="mis. berkas berisi ±1.200 baris data BNBA pada wilayah Kecamatan X">{{ old('dampak') }}</textarea>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tindakan Awal yang Sudah Dilakukan <span class="text-red-500">*</span></label>
                    <textarea name="tindakan_awal" rows="3" required class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                              placeholder="mis. akses dicabut, berkas dikarantina, kata sandi diganti">{{ old('tindakan_awal') }}</textarea>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Lampiran (opsional)</label>
                    <input type="file" name="lampiran" accept=".pdf,.doc,.docx,.png,.jpg,.jpeg,.zip"
                           class="w-full text-sm border border-gray-300 rounded-lg p-2">
                </div>
            </div>
        </div>

        <div class="flex flex-wrap gap-3">
            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-6 py-2.5 rounded-lg font-semibold">Kirim Laporan Insiden</button>
            <a href="{{ route('user.dtsen.insiden.index') }}" class="px-6 py-2.5 rounded-lg font-semibold text-gray-600 hover:bg-gray-100">Batal</a>
        </div>
    </form>
</div>
@endsection
