@extends('layouts.app')

@section('content')
<div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold mb-4">Monitor Website Resmi Kaltara</h1>

    @auth
    @if(auth()->user()->role == 'admin')
    <a href="{{ route('web-monitor.create') }}" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded mb-4 inline-block">Tambah Data</a>
    @endif
    @endauth

    <table id="monitorTable" class="min-w-full table-auto border">
        <thead>
            <tr class="bg-gray-100">
                <th class="border px-4 py-2">#</th>
                <th class="border px-4 py-2">Instansi</th>
                <th class="border px-4 py-2">Subdomain</th>
                <th class="border px-4 py-2">Domain</th>
                <th class="border px-4 py-2">Status</th>
                <th class="border px-4 py-2">Keterangan</th>
                @auth
                @if(auth()->user()->role == 'admin')
                <th class="border px-4 py-2">Aksi</th>
                @endif
                @endauth
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $item)
            <tr>
                <td class="border px-4 py-2">{{ $loop->iteration }}</td>
                <td class="border px-4 py-2">{{ $item->nama_instansi }}</td>
                <td class="border px-4 py-2">{{ $item->subdomain }}</td>
                <td class="border px-4 py-2">{{ $item->domain }}</td>
                <td class="border px-4 py-2">
                    <span class="{{ $item->status == 'Aktif' ? 'text-green-600' : 'text-red-600' }}">
                        {{ $item->status }}
                    </span>
                </td>
                <td class="border px-4 py-2">{{ $item->keterangan }}</td>
                @auth
                @if(auth()->user()->role == 'admin')
                <td class="border px-4 py-2">
                    <a href="{{ route('web-monitor.edit', $item->id) }}" class="text-blue-500 hover:underline">Edit</a> |
                    <form action="{{ route('web-monitor.destroy', $item->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Yakin hapus?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-red-500 hover:underline">Hapus</button>
                    </form>
                </td>
                @endif
                @endauth
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@push('scripts')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function () {
        $('#monitorTable').DataTable();
    });
</script>
@endpush

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endpush

@endsection
