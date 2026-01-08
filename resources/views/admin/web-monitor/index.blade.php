@extends('layouts.authenticated')

@section('title', '- Web Monitor')
@section('header-title', 'Web Monitor')

@section('content')
<div class="p-6">
    <h1 class="text-2xl font-bold mb-4">Monitor Website Resmi Kaltara</h1>

    <div class="mb-4">
        <a href="{{ route('admin.web-monitor.create') }}" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded inline-block">
            + Tambah Website
        </a>
        <button onclick="syncCloudflare()" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded inline-block ml-2">
            🔄 Sync Cloudflare
        </button>
        <button onclick="checkAllStatus()" class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded inline-block ml-2">
            ⭕ Cek Semua Status
        </button>
        <a href="{{ route('admin.web-monitor.check-ip-publik') }}" class="bg-cyan-500 hover:bg-cyan-600 text-white px-4 py-2 rounded inline-block ml-2">
            🔍 Cek IP
        </a>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full table-auto">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">#</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Sumber</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Nama Instansi</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Subdomain</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Domain</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Keterangan</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($data as $item)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-3 text-sm text-gray-900">{{ $loop->iteration }}</td>
                    <td class="px-4 py-3">
                        @if($item->subdomain_request_id)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                                    <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"></path>
                                </svg>
                                Permohonan
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path>
                                </svg>
                                Manual
                            </span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-900">{{ $item->nama_instansi }}</td>
                    <td class="px-4 py-3 text-sm font-mono text-gray-900">{{ $item->subdomain }}</td>
                    <td class="px-4 py-3 text-sm font-mono text-gray-600">{{ $item->domain }}</td>
                    <td class="px-4 py-3">
                        @php
                            $statusColors = [
                                'active' => 'bg-green-100 text-green-800',
                                'down' => 'bg-red-100 text-red-800',
                                'no-domain' => 'bg-orange-100 text-orange-800',
                                'checking' => 'bg-blue-100 text-blue-800',
                            ];
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$item->status] ?? 'bg-gray-100 text-gray-800' }}">
                            @if($item->status === 'active')
                                <span class="w-2 h-2 rounded-full bg-green-600 mr-1.5"></span>
                            @elseif($item->status === 'down')
                                <span class="w-2 h-2 rounded-full bg-red-600 mr-1.5"></span>
                            @endif
                            {{ ucfirst($item->status) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $item->keterangan }}</td>
                    <td class="px-4 py-3 text-sm">
                        <div class="flex items-center gap-2">
                            @if($item->subdomain_request_id)
                                <a href="{{ route('admin.unified-subdomain.show', ['id' => $item->subdomain_request_id, 'type' => 'request']) }}" class="text-purple-600 hover:text-purple-700 font-medium" title="Lihat Detail Permohonan">
                                    <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </a>
                            @endif
                            <a href="{{ route('admin.web-monitor.edit', $item) }}" class="text-blue-600 hover:text-blue-700 font-medium">Edit</a>
                            <span class="text-gray-300">|</span>
                            <form action="{{ route('admin.web-monitor.destroy', $item) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button onclick="return confirm('Yakin hapus data ini?')" class="text-red-600 hover:text-red-700 font-medium">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
