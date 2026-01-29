@extends('layouts.authenticated')

@section('title', 'Permohonan Update Data Kategori Sistem Elektronik dan Klasifikasi Data')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Permohonan Update Data Kategori Sistem Elektronik dan Klasifikasi Data</h1>
            <p class="text-gray-600 mt-1">Kelola permohonan update data Kategori Sistem Elektronik dan Klasifikasi Data subdomain</p>
        </div>
        <a href="{{ route('user.pse-update.create') }}"
           class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-6 rounded-lg shadow-md transition duration-200">
            <i class="fas fa-plus mr-2"></i>Buat Permohonan Baru
        </a>
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

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        @if($requests->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No. Tiket</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subdomain</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Scope Update</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Dibuat</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($requests as $request)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <a href="{{ route('user.pse-update.show', $request->id) }}"
                                       class="text-green-600 hover:text-green-800 font-medium">
                                        {{ $request->ticket_no }}
                                    </a>
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
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $request->created_at->format('d M Y H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center space-x-2">
                                        <a href="{{ route('user.pse-update.show', $request->id) }}"
                                           class="inline-flex items-center gap-2 rounded-lg border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700 hover:border-blue-300 hover:bg-blue-100"
                                           title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                            <span>Lihat Detail</span>
                                        </a>

                                        @if(in_array($request->status, ['draft', 'perlu_revisi']))
                                            <a href="{{ route('user.pse-update.edit', $request->id) }}"
                                               class="text-green-600 hover:text-green-900" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endif

                                        @if($request->status === 'draft')
                                            <form action="{{ route('user.pse-update.submit', $request->id) }}"
                                                  method="POST"
                                                  class="inline"
                                                  onsubmit="return confirm('Apakah Anda yakin ingin mengajukan permohonan ini?')">
                                                @csrf
                                                <button type="submit"
                                                        class="text-yellow-600 hover:text-yellow-900"
                                                        title="Submit Permohonan">
                                                    <i class="fas fa-paper-plane"></i>
                                                </button>
                                            </form>

                                            <form action="{{ route('user.pse-update.destroy', $request->id) }}"
                                                  method="POST"
                                                  class="inline"
                                                  onsubmit="return confirm('Apakah Anda yakin ingin menghapus permohonan ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="text-red-600 hover:text-red-900"
                                                        title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>

                            @if($request->status === 'perlu_revisi' && $request->revision_notes)
                                <tr>
                                    <td colspan="6" class="px-6 py-3 bg-orange-50">
                                        <div class="text-sm">
                                            <span class="font-semibold text-orange-700">Catatan Revisi:</span>
                                            <span class="text-gray-700 ml-2">{{ $request->revision_notes }}</span>
                                        </div>
                                    </td>
                                </tr>
                            @endif
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
                <p class="text-gray-500 text-lg">Belum ada permohonan update data PSE.</p>
                <a href="{{ route('user.pse-update.create') }}"
                   class="inline-block mt-4 bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-6 rounded-lg transition duration-200">
                    Buat Permohonan Pertama
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
