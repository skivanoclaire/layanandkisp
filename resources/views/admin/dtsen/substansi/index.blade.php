@extends('layouts.authenticated')

@section('title', '- Verifikasi Substansi DTSEN')
@section('header-title', 'Verifikasi Substansi DTSEN')

@section('content')
<div class="container mx-auto p-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Verifikasi Substansi</h1>
        <p class="text-gray-600 mt-1">
            Form 3.2 — penilaian kesesuaian KAK dengan dasar hukum &amp; tugas fungsi OPD terhadap variabel yang dimohonkan.
            Koordinator Forum Satu Data Daerah, SLA 2 hari kerja.
        </p>
    </div>

    @include('partials.dtsen.errors')

    <div class="mb-6 rounded-lg border border-amber-300 bg-amber-50 p-4 text-sm text-amber-900">
        Pada tahap ini pemohon <strong>tidak dapat</strong> memperbaiki dokumen. Bila diperlukan penjelasan tambahan,
        undang pemohon untuk klarifikasi dan catat berita acaranya. <strong>Penolakan bersifat final</strong> —
        proses tidak dapat dilanjutkan.
    </div>

    <form method="GET" class="bg-white rounded-lg shadow-sm border p-4 mb-6 flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
            <select name="status" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                <option value="">Semua</option>
                @foreach ([
                    \App\Models\DtsenDataRequest::STATUS_VERIF_SUBSTANSI,
                    \App\Models\DtsenDataRequest::STATUS_KLARIFIKASI,
                    \App\Models\DtsenDataRequest::STATUS_DITERIMA,
                    \App\Models\DtsenDataRequest::STATUS_DITOLAK,
                ] as $val)
                    <option value="{{ $val }}" @selected(request('status') === $val)>{{ \App\Models\DtsenDataRequest::statusLabels()[$val] }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Level</label>
            <select name="level" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                <option value="">Semua</option>
                @foreach (\App\Models\DtsenDataRequest::selectableLevels() as $lvl)
                    <option value="{{ $lvl }}" @selected((string) request('level') === (string) $lvl)>Level {{ $lvl }}</option>
                @endforeach
            </select>
        </div>
        <button class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 rounded-lg font-semibold text-sm">Terapkan</button>
        <a href="{{ route('admin.dtsen.substansi.index') }}" class="px-4 py-2 text-sm text-gray-600 hover:underline">Reset</a>
    </form>

    <div class="bg-white rounded-lg shadow-sm border overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-3 text-left">No. Tiket</th>
                    <th class="px-4 py-3 text-left">Perangkat Daerah</th>
                    <th class="px-4 py-3 text-left">Program/Kegiatan</th>
                    <th class="px-4 py-3 text-center">Level</th>
                    <th class="px-4 py-3 text-center">Variabel</th>
                    <th class="px-4 py-3 text-left">Lolos Administrasi</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($items as $item)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-mono">{{ $item->ticket_no }}</td>
                        <td class="px-4 py-3">{{ $item->unitKerja->nama ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $item->nama_program }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-0.5 rounded text-xs font-semibold
                                {{ $item->level_akses >= 4 ? 'bg-red-100 text-red-700' : ($item->level_akses === 3 ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700') }}">
                                L{{ $item->level_akses }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">{{ $item->request_variables_count }}</td>
                        <td class="px-4 py-3">{{ $item->verif_admin_at?->format('d/m/Y H:i') ?? '-' }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $item->status_badge_class }}">{{ $item->status_label }}</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <a href="{{ route('admin.dtsen.substansi.show', $item->id) }}" class="text-blue-600 hover:underline">Nilai</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-4 py-8 text-center text-gray-500">Tidak ada permohonan pada tahap verifikasi substansi.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $items->links() }}</div>
</div>
@endsection
