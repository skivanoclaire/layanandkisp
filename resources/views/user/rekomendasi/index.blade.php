@extends('layouts.user')

@section('content')
    <div class="mb-4 flex justify-between items-center">
        <h1 class="text-2xl font-bold">Daftar Usulan Rekomendasi Aplikasi</h1>
        <a href="{{ route('user.rekomendasi.aplikasi.create') }}"
            class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
            + Tambah Usulan
        </a>
    </div>

    @if ($forms->isEmpty())
        <p class="text-gray-600">Belum ada usulan yang dikirim.</p>
    @else
        <div class="space-y-4">
            @foreach ($forms as $form)
                <div class="border p-4 rounded bg-white shadow-sm hover:shadow-md transition">
                    <div class="flex justify-between items-start">
                        <div>
                            <h2 class="font-semibold text-lg">{{ $form->judul_aplikasi }}</h2>
                            <p class="text-sm text-gray-500">Status: {{ ucfirst($form->status) }}</p>
                            <p class="text-xs text-gray-400">Ticket: {{ $form->ticket_number }}</p>
                        </div>
                        <div class="flex space-x-2">
                            <a href="{{ route('user.rekomendasi.aplikasi.show', $form->id) }}"
                                class="px-3 py-1 bg-blue-600 text-white rounded text-sm hover:bg-blue-700">
                                Detail
                            </a>
                            <a href="{{ route('user.rekomendasi.aplikasi.edit', $form->id) }}"
                                class="px-3 py-1 bg-yellow-500 text-white rounded text-sm hover:bg-yellow-600">
                                Edit
                            </a>
                            <form action="{{ route('user.rekomendasi.aplikasi.destroy', $form->id) }}" method="POST"
                                onsubmit="return confirm('Yakin ingin menghapus usulan ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="px-3 py-1 bg-red-600 text-white rounded text-sm hover:bg-red-700">
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@endsection
