@extends('layouts.admin')

@section('content')
<div class="p-6">
    <h1 class="text-2xl font-bold mb-4">Monitor Website Resmi Kaltara</h1>

    <a href="{{ route('admin.web-monitor.create') }}" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded mb-4 inline-block">Tambah Data</a>

    <table class="w-full border">
        <thead>
            <tr>
                <th>#</th>
                <th>Nama Instansi</th>
                <th>Subdomain</th>
                <th>Domain</th>
                <th>Status</th>
                <th>Keterangan</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $item)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $item->nama_instansi }}</td>
                <td>{{ $item->subdomain }}</td>
                <td>{{ $item->domain }}</td>
                <td>{{ $item->status }}</td>
                <td>{{ $item->keterangan }}</td>
                <td>
                    <a href="{{ route('admin.web-monitor.edit', $item) }}" class="text-blue-600">Edit</a> |
                    <form action="{{ route('admin.web-monitor.destroy', $item) }}" method="POST" class="inline">
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
