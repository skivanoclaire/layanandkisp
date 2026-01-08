@extends('layouts.authenticated')

@section('title', '- Kelola Data Operator')
@section('header-title', 'Kelola Data Operator')

@section('content')
<div class="container mx-auto px-4">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Kelola Data Operator Video Konferensi</h1>
        <div class="flex gap-2">
            <a href="{{ route('admin.vidcon-data.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded font-semibold">
                Kembali ke Data Vidcon
            </a>
            <a href="{{ route('admin.operators.create') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded font-semibold">
                Tambah Operator
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role Lainnya</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($operators as $operator)
                    <tr>
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">
                            {{ $operator->name }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            {{ $operator->email }}
                        </td>
                        <td class="px-6 py-4 text-sm">
                            @foreach($operator->roles->where('name', '!=', 'Operator-Vidcon') as $role)
                                <span class="inline-block px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800 mr-1">
                                    {{ $role->name }}
                                </span>
                            @endforeach
                        </td>
                        <td class="px-6 py-4 text-sm font-medium">
                            <form action="{{ route('admin.operators.destroy', $operator) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Hapus {{ $operator->name }} dari operator?')">
                                    Hapus
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-center text-gray-500">Tidak ada operator</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $operators->links() }}
    </div>
</div>
@endsection
