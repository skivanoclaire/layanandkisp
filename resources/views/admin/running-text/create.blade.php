@extends('layouts.authenticated')

@section('title', '- Tambah Running Text')
@section('header-title', 'Tambah Running Text')

@section('content')
    <div class="max-w-3xl">
        <div class="flex items-center justify-between mb-5">
            <h1 class="text-xl md:text-2xl font-bold text-gray-800">Tambah Running Text</h1>
            <a href="{{ route('admin.running-text.index') }}" class="text-sm text-gray-600 hover:text-gray-800">&larr; Kembali</a>
        </div>

        <form method="POST" action="{{ route('admin.running-text.store') }}"
            class="bg-white rounded-lg shadow p-5">
            @csrf
            @include('admin.running-text._form')

            <div class="mt-6 flex items-center gap-2">
                <button type="submit"
                    class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-lg text-sm font-semibold">
                    Simpan
                </button>
                <a href="{{ route('admin.running-text.index') }}"
                    class="px-5 py-2 text-sm border rounded-lg hover:bg-gray-100">Batal</a>
            </div>
        </form>
    </div>
@endsection
