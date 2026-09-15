@extends('layouts.authenticated')

@section('title', '- Kelola Permintaan Data DTSEN')
@section('header-title', 'Kelola Permintaan Data DTSEN')

@section('content')
<div class="container mx-auto p-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Permintaan Data DTSEN</h1>
        <p class="text-gray-600 mt-1">Verifikasi administrasi, pemrosesan &amp; QA, BAST, dan penerbitan hak akses.</p>
    </div>

    @include('partials.dtsen.errors')

    <form method="GET" class="bg-white rounded-lg shadow-sm border p-4 mb-6 flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
            <select name="status" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                <option value="">Semua</option>
                @foreach (\App\Models\DtsenDataRequest::statusLabels() as $val => $label)
                    @continue($val === 'draft')
                    <option value="{{ $val }}" @selected(request('status') === $val)>{{ $label }}</option>
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
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Perangkat Daerah</label>
            <select name="unit_kerja_id" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                <option value="">Semua</option>
                @foreach ($unitKerjaList as $uk)
                    <option value="{{ $uk->id }}" @selected((string) request('unit_kerja_id') === (string) $uk->id)>{{ $uk->nama }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Dari</label>
            <input type="date" name="dari_tanggal" value="{{ request('dari_tanggal') }}" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Sampai</label>
            <input type="date" name="sampai_tanggal" value="{{ request('sampai_tanggal') }}" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Cari</label>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Tiket / program / surat"
                   class="px-3 py-2 border border-gray-300 rounded-lg text-sm w-56">
        </div>
        <button class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 rounded-lg font-semibold text-sm">Terapkan</button>
        <a href="{{ route('admin.dtsen.permohonan.index') }}" class="px-4 py-2 text-sm text-gray-600 hover:underline">Reset</a>
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
                    <th class="px-4 py-3 text-left">Diajukan</th>
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
                        <td class="px-4 py-3">{{ $item->submitted_at?->format('d/m/Y H:i') ?? '-' }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $item->status_badge_class }}">{{ $item->status_label }}</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <a href="{{ route('admin.dtsen.permohonan.show', $item->id) }}" class="text-blue-600 hover:underline">Proses</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-4 py-8 text-center text-gray-500">Tidak ada permohonan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $items->links() }}</div>
</div>
@endsection
