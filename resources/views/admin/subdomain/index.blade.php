@extends('layouts.authenticated')
@section('title', '- Permohonan Subdomain')
@section('header-title', 'Permohonan Subdomain')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-bold">Permohonan Subdomain</h1>
        <div class="flex gap-2">
            <a href="{{ route('admin.subdomain.index') }}"
                class="px-3 py-1 border rounded {{ !request('status') ? 'bg-green-600 text-white' : 'hover:bg-gray-100' }}">
                Semua
            </a>
            <a href="{{ route('admin.subdomain.index', ['status' => 'menunggu']) }}"
                class="px-3 py-1 border rounded {{ request('status') == 'menunggu' ? 'bg-yellow-600 text-white' : 'hover:bg-gray-100' }}">
                Menunggu
            </a>
            <a href="{{ route('admin.subdomain.index', ['status' => 'proses']) }}"
                class="px-3 py-1 border rounded {{ request('status') == 'proses' ? 'bg-blue-600 text-white' : 'hover:bg-gray-100' }}">
                Proses
            </a>
            <a href="{{ route('admin.subdomain.index', ['status' => 'ditolak']) }}"
                class="px-3 py-1 border rounded {{ request('status') == 'ditolak' ? 'bg-red-600 text-white' : 'hover:bg-gray-100' }}">
                Ditolak
            </a>
            <a href="{{ route('admin.subdomain.index', ['status' => 'selesai']) }}"
                class="px-3 py-1 border rounded {{ request('status') == 'selesai' ? 'bg-green-600 text-white' : 'hover:bg-gray-100' }}">
                Selesai
            </a>

            <a href="{{ route('admin.subdomain.export', ['status' => request('status', 'selesai')]) }}"
                class="ml-4 bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700">
                Export CSV
            </a>
        </div>
    </div>

    @if (session('status'))
        <div class="mb-4 rounded border border-green-300 bg-green-50 p-3 text-sm">
            {{ session('status') }}
        </div>
    @endif

    <div class="bg-white rounded shadow p-4 mb-4">
        <form method="GET" action="{{ route('admin.subdomain.index') }}" class="space-y-4">
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
                    <a href="{{ route('admin.subdomain.index') }}" class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold rounded-md">
                        Reset
                    </a>
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('admin.subdomain.export-excel', request()->query()) }}"
                        class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-md shadow-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Export Excel
                    </a>
                    <a href="{{ route('admin.subdomain.export-pdf', request()->query()) }}"
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

    @if ($items->count() === 0)
        <div class="bg-white rounded shadow p-6 text-center text-gray-500">
            Tidak ada permohonan subdomain{{ request('status') ? ' dengan status ' . request('status') : '' }}.
        </div>
    @else
        <div class="bg-white rounded shadow overflow-x-auto">
            <table class="min-w-full table-auto text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-3 py-2 text-left">Tiket</th>
                        <th class="px-3 py-2 text-left">Pemohon</th>
                        <th class="px-3 py-2 text-left">Subdomain</th>
                        <th class="px-3 py-2 text-left">IP Address</th>
                        <th class="px-3 py-2 text-left">Aplikasi</th>
                        <th class="px-3 py-2 text-left">Kategori SE</th>
                        <th class="px-3 py-2 text-left">Tech Stack</th>
                        <th class="px-3 py-2 text-left">Status</th>
                        <th class="px-3 py-2 text-left">Diajukan</th>
                        <th class="px-3 py-2">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($items as $it)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-3 py-2 font-mono">{{ $it->ticket_no }}</td>
                            <td class="px-3 py-2">
                                <div class="font-medium">{{ $it->nama }}</div>
                                <div class="text-xs text-gray-500">{{ $it->unitKerja->nama ?? $it->instansi }}</div>
                            </td>
                            <td class="px-3 py-2">
                                <div class="font-semibold">{{ $it->subdomain_requested }}</div>
                                <div class="text-xs text-gray-500">.kaltaraprov.go.id</div>
                            </td>
                            <td class="px-3 py-2 font-mono">{{ $it->ip_address }}</td>
                            <td class="px-3 py-2">
                                <div class="font-medium">{{ $it->nama_aplikasi }}</div>
                                <div class="text-xs text-gray-500">{{ $it->jenis_website }}</div>
                            </td>
                            <td class="px-3 py-2">
                                @if($it->esc_category)
                                    <span class="px-2 py-1 rounded text-xs font-semibold whitespace-nowrap
                                        {{ $it->esc_category === 'Strategis' ? 'bg-red-100 text-red-800' : '' }}
                                        {{ $it->esc_category === 'Tinggi' ? 'bg-orange-100 text-orange-800' : '' }}
                                        {{ $it->esc_category === 'Rendah' ? 'bg-green-100 text-green-800' : '' }}">
                                        {{ $it->esc_category }}
                                    </span>
                                    <div class="text-xs text-gray-500 mt-1">{{ $it->esc_total_score }}/50</div>
                                @else
                                    <span class="text-gray-400 text-xs">Belum diisi</span>
                                @endif
                            </td>
                            <td class="px-3 py-2">
                                <div class="text-xs">
                                    <div>{{ $it->programmingLanguage->name ?? '-' }}</div>
                                    <div class="text-gray-500">{{ $it->framework->name ?? 'No framework' }}</div>
                                    <div class="text-gray-500">{{ $it->database->name ?? '-' }}</div>
                                </div>
                            </td>
                            <td class="px-3 py-2">
                                <span class="px-2 py-1 rounded text-xs whitespace-nowrap
                                    @switch($it->status)
                                        @case('menunggu') bg-yellow-100 text-yellow-800 @break
                                        @case('proses')   bg-blue-100 text-blue-800 @break
                                        @case('ditolak')  bg-red-100 text-red-800 @break
                                        @case('selesai')  bg-green-100 text-green-800 @break
                                    @endswitch
                                ">{{ ucfirst($it->status) }}</span>
                            </td>
                            <td class="px-3 py-2 whitespace-nowrap">
                                {{ optional($it->submitted_at)->format('Y-m-d H:i') }}
                            </td>
                            <td class="px-3 py-2 text-center">
                                <a href="{{ route('admin.subdomain.show', $it->id) }}"
                                    class="inline-block px-3 py-1 border rounded hover:bg-gray-100 font-semibold">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $items->withQueryString()->links() }}
        </div>
    @endif
@endsection
