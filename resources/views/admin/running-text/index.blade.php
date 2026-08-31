@extends('layouts.authenticated')

@section('title', '- Kelola Running Text')
@section('header-title', 'Kelola Running Text')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <h1 class="text-xl md:text-2xl font-bold text-gray-800">Running Text</h1>
        <a href="{{ route('admin.running-text.create') }}"
            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-semibold whitespace-nowrap">
            + Tambah Running Text
        </a>
    </div>

    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6 text-sm text-blue-800">
        <p class="font-semibold mb-1">Catatan:</p>
        <ul class="list-disc list-inside space-y-1">
            <li>Teks tampil berjalan di bawah navbar pada semua halaman pengguna yang sudah login.</li>
            <li>Isi disimpan sebagai teks polos — tag HTML dan skrip otomatis dibuang.</li>
            <li>Urutan kecil tampil lebih dulu. Kosongkan jadwal bila ingin tayang terus-menerus.</li>
            <li>Perubahan tampil ke pengguna paling lambat 1 menit (cache).</li>
        </ul>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        @if ($runningTexts->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-100 text-xs uppercase tracking-wide text-gray-700">
                        <tr>
                            <th class="px-4 py-3 text-left">Urutan</th>
                            <th class="px-4 py-3 text-left">Isi Teks</th>
                            <th class="px-4 py-3 text-left">Jadwal</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-left">Diubah Oleh</th>
                            <th class="px-4 py-3 text-left">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach ($runningTexts as $rt)
                            <tr class="hover:bg-gray-50 align-top">
                                <td class="px-4 py-3 text-gray-700">{{ $rt->urutan }}</td>
                                <td class="px-4 py-3 max-w-md">
                                    <div class="text-gray-800 break-words">{{ $rt->isi }}</div>
                                    @if ($rt->tautan)
                                        <a href="{{ $rt->tautan }}" target="_blank" rel="noopener noreferrer nofollow"
                                            class="text-xs text-blue-600 hover:underline break-all">{{ $rt->tautan }}</a>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-600 whitespace-nowrap">
                                    <div>Mulai: {{ $rt->mulai_at?->format('d/m/Y H:i') ?? '—' }}</div>
                                    <div>Selesai: {{ $rt->selesai_at?->format('d/m/Y H:i') ?? '—' }}</div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if (!$rt->is_active)
                                        <span class="inline-block rounded-full bg-gray-200 text-gray-700 px-2 py-0.5 text-xs font-medium">Nonaktif</span>
                                    @elseif ($rt->sedangTayang())
                                        <span class="inline-block rounded-full bg-green-100 text-green-700 px-2 py-0.5 text-xs font-medium">Tayang</span>
                                    @else
                                        <span class="inline-block rounded-full bg-yellow-100 text-yellow-700 px-2 py-0.5 text-xs font-medium">Di luar jadwal</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-600">
                                    {{ $rt->pengubah->name ?? $rt->pembuat->name ?? '—' }}
                                    <div class="text-gray-400">{{ $rt->updated_at?->format('d/m/Y H:i') }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap gap-1">
                                        <form method="POST" action="{{ route('admin.running-text.toggle', $rt) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                class="{{ $rt->is_active ? 'bg-yellow-500 hover:bg-yellow-600' : 'bg-green-500 hover:bg-green-600' }} text-white px-2 py-1 rounded text-xs font-medium">
                                                {{ $rt->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        </form>

                                        <a href="{{ route('admin.running-text.edit', $rt) }}"
                                            class="bg-blue-500 hover:bg-blue-600 text-white px-2 py-1 rounded text-xs font-medium">
                                            Edit
                                        </a>

                                        <form method="POST" action="{{ route('admin.running-text.destroy', $rt) }}"
                                            onsubmit="return confirm('Yakin ingin menghapus running text ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded text-xs font-medium">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-4 py-3 border-t">
                {{ $runningTexts->links() }}
            </div>
        @else
            <div class="p-8 text-center text-gray-500 text-sm">
                Belum ada running text. Klik "Tambah Running Text" untuk membuat yang pertama.
            </div>
        @endif
    </div>
@endsection
