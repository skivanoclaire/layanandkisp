@extends('layouts.authenticated')

@section('title', 'Detail Subdomain - ' . ($request ? $request->subdomain_requested : $monitor->subdomain))

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-3xl font-bold text-gray-800 mb-2">
                    {{ $type === 'request' ? $request->subdomain_requested : $monitor->subdomain }}.kaltaraprov.go.id
                </h1>
                <div class="flex items-center gap-3 flex-wrap">
                    <!-- Source Badge -->
                    @if($type === 'request')
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                                <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"></path>
                            </svg>
                            Dari Permohonan
                        </span>
                    @else
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path>
                            </svg>
                            Manual Entry
                        </span>
                    @endif

                    <!-- Status Permohonan -->
                    @if($type === 'request')
                        @php
                            $statusColors = [
                                'menunggu' => 'bg-yellow-100 text-yellow-800',
                                'proses' => 'bg-blue-100 text-blue-800',
                                'selesai' => 'bg-green-100 text-green-800',
                                'ditolak' => 'bg-red-100 text-red-800',
                            ];
                            $statusText = [
                                'menunggu' => 'Menunggu',
                                'proses' => 'Proses',
                                'selesai' => 'Selesai',
                                'ditolak' => 'Ditolak',
                            ];
                        @endphp
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $statusColors[$request->status] ?? 'bg-gray-100 text-gray-800' }}">
                            {{ $statusText[$request->status] ?? ucfirst($request->status) }}
                        </span>
                    @endif

                    <!-- Status Monitoring -->
                    @if($has_monitor && $monitor)
                        @php
                            $monitorColors = [
                                'active' => 'bg-green-100 text-green-800',
                                'down' => 'bg-red-100 text-red-800',
                                'no-domain' => 'bg-orange-100 text-orange-800',
                                'checking' => 'bg-blue-100 text-blue-800',
                            ];
                        @endphp
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $monitorColors[$monitor->status] ?? 'bg-gray-100 text-gray-800' }}">
                            <span class="w-2 h-2 rounded-full mr-2 {{ $monitor->status === 'active' ? 'bg-green-600' : 'bg-red-600' }}"></span>
                            {{ ucfirst($monitor->status) }}
                        </span>

                        @if($monitor->is_active)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-50 text-green-700 border border-green-200">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                Aktif
                            </span>
                        @else
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-50 text-gray-700 border border-gray-200">
                                Nonaktif
                            </span>
                        @endif
                    @endif
                </div>
            </div>

            <div class="flex gap-2">
                <a href="{{ route('admin.unified-subdomain.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-lg transition">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Kembali
                </a>

                @if($has_monitor)
                    <button onclick="checkStatus()" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Check Status
                    </button>
                @endif
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div x-data="{ activeTab: 'info' }" class="bg-white rounded-lg shadow">
        <!-- Tab Navigation -->
        <div class="border-b border-gray-200">
            <nav class="flex flex-wrap -mb-px">
                @if($type === 'request')
                    <button @click="activeTab = 'info'" :class="activeTab === 'info' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="px-6 py-4 border-b-2 font-medium text-sm whitespace-nowrap">
                        <svg class="w-5 h-5 inline-block mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                            <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"></path>
                        </svg>
                        Info Permohonan
                    </button>
                @endif

                <button @click="activeTab = 'teknis'" :class="activeTab === 'teknis' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="px-6 py-4 border-b-2 font-medium text-sm whitespace-nowrap">
                    <svg class="w-5 h-5 inline-block mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M12.316 3.051a1 1 0 01.633 1.265l-4 12a1 1 0 11-1.898-.632l4-12a1 1 0 011.265-.633zM5.707 6.293a1 1 0 010 1.414L3.414 10l2.293 2.293a1 1 0 11-1.414 1.414l-3-3a1 1 0 010-1.414l3-3a1 1 0 011.414 0zm8.586 0a1 1 0 011.414 0l3 3a1 1 0 010 1.414l-3 3a1 1 0 11-1.414-1.414L16.586 10l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                    Info Teknis
                </button>

                @if($has_monitor)
                    <button @click="activeTab = 'monitoring'" :class="activeTab === 'monitoring' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="px-6 py-4 border-b-2 font-medium text-sm whitespace-nowrap">
                        <svg class="w-5 h-5 inline-block mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"></path>
                        </svg>
                        Monitoring
                    </button>

                    <button @click="activeTab = 'cloudflare'" :class="activeTab === 'cloudflare' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="px-6 py-4 border-b-2 font-medium text-sm whitespace-nowrap">
                        <svg class="w-5 h-5 inline-block mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"></path>
                        </svg>
                        Cloudflare
                    </button>
                @endif

                @if($type === 'request')
                    <button @click="activeTab = 'logs'" :class="activeTab === 'logs' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="px-6 py-4 border-b-2 font-medium text-sm whitespace-nowrap">
                        <svg class="w-5 h-5 inline-block mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                        </svg>
                        History & Logs
                    </button>
                @endif
            </nav>
        </div>

        <!-- Tab Content -->
        <div class="p-6">
            <!-- Tab 1: Info Permohonan (only for requests) -->
            @if($type === 'request')
                <div x-show="activeTab === 'info'" x-cloak>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Left Column -->
                        <div class="space-y-6">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b">Informasi Permohonan</h3>
                                <dl class="space-y-3">
                                    <div class="flex justify-between">
                                        <dt class="text-sm font-medium text-gray-500">Nomor Tiket:</dt>
                                        <dd class="text-sm text-gray-900 font-mono">{{ $request->ticket_no }}</dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-sm font-medium text-gray-500">Status:</dt>
                                        <dd class="text-sm">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$request->status] ?? 'bg-gray-100 text-gray-800' }}">
                                                {{ $statusText[$request->status] ?? ucfirst($request->status) }}
                                            </span>
                                        </dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-sm font-medium text-gray-500">Tanggal Pengajuan:</dt>
                                        <dd class="text-sm text-gray-900">{{ $request->submitted_at ? $request->submitted_at->format('d M Y H:i') : '-' }}</dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-sm font-medium text-gray-500">Tanggal Selesai:</dt>
                                        <dd class="text-sm text-gray-900">{{ $request->completed_at ? $request->completed_at->format('d M Y H:i') : '-' }}</dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-sm font-medium text-gray-500">Pemohon:</dt>
                                        <dd class="text-sm text-gray-900">{{ $request->nama }}</dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-sm font-medium text-gray-500">NIK:</dt>
                                        <dd class="text-sm text-gray-900 font-mono">{{ $request->nik }}</dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-sm font-medium text-gray-500">Instansi:</dt>
                                        <dd class="text-sm text-gray-900">{{ optional($request->unitKerja)->nama ?? $request->instansi }}</dd>
                                    </div>
                                </dl>
                            </div>

                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b">Informasi Subdomain</h3>
                                <dl class="space-y-3">
                                    <div class="flex justify-between">
                                        <dt class="text-sm font-medium text-gray-500">Subdomain Diminta:</dt>
                                        <dd class="text-sm text-gray-900 font-mono">{{ $request->subdomain_requested }}.kaltaraprov.go.id</dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-sm font-medium text-gray-500">IP Address:</dt>
                                        <dd class="text-sm text-gray-900 font-mono">{{ $request->ip_address }}</dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-sm font-medium text-gray-500">Jenis Website:</dt>
                                        <dd class="text-sm text-gray-900">{{ ucfirst($request->jenis_website) }}</dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-sm font-medium text-gray-500">Nama Aplikasi:</dt>
                                        <dd class="text-sm text-gray-900">{{ $request->nama_aplikasi }}</dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-sm font-medium text-gray-500">Deskripsi Aplikasi:</dt>
                                        <dd class="text-sm text-gray-900">{{ $request->deskripsi_aplikasi }}</dd>
                                    </div>
                                </dl>
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="space-y-6">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b">Informasi Kontak</h3>
                                <dl class="space-y-3">
                                    <div class="flex justify-between">
                                        <dt class="text-sm font-medium text-gray-500">Developer:</dt>
                                        <dd class="text-sm text-gray-900">{{ $request->developer }}</dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-sm font-medium text-gray-500">Contact Person:</dt>
                                        <dd class="text-sm text-gray-900">{{ $request->contact_person }}</dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-sm font-medium text-gray-500">No. HP:</dt>
                                        <dd class="text-sm text-gray-900 font-mono">{{ $request->contact_phone }}</dd>
                                    </div>
                                </dl>
                            </div>

                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b">Informasi Server</h3>
                                <dl class="space-y-3">
                                    <div class="flex justify-between">
                                        <dt class="text-sm font-medium text-gray-500">Kepemilikan Server:</dt>
                                        <dd class="text-sm text-gray-900">{{ ucfirst($request->server_ownership) }}</dd>
                                    </div>
                                    @if($request->server_ownership === 'pribadi')
                                        <div class="flex justify-between">
                                            <dt class="text-sm font-medium text-gray-500">Nama Pemilik:</dt>
                                            <dd class="text-sm text-gray-900">{{ $request->server_owner_name }}</dd>
                                        </div>
                                    @endif
                                    <div class="flex justify-between">
                                        <dt class="text-sm font-medium text-gray-500">Lokasi Server:</dt>
                                        <dd class="text-sm text-gray-900">{{ optional($request->serverLocation)->name ?? '-' }}</dd>
                                    </div>
                                </dl>
                            </div>

                            @if($request->admin_notes)
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b">Catatan Admin</h3>
                                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                        <p class="text-sm text-gray-700">{{ $request->admin_notes }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <!-- Tab 2: Info Teknis -->
            <div x-show="activeTab === 'teknis'" x-cloak>
                @php
                    $source = $type === 'request' ? $request : $monitor;
                @endphp

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Programming Language -->
                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-6 border border-blue-200">
                        <div class="flex items-center mb-4">
                            <div class="p-3 bg-blue-600 rounded-lg">
                                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M12.316 3.051a1 1 0 01.633 1.265l-4 12a1 1 0 11-1.898-.632l4-12a1 1 0 011.265-.633zM5.707 6.293a1 1 0 010 1.414L3.414 10l2.293 2.293a1 1 0 11-1.414 1.414l-3-3a1 1 0 010-1.414l3-3a1 1 0 011.414 0zm8.586 0a1 1 0 011.414 0l3 3a1 1 0 010 1.414l-3 3a1 1 0 11-1.414-1.414L16.586 10l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <h3 class="ml-3 text-lg font-semibold text-gray-900">Programming Language</h3>
                        </div>
                        <div class="space-y-2">
                            <p class="text-2xl font-bold text-blue-900">{{ optional($source->programmingLanguage)->name ?? '-' }}</p>
                            @if($source->programming_language_version)
                                <p class="text-sm text-gray-600">Version: <span class="font-mono">{{ $source->programming_language_version }}</span></p>
                            @endif
                        </div>
                    </div>

                    <!-- Framework -->
                    <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg p-6 border border-purple-200">
                        <div class="flex items-center mb-4">
                            <div class="p-3 bg-purple-600 rounded-lg">
                                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z"></path>
                                </svg>
                            </div>
                            <h3 class="ml-3 text-lg font-semibold text-gray-900">Framework</h3>
                        </div>
                        <div class="space-y-2">
                            <p class="text-2xl font-bold text-purple-900">{{ optional($source->framework)->name ?? '-' }}</p>
                            @if($source->framework_version)
                                <p class="text-sm text-gray-600">Version: <span class="font-mono">{{ $source->framework_version }}</span></p>
                            @endif
                        </div>
                    </div>

                    <!-- Database -->
                    <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-6 border border-green-200">
                        <div class="flex items-center mb-4">
                            <div class="p-3 bg-green-600 rounded-lg">
                                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M3 12v3c0 1.657 3.134 3 7 3s7-1.343 7-3v-3c0 1.657-3.134 3-7 3s-7-1.343-7-3z"></path>
                                    <path d="M3 7v3c0 1.657 3.134 3 7 3s7-1.343 7-3V7c0 1.657-3.134 3-7 3S3 8.657 3 7z"></path>
                                    <path d="M17 5c0 1.657-3.134 3-7 3S3 6.657 3 5s3.134-3 7-3 7 1.343 7 3z"></path>
                                </svg>
                            </div>
                            <h3 class="ml-3 text-lg font-semibold text-gray-900">Database</h3>
                        </div>
                        <div class="space-y-2">
                            <p class="text-2xl font-bold text-green-900">{{ optional($source->database)->name ?? '-' }}</p>
                            @if($source->database_version)
                                <p class="text-sm text-gray-600">Version: <span class="font-mono">{{ $source->database_version }}</span></p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Frontend Tech -->
                @if($source->frontend_tech)
                    <div class="mt-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">Frontend Technologies</h3>
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <p class="text-gray-700">{{ $source->frontend_tech }}</p>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Tab 3: Monitoring -->
            @if($has_monitor)
                <div x-show="activeTab === 'monitoring'" x-cloak>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Status Card -->
                        <div class="bg-white border border-gray-200 rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Status Monitoring</h3>
                            <div class="space-y-4">
                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                    <div class="flex items-center">
                                        <div class="w-12 h-12 rounded-full {{ $monitor->status === 'active' ? 'bg-green-100' : 'bg-red-100' }} flex items-center justify-center">
                                            @if($monitor->status === 'active')
                                                <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                                </svg>
                                            @else
                                                <svg class="w-6 h-6 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                                </svg>
                                            @endif
                                        </div>
                                        <div class="ml-4">
                                            <p class="text-sm font-medium text-gray-500">Current Status</p>
                                            <p class="text-xl font-bold {{ $monitor->status === 'active' ? 'text-green-600' : 'text-red-600' }}">
                                                {{ ucfirst($monitor->status) }}
                                            </p>
                                        </div>
                                    </div>
                                    <button onclick="checkStatus()" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded-lg transition">
                                        Check Now
                                    </button>
                                </div>

                                <dl class="space-y-3">
                                    <div class="flex justify-between border-b pb-2">
                                        <dt class="text-sm font-medium text-gray-500">Last Checked:</dt>
                                        <dd class="text-sm text-gray-900" id="last-checked">
                                            {{ $monitor->last_checked_at ? $monitor->last_checked_at->format('d M Y H:i:s') : 'Belum pernah dicek' }}
                                        </dd>
                                    </div>
                                    <div class="flex justify-between border-b pb-2">
                                        <dt class="text-sm font-medium text-gray-500">Response Time:</dt>
                                        <dd class="text-sm text-gray-900">
                                            {{ $monitor->response_time ? number_format($monitor->response_time, 2) . ' ms' : '-' }}
                                        </dd>
                                    </div>
                                    <div class="flex justify-between border-b pb-2">
                                        <dt class="text-sm font-medium text-gray-500">HTTP Code:</dt>
                                        <dd class="text-sm">
                                            @if($monitor->http_code)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium font-mono {{ $monitor->http_code >= 200 && $monitor->http_code < 300 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                    {{ $monitor->http_code }}
                                                </span>
                                            @else
                                                -
                                            @endif
                                        </dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-sm font-medium text-gray-500">Is Active:</dt>
                                        <dd class="text-sm">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $monitor->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                                {{ $monitor->is_active ? 'Yes' : 'No' }}
                                            </span>
                                        </dd>
                                    </div>
                                </dl>
                            </div>
                        </div>

                        <!-- Error Info -->
                        <div class="bg-white border border-gray-200 rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Error Information</h3>
                            @if($monitor->check_error)
                                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                                    <div class="flex">
                                        <svg class="w-5 h-5 text-red-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                        </svg>
                                        <div class="ml-3">
                                            <h4 class="text-sm font-medium text-red-800 mb-1">Error Details</h4>
                                            <p class="text-sm text-red-700">{{ $monitor->check_error }}</p>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="bg-green-50 border border-green-200 rounded-lg p-4 text-center">
                                    <svg class="w-12 h-12 text-green-600 mx-auto mb-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                    <p class="text-sm text-green-800 font-medium">No errors detected</p>
                                </div>
                            @endif

                            @if($monitor->ssl_valid_until)
                                <div class="mt-4">
                                    <h4 class="text-sm font-medium text-gray-700 mb-2">SSL Certificate</h4>
                                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                                        <p class="text-sm text-gray-700">
                                            Valid until: <span class="font-medium">{{ $monitor->ssl_valid_until->format('d M Y') }}</span>
                                        </p>
                                        @php
                                            $daysRemaining = now()->diffInDays($monitor->ssl_valid_until, false);
                                        @endphp
                                        @if($daysRemaining < 30 && $daysRemaining > 0)
                                            <p class="text-xs text-orange-600 mt-1">⚠️ Certificate expires in {{ $daysRemaining }} days</p>
                                        @elseif($daysRemaining <= 0)
                                            <p class="text-xs text-red-600 mt-1">❌ Certificate has expired</p>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <!-- Tab 4: Cloudflare -->
            @if($has_monitor)
                <div x-show="activeTab === 'cloudflare'" x-cloak>
                    <div class="max-w-2xl">
                        <div class="bg-white border border-gray-200 rounded-lg p-6">
                            <div class="flex items-center mb-6">
                                <svg class="w-8 h-8 text-orange-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"></path>
                                </svg>
                                <h3 class="text-xl font-bold text-gray-900">Cloudflare Configuration</h3>
                            </div>

                            <dl class="space-y-4">
                                <div class="flex justify-between items-center border-b pb-3">
                                    <dt class="text-sm font-medium text-gray-500">Record ID:</dt>
                                    <dd class="text-sm text-gray-900 font-mono">
                                        {{ $monitor->cloudflare_record_id ?? '-' }}
                                    </dd>
                                </div>

                                <div class="flex justify-between items-center border-b pb-3">
                                    <dt class="text-sm font-medium text-gray-500">Subdomain:</dt>
                                    <dd class="text-sm text-gray-900 font-mono">
                                        {{ $monitor->subdomain }}.kaltaraprov.go.id
                                    </dd>
                                </div>

                                <div class="flex justify-between items-center border-b pb-3">
                                    <dt class="text-sm font-medium text-gray-500">IP Address:</dt>
                                    <dd class="text-sm text-gray-900 font-mono">{{ $monitor->ip_address }}</dd>
                                </div>

                                <div class="flex justify-between items-center border-b pb-3">
                                    <dt class="text-sm font-medium text-gray-500">Proxy Status:</dt>
                                    <dd class="text-sm">
                                        @if($monitor->is_proxied)
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path>
                                                </svg>
                                                Proxied (Protected)
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                DNS Only
                                            </span>
                                        @endif
                                    </dd>
                                </div>

                                <div class="flex justify-between items-center">
                                    <dt class="text-sm font-medium text-gray-500">Record Type:</dt>
                                    <dd class="text-sm">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 font-mono">
                                            A
                                        </span>
                                    </dd>
                                </div>
                            </dl>

                            @if(!$monitor->cloudflare_record_id)
                                <div class="mt-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                    <div class="flex">
                                        <svg class="w-5 h-5 text-yellow-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                        </svg>
                                        <div class="ml-3">
                                            <p class="text-sm text-yellow-700">This subdomain has not been registered in Cloudflare yet.</p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <!-- Tab 5: Logs & History (only for requests) -->
            @if($type === 'request')
                <div x-show="activeTab === 'logs'" x-cloak>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Activity History</h3>

                    @if($request->logs && $request->logs->count() > 0)
                        <div class="space-y-4">
                            @foreach($request->logs as $log)
                                <div class="flex">
                                    <div class="flex flex-col items-center mr-4">
                                        <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                                            <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                            </svg>
                                        </div>
                                        @if(!$loop->last)
                                            <div class="w-0.5 h-full bg-gray-300 my-1"></div>
                                        @endif
                                    </div>
                                    <div class="flex-1 bg-gray-50 border border-gray-200 rounded-lg p-4 mb-4">
                                        <div class="flex justify-between items-start mb-2">
                                            <p class="text-sm font-medium text-gray-900">{{ $log->activity }}</p>
                                            <span class="text-xs text-gray-500 whitespace-nowrap ml-4">
                                                {{ $log->created_at->format('d M Y H:i') }}
                                            </span>
                                        </div>
                                        @if($log->actor)
                                            <p class="text-xs text-gray-500">
                                                By: <span class="font-medium">{{ $log->actor->name }}</span>
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12">
                            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <p class="text-gray-500">No activity logs yet</p>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

<script>
function checkStatus() {
    const button = event.target;
    const originalText = button.innerHTML;

    button.disabled = true;
    button.innerHTML = '<svg class="animate-spin h-5 w-5 mr-2 inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Checking...';

    fetch('{{ route("admin.unified-subdomain.check-status", ["id" => $has_monitor ? $monitor->id : 0, "type" => $type]) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Status berhasil dicek!\n\nStatus: ' + data.status + '\nLast Checked: ' + data.last_checked_at);
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        alert('Error checking status: ' + error);
    })
    .finally(() => {
        button.disabled = false;
        button.innerHTML = originalText;
    });
}
</script>

<style>
[x-cloak] {
    display: none !important;
}
</style>
@endsection
