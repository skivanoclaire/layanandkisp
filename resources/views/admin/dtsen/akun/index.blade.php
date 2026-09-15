@extends('layouts.authenticated')

@section('title', '- Kelola Akun DTSEN')
@section('header-title', 'Kelola Akun Layanan DTSEN')

@section('content')
<div class="container mx-auto p-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Verifikasi Akun Layanan DTSEN</h1>
        <p class="text-gray-600 mt-1">Form 1.2 — target penyelesaian 1 hari kerja sejak dokumen lengkap.</p>
    </div>

    @include('partials.dtsen.errors')

    {{-- Permintaan aktivasi ulang yang menunggu keputusan --}}
    @if ($pendingReactivations->isNotEmpty())
        <div class="mb-6 bg-white rounded-lg shadow-sm border border-yellow-300 p-6">
            <h2 class="font-bold text-gray-800 mb-3">Permintaan Aktivasi Ulang ({{ $pendingReactivations->count() }})</h2>
            <div class="space-y-3">
                @foreach ($pendingReactivations as $r)
                    <div class="border rounded-lg p-3 text-sm">
                        <div class="flex flex-wrap justify-between gap-2">
                            <div class="min-w-0">
                                <p class="font-semibold">{{ $r->accountRequest->unitKerja->nama ?? '-' }}
                                    <span class="font-mono text-xs text-gray-500 ml-1">{{ $r->accountRequest->ticket_no ?? '' }}</span>
                                </p>
                                <p class="text-gray-600 mt-1">{{ $r->alasan }}</p>
                                <p class="text-xs text-gray-500 mt-1">Diajukan {{ $r->user->name ?? '-' }} — {{ $r->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                        <form action="{{ route('admin.dtsen.akun.reaktivasi', $r->id) }}" method="POST"
                              class="mt-3 flex flex-wrap items-end gap-2">
                            @csrf
                            <input type="text" name="catatan" placeholder="Catatan (opsional)"
                                   class="flex-1 min-w-[200px] px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            <button name="status" value="disetujui"
                                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-semibold text-sm">Setujui</button>
                            <button name="status" value="ditolak"
                                    class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-semibold text-sm">Tolak</button>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Filter --}}
    <form method="GET" class="bg-white rounded-lg shadow-sm border p-4 mb-6 flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
            <select name="status" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                <option value="">Semua</option>
                @foreach (\App\Models\DtsenAccountRequest::statusLabels() as $val => $label)
                    @continue($val === 'draft')
                    <option value="{{ $val }}" @selected(request('status') === $val)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Cari</label>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Tiket / nomor surat / narahubung"
                   class="px-3 py-2 border border-gray-300 rounded-lg text-sm w-64">
        </div>
        <button class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 rounded-lg font-semibold text-sm">Terapkan</button>
        <a href="{{ route('admin.dtsen.akun.index') }}" class="px-4 py-2 text-sm text-gray-600 hover:underline">Reset</a>
    </form>

    <div class="bg-white rounded-lg shadow-sm border overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-3 text-left">No. Tiket</th>
                    <th class="px-4 py-3 text-left">Perangkat Daerah</th>
                    <th class="px-4 py-3 text-left">Pengaju</th>
                    <th class="px-4 py-3 text-center">Personel</th>
                    <th class="px-4 py-3 text-left">Diajukan</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-left">Keaktifan</th>
                    <th class="px-4 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($items as $item)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-mono">{{ $item->ticket_no }}</td>
                        <td class="px-4 py-3">{{ $item->unitKerja->nama ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $item->user->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-center">{{ $item->members_count }}</td>
                        <td class="px-4 py-3">{{ $item->submitted_at?->format('d/m/Y H:i') ?? '-' }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $item->status_badge_class }}">{{ $item->status_label }}</span>
                        </td>
                        <td class="px-4 py-3">
                            @if ($item->status === \App\Models\DtsenAccountRequest::STATUS_DISETUJUI)
                                <span class="{{ $item->is_active ? 'text-green-700' : 'text-red-600' }} font-semibold">
                                    {{ $item->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <a href="{{ route('admin.dtsen.akun.show', $item->id) }}" class="text-blue-600 hover:underline">Periksa</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-4 py-8 text-center text-gray-500">Tidak ada permohonan akun.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $items->links() }}</div>
</div>
@endsection
