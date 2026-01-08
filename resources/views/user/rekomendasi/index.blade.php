@extends('layouts.authenticated')

@section('title', '- Rekomendasi Aplikasi')
@section('header-title', 'Rekomendasi Aplikasi')

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
                        <div class="flex-1">
                            <h2 class="font-semibold text-lg">{{ $form->judul_aplikasi }}</h2>
                            <div class="flex items-center gap-2 mt-1">
                                <p class="text-sm text-gray-500">Status:</p>
                                @if ($form->status == 'diajukan')
                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full">Diajukan</span>
                                @elseif ($form->status == 'perlu_revisi')
                                    <span class="px-2 py-1 bg-orange-100 text-orange-800 text-xs rounded-full">Perlu Revisi</span>
                                @elseif ($form->status == 'diproses')
                                    <span class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs rounded-full">Diproses</span>
                                @elseif ($form->status == 'disetujui')
                                    <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">Disetujui</span>
                                @elseif ($form->status == 'ditolak')
                                    <span class="px-2 py-1 bg-red-100 text-red-800 text-xs rounded-full">Ditolak</span>
                                @else
                                    <span class="px-2 py-1 bg-gray-100 text-gray-800 text-xs rounded-full">{{ ucfirst($form->status) }}</span>
                                @endif
                            </div>
                            <p class="text-xs text-gray-400 mt-1">Ticket: {{ $form->ticket_number }}</p>

                            @if ($form->status == 'perlu_revisi' && $form->revision_notes)
                                <div class="mt-2 p-2 bg-orange-50 border border-orange-200 rounded">
                                    <p class="text-xs font-semibold text-orange-800">Catatan Revisi dari Admin:</p>
                                    <p class="text-sm text-orange-700 mt-1">{{ $form->revision_notes }}</p>
                                </div>
                            @endif
                        </div>
                        <div class="flex space-x-2">
                            <a href="{{ route('user.rekomendasi.aplikasi.show', $form->id) }}"
                                class="px-3 py-1 bg-blue-600 text-white rounded text-sm hover:bg-blue-700">
                                Detail
                            </a>
                            @if ($form->status == 'perlu_revisi')
                                <a href="{{ route('user.rekomendasi.aplikasi.edit', $form->id) }}"
                                    class="px-3 py-1 bg-orange-500 hover:bg-orange-600 text-white rounded text-sm font-semibold">
                                    ↻ Revisi
                                </a>
                            @elseif ($form->status == 'draft')
                                <a href="{{ route('user.rekomendasi.aplikasi.edit', $form->id) }}"
                                    class="px-3 py-1 bg-yellow-500 hover:bg-yellow-600 text-white rounded text-sm">
                                    Edit
                                </a>
                            @elseif ($form->status == 'disetujui')
                                <a href="{{ route('user.rekomendasi.aplikasi.download-pdf', $form->id) }}"
                                    class="px-3 py-1 bg-green-600 hover:bg-green-700 text-white rounded text-sm font-semibold">
                                    📄 Download PDF
                                </a>
                            @endif
                            @if ($form->status == 'draft')
                                <form action="{{ route('user.rekomendasi.aplikasi.destroy', $form->id) }}" method="POST"
                                    onsubmit="return confirm('Yakin ingin menghapus usulan ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="px-3 py-1 bg-red-600 text-white rounded text-sm hover:bg-red-700">
                                        Hapus
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@endsection
