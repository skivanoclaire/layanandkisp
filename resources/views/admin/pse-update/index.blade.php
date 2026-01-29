@extends('layouts.authenticated')

@section('title', 'Kelola Permohonan Update Kategori Sistem Elektronik dan Klasifikasi Data')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Kelola Permohonan Update Kategori Sistem Elektronik dan Klasifikasi Data</h1>
        <p class="text-gray-600 mt-1">Kelola permohonan update data Kategori Sistem Elektronik dan Klasifikasi Data subdomain</p>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded">
            <p class="font-medium">{{ session('success') }}</p>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded">
            <p class="font-medium">{{ session('error') }}</p>
        </div>
    @endif

    {{-- Filters --}}
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <form method="GET" action="{{ route('admin.pse-update.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        <option value="">Semua Status</option>
                        <option value="diajukan" {{ request('status') === 'diajukan' ? 'selected' : '' }}>Diajukan</option>
                        <option value="diproses" {{ request('status') === 'diproses' ? 'selected' : '' }}>Diproses</option>
                        <option value="perlu_revisi" {{ request('status') === 'perlu_revisi' ? 'selected' : '' }}>Perlu Revisi</option>
                        <option value="disetujui" {{ request('status') === 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                        <option value="ditolak" {{ request('status') === 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Dari</label>
                    <input type="date"
                           name="date_from"
                           value="{{ request('date_from') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Sampai</label>
                    <input type="date"
                           name="date_to"
                           value="{{ request('date_to') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Pencarian</label>
                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="Tiket, subdomain, pemohon..."
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                </div>
            </div>

            <div class="flex gap-2">
                <button type="submit"
                        class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-6 rounded-lg transition duration-200">
                    <i class="fas fa-search mr-2"></i>Filter
                </button>
                <a href="{{ route('admin.pse-update.index') }}"
                   class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-6 rounded-lg transition duration-200">
                    <i class="fas fa-redo mr-2"></i>Reset
                </a>
            </div>
        </form>
    </div>

    {{-- Statistics Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
        @php
            $statusConfigs = [
                'diajukan' => ['label' => 'Diajukan', 'color' => 'bg-yellow-100 text-yellow-700', 'icon' => 'fa-paper-plane'],
                'diproses' => ['label' => 'Diproses', 'color' => 'bg-blue-100 text-blue-700', 'icon' => 'fa-cog'],
                'perlu_revisi' => ['label' => 'Perlu Revisi', 'color' => 'bg-orange-100 text-orange-700', 'icon' => 'fa-exclamation-triangle'],
                'disetujui' => ['label' => 'Disetujui', 'color' => 'bg-green-100 text-green-700', 'icon' => 'fa-check-circle'],
                'ditolak' => ['label' => 'Ditolak', 'color' => 'bg-red-100 text-red-700', 'icon' => 'fa-times-circle'],
            ];
        @endphp

        @foreach($statusConfigs as $status => $config)
            <div class="bg-white rounded-lg shadow-md p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">{{ $config['label'] }}</p>
                        <p class="text-2xl font-bold text-gray-800">{{ $stats[$status] ?? 0 }}</p>
                    </div>
                    <div class="rounded-full p-3 {{ $config['color'] }}">
                        <i class="fas {{ $config['icon'] }}"></i>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        @if($requests->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No. Tiket</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pemohon</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subdomain</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Scope</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($requests as $request)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <a href="{{ route('admin.pse-update.show', $request->id) }}"
                                       class="text-green-600 hover:text-green-800 font-medium">
                                        {{ $request->ticket_no }}
                                    </a>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $request->user->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $request->user->unitKerja->nama ?? '-' }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $request->webMonitor->subdomain }}</div>
                                    <div class="text-sm text-gray-500">{{ $request->webMonitor->nama_aplikasi ?? '-' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex gap-1">
                                        @if($request->update_esc)
                                            <span class="px-2 py-1 text-xs font-semibold rounded bg-blue-100 text-blue-700">ESC</span>
                                        @endif
                                        @if($request->update_dc)
                                            <span class="px-2 py-1 text-xs font-semibold rounded bg-purple-100 text-purple-700">DC</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $statusConfig = [
                                            'draft' => ['label' => 'Draft', 'color' => 'bg-gray-100 text-gray-700'],
                                            'diajukan' => ['label' => 'Diajukan', 'color' => 'bg-yellow-100 text-yellow-700'],
                                            'diproses' => ['label' => 'Diproses', 'color' => 'bg-blue-100 text-blue-700'],
                                            'perlu_revisi' => ['label' => 'Perlu Revisi', 'color' => 'bg-orange-100 text-orange-700'],
                                            'disetujui' => ['label' => 'Disetujui', 'color' => 'bg-green-100 text-green-700'],
                                            'ditolak' => ['label' => 'Ditolak', 'color' => 'bg-red-100 text-red-700'],
                                        ];
                                        $config = $statusConfig[$request->status] ?? ['label' => $request->status, 'color' => 'bg-gray-100 text-gray-700'];
                                    @endphp
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $config['color'] }}">
                                        {{ $config['label'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $request->created_at->format('d M Y') }}</div>
                                    <div class="text-sm text-gray-500">{{ $request->created_at->format('H:i') }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="{{ route('admin.pse-update.show', $request->id) }}"
                                       class="inline-flex items-center gap-2 rounded-lg border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700 hover:border-blue-300 hover:bg-blue-100"
                                       title="Lihat Detail">
                                        <i class="fas fa-eye"></i>
                                        <span>Lihat Detail</span>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                {{ $requests->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <i class="fas fa-inbox text-gray-400 text-6xl mb-4"></i>
                <p class="text-gray-500 text-lg">
                    @if(request()->hasAny(['status', 'date_from', 'date_to', 'search']))
                        Tidak ada permohonan yang sesuai dengan filter.
                    @else
                        Belum ada permohonan update data PSE.
                    @endif
                </p>
            </div>
        @endif
    </div>
</div>
@endsection
