@extends('layouts.authenticated')
@section('title', '- Permohonan Email')
@section('header-title', 'Permohonan Email')

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

    <div class="bg-white rounded shadow p-4 mb-4">
        <form method="GET" action="{{ route('admin.email.index') }}" class="space-y-4">
            @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="dari_tanggal" class="block text-sm font-medium text-gray-700 mb-1">Dari Tanggal</label>
                    <input type="date" id="dari_tanggal" name="dari_tanggal" value="{{ request('dari_tanggal') }}"
                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label for="sampai_tanggal" class="block text-sm font-medium text-gray-700 mb-1">Sampai Tanggal</label>
                    <input type="date" id="sampai_tanggal" name="sampai_tanggal" value="{{ request('sampai_tanggal') }}"
                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
            </div>

            <div class="flex justify-between items-center">
                <div class="flex gap-2">
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-md">
                        Filter
                    </button>
                    <a href="{{ route('admin.email.index') }}" class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold rounded-md">
                        Reset
                    </a>
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('admin.email.export-excel', request()->query()) }}"
                        class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-md shadow-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Export Excel
                    </a>
                    <a href="{{ route('admin.email.export-pdf', request()->query()) }}"
                        class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-md shadow-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                        Export PDF
                    </a>
                </div>
            </div>
        </form>
    </div>

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
