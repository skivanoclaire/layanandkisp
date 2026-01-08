@extends('layouts.authenticated')

@section('title', '- Detail Peminjaman')
@section('header-title', 'Detail Peminjaman')

@section('content')
    <h1 class="text-2xl font-bold text-green-700 mb-4">Detail Peminjaman {{ $borrowing->code }}</h1>

    <div class="grid md:grid-cols-2 gap-6">
        <div class="bg-white p-4 rounded shadow">
            <dl class="space-y-2 text-sm">
                <div>
                    <dt class="font-semibold">Status</dt>
                    <dd class="capitalize">{{ $borrowing->status }}</dd>
                </div>
                <div>
                    <dt class="font-semibold">Mulai</dt>
                    <dd>{{ optional($borrowing->started_at)->format('Y-m-d H:i') }}</dd>
                </div>
                <div>
                    <dt class="font-semibold">Selesai</dt>
                    <dd>{{ optional($borrowing->finished_at)->format('Y-m-d H:i') ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="font-semibold">Catatan</dt>
                    <dd>{{ $borrowing->notes ?: '—' }}</dd>
                </div>
            </dl>

            <h2 class="mt-4 font-semibold">Barang</h2>
            <ul class="list-disc pl-5 text-sm">
                @foreach ($borrowing->items as $it)
                    <li>{{ $it->asset->name }} × {{ $it->qty }} ({{ $it->asset->category?->name }})</li>
                @endforeach
            </ul>
        </div>

        <div class="bg-white p-4 rounded shadow">
            <h2 class="font-semibold mb-2">Foto</h2>
            <div class="grid grid-cols-3 gap-2">
                @forelse ($borrowing->photos as $p)
                    <div class="border rounded overflow-hidden">
                        @if ($p->path)
                            <a href="{{ asset('storage/' . $p->path) }}" target="_blank" title="Lihat Foto">
                                <img src="{{ asset('storage/' . $p->path) }}" alt="Foto {{ $p->phase }}"
                                    class="object-cover w-full h-32 cursor-pointer hover:opacity-80 transition">
                            </a>
                        @else
                            <div class="h-32 bg-gray-100 flex items-center justify-center">
                                <span class="text-gray-400">No image</span>
                            </div>
                        @endif
                        <div class="text-xs text-center py-1 bg-gray-50">{{ $p->phase }}</div>
                    </div>
                @empty
                    <div class="text-sm text-gray-500">Tidak ada foto</div>
                @endforelse
            </div>
        </div>

    </div>

    <div class="mt-4 flex gap-2">
        <a href="{{ route('op.tik.borrow.index') }}" class="px-4 py-2 border rounded">Kembali</a>

        {{-- Tampilkan tombol Edit jika masih bisa direvisi --}}
        @if (in_array($borrowing->status, ['pending', 'ongoing']))
            <a href="{{ route('op.tik.borrow.edit', $borrowing->id) }}"
                class="px-4 py-2 border rounded bg-blue-50 hover:bg-blue-100">
                Edit
            </a>
        @endif

        {{-- Tombol Kembalikan hanya untuk status ongoing --}}
        @if ($borrowing->status === 'ongoing')
            <a href="{{ route('op.tik.borrow.return.form', $borrowing->id) }}"
                class="px-4 py-2 border rounded bg-yellow-50 hover:bg-yellow-100">
                Kembalikan
            </a>
        @endif
    </div>

@endsection
