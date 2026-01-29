@extends('layouts.authenticated')

@section('title', 'Pilih Subdomain untuk Update Data Kategori Sistem Elektronik dan Klasifikasi Data')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <a href="{{ route('user.pse-update.index') }}"
           class="text-green-600 hover:text-green-800 font-medium">
            <i class="fas fa-arrow-left mr-2"></i>Kembali ke Daftar Permohonan
        </a>
    </div>

    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Pilih Subdomain</h1>
        <p class="text-gray-600 mt-1">Pilih subdomain yang ingin Anda update data Kategori Sistem Elektronik dan/atau Klasifikasi Data-nya</p>
    </div>

    @if($webMonitors->count() > 0)
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Subdomain</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori Sistem Elektronik</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Klasifikasi Data</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($webMonitors as $monitor)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $monitor->subdomain }}</div>
                                    <div class="text-sm text-gray-500">{{ $monitor->nama_aplikasi ?? '-' }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    @if($monitor->esc_category)
                                        @php
                                            $escColors = [
                                                'Strategis' => 'bg-red-100 text-red-700',
                                                'Tinggi' => 'bg-orange-100 text-orange-700',
                                                'Rendah' => 'bg-green-100 text-green-700',
                                            ];
                                            $escColor = $escColors[$monitor->esc_category] ?? 'bg-gray-100 text-gray-700';
                                        @endphp
                                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded {{ $escColor }}">
                                            {{ $monitor->esc_category }}
                                        </span>
                                        <div class="text-xs text-gray-500 mt-1">Skor: {{ $monitor->esc_total_score ?? 0 }}/50</div>
                                    @else
                                        <span class="text-sm text-gray-400 italic">Belum diisi</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if($monitor->dc_data_name)
                                        <div class="text-sm font-medium text-gray-900">{{ $monitor->dc_data_name }}</div>
                                        <div class="flex gap-1 mt-1">
                                            @if($monitor->dc_confidentiality)
                                                <span class="text-xs px-1.5 py-0.5 rounded bg-gray-100 text-gray-600">Kerahasiaan: {{ $monitor->dc_confidentiality }}</span>
                                            @endif
                                            @if($monitor->dc_integrity)
                                                <span class="text-xs px-1.5 py-0.5 rounded bg-gray-100 text-gray-600">Integritas: {{ $monitor->dc_integrity }}</span>
                                            @endif
                                            @if($monitor->dc_availability)
                                                <span class="text-xs px-1.5 py-0.5 rounded bg-gray-100 text-gray-600">Ketersediaan: {{ $monitor->dc_availability }}</span>
                                            @endif
                                        </div>
                                        <div class="text-xs text-gray-500 mt-1">Skor: {{ $monitor->dc_total_score ?? 0 }}/15</div>
                                    @else
                                        <span class="text-sm text-gray-400 italic">Belum diisi</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="{{ route('user.pse-update.create-form', $monitor->id) }}"
                                       class="inline-flex items-center px-3 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition duration-200">
                                        <i class="fas fa-edit mr-2"></i>Update Data
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="bg-white rounded-lg shadow-md p-12 text-center">
            <i class="fas fa-exclamation-circle text-gray-400 text-6xl mb-4"></i>
            <h2 class="text-xl font-semibold text-gray-700 mb-2">Belum Ada Subdomain</h2>
            <p class="text-gray-500 mb-4">
                Belum ada subdomain yang terdaftar di sistem.<br>
                Silakan hubungi administrator untuk informasi lebih lanjut.
            </p>
        </div>
    @endif
</div>
@endsection
