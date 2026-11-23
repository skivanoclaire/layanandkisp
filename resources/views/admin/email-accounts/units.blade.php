@extends('layouts.authenticated')

@section('title', '- Monitoring Email Unit')
@section('header-title', 'Monitoring Email Unit')

@section('content')
<div class="container mx-auto px-4">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Monitoring Email Unit</h1>
            <p class="text-sm text-gray-600 mt-1">Email resmi setiap unit kerja dan unit organisasi.</p>
        </div>
        <a href="{{ route('admin.email-accounts.index', ['jenis' => $tipe]) }}"
           class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded font-semibold">
            Kembali ke Master Data Email
        </a>
    </div>

    <div class="flex gap-2 mb-4">
        <a href="{{ route('admin.email-accounts.units', ['tipe' => 'unit_kerja']) }}"
           class="px-4 py-2 rounded border {{ $tipe === 'unit_kerja' ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-700' }}">
            Unit Kerja (Perangkat Daerah)
        </a>
        <a href="{{ route('admin.email-accounts.units', ['tipe' => 'unit_organisasi']) }}"
           class="px-4 py-2 rounded border {{ $tipe === 'unit_organisasi' ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-700' }}">
            Unit Organisasi (Bidang/Sekretariat)
        </a>
    </div>

    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow-md p-4">
            <div class="text-xs text-gray-500 uppercase">Total unit</div>
            <div class="text-2xl font-bold">{{ $stats['total'] }}</div>
        </div>
        <div class="bg-white rounded-lg shadow-md p-4">
            <div class="text-xs text-gray-500 uppercase">Sudah punya email resmi</div>
            <div class="text-2xl font-bold text-green-700">{{ $stats['terdaftar'] }}</div>
        </div>
        <div class="bg-white rounded-lg shadow-md p-4">
            <div class="text-xs text-gray-500 uppercase">Belum punya email</div>
            <div class="text-2xl font-bold text-gray-600">{{ $stats['belum'] }}</div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Unit</th>
                        @if ($tipe === 'unit_organisasi')
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Tipe</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Perangkat Daerah Induk</th>
                        @endif
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Email Resmi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($rows as $row)
                        <tr>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                {{ $row['nama'] }}
                                @if ($tipe === 'unit_organisasi' && isset($row['is_active']) && !$row['is_active'])
                                    <span class="ml-1 px-2 py-0.5 rounded text-xs bg-gray-200 text-gray-700">Nonaktif</span>
                                @endif
                            </td>
                            @if ($tipe === 'unit_organisasi')
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $row['tipe'] }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $row['induk'] ?? '-' }}</td>
                            @endif
                            <td class="px-6 py-4 text-sm">
                                @if ($row['email'])
                                    <span class="font-mono text-gray-900">{{ $row['email'] }}</span>
                                @else
                                    <span class="text-gray-400 italic">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm">
                                @if ($row['email'])
                                    @if ($row['suspended'])
                                        <span class="px-2 py-1 rounded text-xs bg-red-100 text-red-800">Suspended</span>
                                    @else
                                        <span class="px-2 py-1 rounded text-xs bg-green-100 text-green-800">Aktif</span>
                                    @endif
                                @elseif ($row['pending_ticket'])
                                    <span class="px-2 py-1 rounded text-xs bg-yellow-100 text-yellow-800">
                                        Permohonan {{ $row['pending_status'] }} ({{ $row['pending_ticket'] }})
                                    </span>
                                @else
                                    <span class="px-2 py-1 rounded text-xs bg-gray-100 text-gray-600">Belum diajukan</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500">Belum ada data unit.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
