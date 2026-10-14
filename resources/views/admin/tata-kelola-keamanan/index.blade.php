@extends('layouts.authenticated')

@section('title', '- Tata Kelola Keamanan Informasi')
@section('header-title', 'Tata Kelola Keamanan: Integrasi COBIT 2019 dan ISO/IEC 27001:2022')

@section('content')
    <div class="container mx-auto px-4 py-6">
        @include('user.kuesioner-portal._flash')

        <div class="mb-6">
            <h1 class="text-2xl font-semibold text-gray-900">Tata Kelola Keamanan Informasi</h1>
            <p class="text-sm text-gray-600 mt-1">Evaluasi tata kelola keamanan informasi berbasis COBIT 2019 dan ISO/IEC 27001:2022: design factors, pemetaan COBIT–ISO–regulasi,
                validasi pakar (Delphi), asesmen kapabilitas, status kontrol, kesenjangan, dan peta jalan.</p>
            <a href="{{ route('admin.tata-kelola-keamanan.panduan') }}" class="inline-block mt-3 px-4 py-2 text-sm font-medium rounded-lg border border-blue-300 text-blue-700 hover:bg-blue-50">Baca panduan penggunaan</a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 bg-white rounded-lg shadow-sm p-6 overflow-x-auto">
                <h2 class="text-lg font-semibold text-gray-900 mb-3">Daftar evaluasi</h2>
                @if ($evaluasis->isEmpty())
                    <p class="text-sm text-gray-500">Belum ada evaluasi. Buat evaluasi baru di samping.</p>
                @else
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-left text-xs text-gray-500 border-b">
                                <th class="py-2 pr-4">Nama</th>
                                <th class="py-2 px-4">Instansi</th>
                                <th class="py-2 px-4 text-right">Objektif dipilih</th>
                                <th class="py-2 px-4 text-right">Butir pemetaan</th>
                                <th class="py-2 px-4 text-right">Pakar</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @foreach ($evaluasis as $s)
                                <tr>
                                    <td class="py-2 pr-4">
                                        <a href="{{ route('admin.tata-kelola-keamanan.show', $s) }}" class="text-green-700 font-medium hover:underline">{{ $s->nama }}</a>
                                        @if ($s->is_simulasi)
                                            <span class="ml-1 px-2 py-0.5 text-xs rounded bg-yellow-100 text-yellow-800">simulasi</span>
                                        @endif
                                    </td>
                                    <td class="py-2 px-4">{{ $s->instansi }}</td>
                                    <td class="py-2 px-4 text-right">{{ $s->jumlah_dipilih }}</td>
                                    <td class="py-2 px-4 text-right">{{ $s->pemetaans_count }}</td>
                                    <td class="py-2 px-4 text-right">{{ $s->pakars_count }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-3">Buat evaluasi</h2>
                <form method="POST" action="{{ route('admin.tata-kelola-keamanan.store') }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-sm text-gray-700">Nama evaluasi</label>
                        <input name="nama" required value="{{ old('nama', 'Evaluasi Tata Kelola Keamanan Informasi '.now()->year) }}" class="mt-1 w-full rounded-lg border-gray-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-700">Instansi</label>
                        <input name="instansi" value="{{ old('instansi') }}" placeholder="mis. DKISP Provinsi Kalimantan Utara" class="mt-1 w-full rounded-lg border-gray-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-700">Keterangan <span class="text-xs text-gray-500">(ditampilkan kepada responden dan pakar)</span></label>
                        <textarea name="keterangan" rows="3" class="mt-1 w-full rounded-lg border-gray-300 text-sm">{{ old('keterangan') }}</textarea>
                    </div>
                    <button class="px-4 py-2 text-sm font-medium rounded-lg bg-green-600 text-white hover:bg-green-700">Simpan</button>
                </form>
            </div>
        </div>
    </div>
@endsection
