@extends('layouts.authenticated')

@section('title', '- Edit Running Text')
@section('header-title', 'Edit Running Text')

@section('content')
    <div class="max-w-3xl">
        <div class="flex items-center justify-between mb-5">
            <h1 class="text-xl md:text-2xl font-bold text-gray-800">Edit Running Text</h1>
            <a href="{{ route('admin.running-text.index') }}" class="text-sm text-gray-600 hover:text-gray-800">&larr; Kembali</a>
        </div>

        <form method="POST" action="{{ route('admin.running-text.update', $runningText) }}"
            class="bg-white rounded-lg shadow p-5">
            @csrf
            @method('PUT')
            @include('admin.running-text._form')

            <div class="mt-6 flex items-center gap-2">
                <button type="submit"
                    class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-lg text-sm font-semibold">
                    Simpan Perubahan
                </button>
                <a href="{{ route('admin.running-text.index') }}"
                    class="px-5 py-2 text-sm border rounded-lg hover:bg-gray-100">Batal</a>
            </div>
        </form>
    </div>
@endsection
