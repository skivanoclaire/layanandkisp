@extends('layouts.admin')
@section('title', 'Permohonan Email')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-bold">Permohonan Email</h1>
        <div class="flex gap-2">
            <a href="{{ route('admin.email.index') }}" class="px-3 py-1 border rounded">Semua</a>
            <a href="{{ route('admin.email.index', ['status' => 'menunggu']) }}" class="px-3 py-1 border rounded">Menunggu</a>
            <a href="{{ route('admin.email.index', ['status' => 'proses']) }}" class="px-3 py-1 border rounded">Proses</a>
            <a href="{{ route('admin.email.index', ['status' => 'ditolak']) }}" class="px-3 py-1 border rounded">Ditolak</a>
            <a href="{{ route('admin.email.index', ['status' => 'selesai']) }}" class="px-3 py-1 border rounded">Selesai</a>

            <a href="{{ route('admin.email.export', ['status' => 'selesai']) }}"
                class="ml-4 bg-green-600 text-white px-3 py-1 rounded">
                Export CSV (Selesai)
            </a>
        </div>
    </div>

    @if (session('status'))
        <div class="mb-4 rounded border border-green-300 bg-green-50 p-3 text-sm">{{ session('status') }}</div>
    @endif

    <div class="bg-white rounded shadow overflow-x-auto">
        <table class="min-w-full table-auto">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-3 py-2 text-left">Tiket</th>
                    <th class="px-3 py-2 text-left">Nama</th>
                    <th class="px-3 py-2 text-left">Username</th>
                    <th class="px-3 py-2 text-left">Instansi</th>
                    <th class="px-3 py-2 text-left">Status</th>
                    <th class="px-3 py-2 text-left">Diajukan</th>
                    <th class="px-3 py-2">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($items as $it)
                    <tr class="border-b">
                        <td class="px-3 py-2 font-mono">{{ $it->ticket_no }}</td>
                        <td class="px-3 py-2">{{ $it->nama }}</td>
                        <td class="px-3 py-2">{{ $it->username }}@kaltaraprov.go.id</td>
                        <td class="px-3 py-2">{{ $it->instansi }}</td>
                        <td class="px-3 py-2 capitalize">
                            <span
                                class="px-2 py-1 rounded text-xs
          @switch($it->status)
            @case('menunggu') bg-yellow-100 text-yellow-800 @break
            @case('proses')   bg-blue-100 text-blue-800 @break
            @case('ditolak')  bg-red-100 text-red-800 @break
            @case('selesai')  bg-green-100 text-green-800 @break
          @endswitch
        ">{{ $it->status }}</span>
                        </td>
                        <td class="px-3 py-2">{{ optional($it->submitted_at)->format('Y-m-d H:i') }}</td>
                        <td class="px-3 py-2 text-center">
                            <a href="{{ route('admin.email.show', $it->id) }}" class="px-3 py-1 border rounded">Detail</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $items->withQueryString()->links() }}
    </div>
@endsection
