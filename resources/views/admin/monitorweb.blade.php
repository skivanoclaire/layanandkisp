@extends('layouts.authenticated')

@section('title', '- Monitor Web')
@section('header-title', 'Monitor Web')

@section('content')
<div class="p-6">
    <h1 class="text-2xl font-bold mb-4">Monitor Website Resmi Kaltara</h1>

    @if(session('success'))
        <div class="bg-green-100 text-green-800 p-2 rounded mb-4">{{ session('success') }}</div>
    @endif
    <a href="{{ route('admin.monitorweb.create') }}" class="bg-green-500 text-white px-4 py-2 rounded mb-4 inline-block">Tambah Data</a>

    <table class="w-full border">
        <thead>
            <tr class="bg-gray-100">
                <th class="text-left px-2 py-1">No</th>
                <th class="text-left px-2 py-1">Nama Instansi</th>
                <th class="text-left px-2 py-1">Subdomain</th>
                <th class="text-left px-2 py-1">Status</th>
                <th class="text-left px-2 py-1">Keterangan</th>
                <th class="text-left px-2 py-1">Jenis</th>
                <th class="text-left px-2 py-1">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $item)
            <tr>
                <td class="text-left px-2 py-1">{{ $loop->iteration }}</td>
                <td class="text-left px-2 py-1">{{ $item->nama_instansi }}</td>
                <td class="text-left px-2 py-1">{{ $item->subdomain }}</td>
                <td class="text-left px-2 py-1">{{ $item->status }}</td>
                <td class="text-left px-2 py-1">{{ $item->keterangan }}</td>
                <td class="text-left px-2 py-1">{{ $item->jenis }}</td>
                <td class="text-left px-2 py-1">
                    <a href="{{ route('admin.monitorweb.edit', $item->id) }}" class="text-blue-600">Edit</a> |
                    <form action="{{ route('admin.monitorweb.delete', $item->id) }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button onclick="return confirm('Yakin hapus data ini?')" class="text-red-600">Hapus</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
