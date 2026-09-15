@extends('layouts.authenticated')

@section('title', '- Permintaan Data DTSEN')
@section('header-title', 'Permintaan Data DTSEN')

@section('content')
<div class="container mx-auto p-6">
    <div class="mb-6 flex flex-wrap justify-between items-center gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Permintaan Data DTSEN</h1>
            <p class="text-gray-600 mt-1">Tahap 2 s.d. 4 — pengajuan, pengecekan, dan pemberian hak akses data.</p>
        </div>
        @if ($account)
            <a href="{{ route('user.dtsen.permohonan.create') }}"
               class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-semibold">+ Ajukan Permintaan</a>
        @endif
    </div>

    @include('partials.dtsen.errors')

    @unless ($account)
        <div class="mb-6 rounded-lg border border-orange-300 bg-orange-50 p-4 text-sm text-orange-900">
            <p class="font-semibold">Akun layanan DTSEN belum aktif</p>
            <p class="mt-1">Permintaan data hanya dapat diajukan lewat akun layanan DTSEN yang telah disetujui dan masih aktif.</p>
            <a href="{{ route('user.dtsen.akun.index') }}" class="mt-2 inline-block font-semibold underline">Kelola Akun Layanan DTSEN &rarr;</a>
        </div>
    @endunless

    <div class="bg-white rounded-lg shadow-sm border overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-3 text-left">No. Tiket</th>
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
                        <td class="px-4 py-3">
                            {{ $item->nama_program }}
                            @if ($item->parent_request_id)
                                <span class="ml-1 px-1.5 py-0.5 rounded text-[10px] font-semibold bg-indigo-100 text-indigo-700">Permintaan ulang</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-0.5 rounded text-xs font-semibold
                                {{ $item->level_akses >= 4 ? 'bg-red-100 text-red-700' : ($item->level_akses === 3 ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700') }}">
                                L{{ $item->level_akses }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">{{ $item->request_variables_count }}</td>
                        <td class="px-4 py-3">{{ optional($item->submitted_at ?? $item->created_at)->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $item->status_badge_class }}">{{ $item->status_label }}</span>
                        </td>
                        <td class="px-4 py-3 text-center whitespace-nowrap">
                            <a href="{{ route('user.dtsen.permohonan.show', $item->id) }}" class="text-blue-600 hover:underline">Detail</a>
                            @if ($item->isEditableByOwner())
                                <a href="{{ route('user.dtsen.permohonan.edit', $item->id) }}" class="text-blue-600 hover:underline ml-2">Edit</a>
                            @endif
                            @if ($item->status === \App\Models\DtsenDataRequest::STATUS_DRAFT)
                                <form action="{{ route('user.dtsen.permohonan.destroy', $item->id) }}" method="POST" class="inline"
                                      onsubmit="return confirm('Hapus draft permohonan ini?')">
                                    @csrf @method('DELETE')
                                    <button class="text-red-600 hover:underline ml-2">Hapus</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-8 text-center text-gray-500">Belum ada permintaan data.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $items->links() }}</div>
</div>
@endsection
