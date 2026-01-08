@extends('layouts.authenticated')

@section('title', '- Tambah Operator')
@section('header-title', 'Tambah Operator')

@section('content')
<div class="container mx-auto px-4">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Tambah Operator Video Konferensi</h1>
        <a href="{{ route('admin.operators.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded font-semibold">
            Kembali
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <form action="{{ route('admin.operators.store') }}" method="POST">
            @csrf

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Pengguna <span class="text-red-500">*</span></label>
                <select name="user_id" class="w-full border-gray-300 rounded-md shadow-sm @error('user_id') border-red-500 @enderror" required>
                    <option value="">-- Pilih Pengguna --</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }} ({{ $user->email }})
                        </option>
                    @endforeach
                </select>
                @error('user_id')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror

                @if($users->isEmpty())
                    <p class="text-sm text-gray-500 mt-2">Semua pengguna sudah menjadi operator atau tidak ada pengguna lain.</p>
                @endif
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('admin.operators.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded">
                    Batal
                </a>
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded" {{ $users->isEmpty() ? 'disabled' : '' }}>
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
