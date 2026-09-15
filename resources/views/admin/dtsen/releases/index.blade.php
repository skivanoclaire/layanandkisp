@extends('layouts.authenticated')

@section('title', '- Master Rilis DTSEN')
@section('header-title', 'Master Rilis DTSEN')

@section('content')
<div class="container mx-auto p-6">
    <div class="mb-6 flex flex-wrap justify-between items-center gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Rilis DTSEN</h1>
            <p class="text-gray-600 mt-1">Rilis baru menjadi dasar pemberitahuan pemusnahan salinan data rilis lama (Bab VII Juknis).</p>
        </div>
        <a href="{{ route('admin.dtsen.releases.create') }}"
           class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-semibold">+ Tambah Rilis</a>
    </div>

    @include('partials.dtsen.errors')

    <div class="bg-white rounded-lg shadow-sm border overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-3 text-left">Nomor Rilis</th>
                    <th class="px-4 py-3 text-left">Tanggal</th>
                    <th class="px-4 py-3 text-center">Variabel</th>
                    <th class="px-4 py-3 text-left">Keterangan</th>
                    <th class="px-4 py-3 text-center">Status</th>
                    <th class="px-4 py-3 text-left">Pemberitahuan</th>
                    <th class="px-4 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($items as $item)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-mono">{{ $item->nomor_rilis }}</td>
                        <td class="px-4 py-3">{{ $item->tanggal_rilis->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 text-center">{{ $item->variables_count }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ Str::limit($item->keterangan, 80) ?: '-' }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-0.5 rounded text-xs font-semibold {{ $item->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-600' }}">
                                {{ $item->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            @if ($item->notified_at)
                                <span class="text-xs text-gray-600">Dikirim {{ $item->notified_at->format('d/m/Y H:i') }}</span>
                            @else
                                <form action="{{ route('admin.dtsen.releases.notify', $item->id) }}" method="POST"
                                      onsubmit="return confirm('Catat pemberitahuan rilis ini ke seluruh OPD pemegang data rilis sebelumnya?')">
                                    @csrf
                                    <button class="text-blue-600 hover:underline text-xs font-semibold">Kirim Pemberitahuan</button>
                                </form>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center whitespace-nowrap">
                            <a href="{{ route('admin.dtsen.releases.edit', $item->id) }}" class="text-blue-600 hover:underline">Edit</a>
                            <form action="{{ route('admin.dtsen.releases.destroy', $item->id) }}" method="POST" class="inline"
                                  onsubmit="return confirm('Hapus rilis ini?')">
                                @csrf @method('DELETE')
                                <button class="text-red-600 hover:underline ml-2">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-8 text-center text-gray-500">Belum ada rilis DTSEN.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $items->links() }}</div>
</div>
@endsection
