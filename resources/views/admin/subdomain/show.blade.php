@extends('layouts.authenticated')
@section('title', '- Detail Tiket ' . $item->ticket_no)
@section('header-title', 'Detail Tiket Subdomain')

@section('content')
    <div class="mb-4">
        <a href="{{ route('admin.subdomain.index') }}" class="text-green-600 hover:text-green-800">
            ← Kembali ke Daftar Permohonan
        </a>
    </div>

    <h1 class="text-2xl font-bold mb-4">Tiket {{ $item->ticket_no }}</h1>

    <div class="grid md:grid-cols-3 gap-6">
        {{-- Left: Main Data (2 columns) --}}
        <div class="md:col-span-2 space-y-6">
            {{-- Section 1: Informasi Pemohon --}}
            <div class="bg-white rounded shadow">
                <div class="bg-green-600 text-white px-4 py-2 rounded-t font-semibold">
                    Informasi Pemohon
                </div>
                <div class="p-4">
                    <dl class="grid md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <dt class="font-medium text-gray-700">Nama</dt>
                            <dd class="mt-1">{{ $item->nama }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-700">NIP</dt>
                            <dd class="mt-1 font-mono">{{ $item->nip ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-700">Instansi</dt>
                            <dd class="mt-1">{{ $item->unitKerja->nama ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-700">Instansi</dt>
                            <dd class="mt-1">{{ $item->instansi }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-700">Email Pemohon</dt>
                            <dd class="mt-1">{{ $item->email_pemohon }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-700">No. HP</dt>
                            <dd class="mt-1">{{ $item->no_hp }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            {{-- Section 2: Detail Subdomain --}}
            <div class="bg-white rounded shadow">
                <div class="bg-green-600 text-white px-4 py-2 font-semibold">
                    Detail Subdomain
                </div>
                <div class="p-4">
                    <dl class="grid md:grid-cols-2 gap-4 text-sm">
                        <div class="md:col-span-2">
                            <dt class="font-medium text-gray-700">Subdomain Diminta</dt>
                            <dd class="mt-1 text-lg font-bold text-green-700">
                                {{ $item->subdomain_requested }}<span class="text-gray-500">.kaltaraprov.go.id</span>
                            </dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-700">IP Address</dt>
                            <dd class="mt-1">
                                <form action="{{ route('admin.subdomain.update-ip', $item->id) }}" method="POST" class="flex items-start gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <div class="flex-1">
                                        <input type="text" name="ip_address" value="{{ old('ip_address', $item->ip_address) }}"
                                            class="font-mono border border-gray-300 rounded px-3 py-1 w-full focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                            pattern="^103\.156\.110\.\d{1,3}$"
                                            placeholder="103.156.110.x"
                                            required>
                                        @error('ip_address')
                                            <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                        <p class="text-xs text-gray-500 mt-1">
                                            ⓘ IP range: 103.156.110.0/24 | Duplicate IPs allowed (virtualization)
                                        </p>
                                    </div>
                                    <button type="submit" class="px-4 py-1 bg-green-600 hover:bg-green-700 text-white text-sm rounded font-semibold transition">
                                        Update IP
                                    </button>
                                </form>
                            </dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-700">Jenis Website</dt>
                            <dd class="mt-1">{{ $item->jenis_website }}</dd>
                        </div>
                        <div class="md:col-span-2">
                            <dt class="font-medium text-gray-700">Tujuan Penggunaan</dt>
                            <dd class="mt-1">{{ $item->purpose }}</dd>
                        </div>
                        @if($item->description)
                        <div class="md:col-span-2">
                            <dt class="font-medium text-gray-700">Deskripsi</dt>
                            <dd class="mt-1">{{ $item->description }}</dd>
                        </div>
                        @endif
                    </dl>
                </div>
            </div>

            {{-- Section 3: Informasi Aplikasi --}}
            <div class="bg-white rounded shadow">
                <div class="bg-green-600 text-white px-4 py-2 font-semibold">
                    Informasi Aplikasi
                </div>
                <div class="p-4">
                    <dl class="space-y-3 text-sm">
                        <div>
                            <dt class="font-medium text-gray-700">Nama Aplikasi</dt>
                            <dd class="mt-1 text-lg font-semibold">{{ $item->nama_aplikasi }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-700">Latar Belakang</dt>
                            <dd class="mt-1">{{ $item->latar_belakang }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-700">Manfaat Aplikasi</dt>
                            <dd class="mt-1">{{ $item->manfaat_aplikasi }}</dd>
                        </div>
                        <div class="grid md:grid-cols-3 gap-4">
                            <div>
                                <dt class="font-medium text-gray-700">Tahun Pembuatan</dt>
                                <dd class="mt-1">{{ $item->tahun_pembuatan }}</dd>
                            </div>
                            <div>
                                <dt class="font-medium text-gray-700">Developer</dt>
                                <dd class="mt-1">{{ $item->developer }}</dd>
                            </div>
                            <div>
                                <dt class="font-medium text-gray-700">Contact Person</dt>
                                <dd class="mt-1">{{ $item->contact_person }}</dd>
                            </div>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-700">No. HP Contact Person</dt>
                            <dd class="mt-1">{{ $item->contact_phone }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            {{-- Section 4: Tech Stack --}}
            <div class="bg-white rounded shadow">
                <div class="bg-green-600 text-white px-4 py-2 font-semibold">
                    Teknologi yang Digunakan
                </div>
                <div class="p-4">
                    <dl class="grid md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <dt class="font-medium text-gray-700">Bahasa Pemrograman</dt>
                            <dd class="mt-1">
                                <span class="font-semibold">{{ $item->programmingLanguage->name ?? '-' }}</span>
                                @if($item->programming_language_version)
                                    <span class="text-gray-500">v{{ $item->programming_language_version }}</span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-700">Framework</dt>
                            <dd class="mt-1">
                                <span class="font-semibold">{{ $item->framework->name ?? 'Tidak menggunakan framework' }}</span>
                                @if($item->framework_version)
                                    <span class="text-gray-500">v{{ $item->framework_version }}</span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-700">Database</dt>
                            <dd class="mt-1">
                                <span class="font-semibold">{{ $item->database->name ?? '-' }}</span>
                                @if($item->database_version)
                                    <span class="text-gray-500">v{{ $item->database_version }}</span>
                                @endif
                            </dd>
                        </div>
                        @if($item->frontend_tech)
                        <div>
                            <dt class="font-medium text-gray-700">Frontend Tech</dt>
                            <dd class="mt-1">{{ $item->frontend_tech }}</dd>
                        </div>
                        @endif
                    </dl>
                </div>
            </div>

            {{-- Section 5: Server & Maintenance --}}
            <div class="bg-white rounded shadow">
                <div class="bg-green-600 text-white px-4 py-2 font-semibold">
                    Server & Pemeliharaan
                </div>
                <div class="p-4">
                    <dl class="grid md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <dt class="font-medium text-gray-700">Kepemilikan Server</dt>
                            <dd class="mt-1 font-semibold">{{ $item->server_ownership }}</dd>
                        </div>
                        @if($item->server_owner_name)
                        <div>
                            <dt class="font-medium text-gray-700">Nama Pemilik Server</dt>
                            <dd class="mt-1">{{ $item->server_owner_name }}</dd>
                        </div>
                        @endif
                        <div>
                            <dt class="font-medium text-gray-700">Lokasi Server</dt>
                            <dd class="mt-1">{{ $item->serverLocation->name ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-700">HTTPS/SSL</dt>
                            <dd class="mt-1">
                                @if($item->has_https)
                                    <span class="text-green-600">✓ Menggunakan HTTPS</span>
                                @else
                                    <span class="text-gray-500">✗ Tidak menggunakan HTTPS</span>
                                @endif
                            </dd>
                        </div>
                        @if($item->maintenance_schedule)
                        <div class="md:col-span-2">
                            <dt class="font-medium text-gray-700">Jadwal Pemeliharaan</dt>
                            <dd class="mt-1">{{ $item->maintenance_schedule }}</dd>
                        </div>
                        @endif
                        @if($item->backup_strategy)
                        <div class="md:col-span-2">
                            <dt class="font-medium text-gray-700">Strategi Backup</dt>
                            <dd class="mt-1">{{ $item->backup_strategy }}</dd>
                        </div>
                        @endif
                    </dl>
                </div>
            </div>

            {{-- Section 6: Kategori Sistem Elektronik --}}
            @if($item->hasCompletedEsc())
            <div class="bg-white rounded shadow border-l-4 border-purple-500">
                <div class="bg-purple-600 text-white px-4 py-2 font-semibold flex items-center justify-between">
                    <span class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Kategori Sistem Elektronik
                    </span>
                </div>
                <div class="p-4">
                    {{-- Category Badge --}}
                    <div class="mb-4 bg-gradient-to-r from-purple-50 to-blue-50 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600 mb-1">Kategori:</p>
                                <span class="text-2xl font-bold px-4 py-2 rounded-full inline-block
                                    {{ $item->esc_category === 'Strategis' ? 'bg-red-100 text-red-800' : '' }}
                                    {{ $item->esc_category === 'Tinggi' ? 'bg-orange-100 text-orange-800' : '' }}
                                    {{ $item->esc_category === 'Rendah' ? 'bg-green-100 text-green-800' : '' }}">
                                    {{ $item->esc_category }}
                                </span>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-gray-600 mb-1">Total Skor:</p>
                                <p class="text-3xl font-bold text-purple-700">{{ $item->esc_total_score }}</p>
                                <p class="text-xs text-gray-500">dari 50 poin</p>
                            </div>
                        </div>
                    </div>

                    {{-- Questionnaire Answers --}}
                    <div class="space-y-3">
                        <h4 class="font-semibold text-sm text-gray-700 mb-3">Hasil Kuesioner:</h4>

                        <div class="grid md:grid-cols-2 gap-3 text-sm">
                            <div class="bg-gray-50 rounded p-3">
                                <p class="font-medium text-gray-700 mb-1">1.1. Nilai investasi sistem elektronik</p>
                                <p class="text-gray-900">
                                    <span class="font-bold">[{{ $item->esc_answers['1_1'] ?? '-' }}]</span>
                                    @if(isset($item->esc_answers['1_1']))
                                        @if($item->esc_answers['1_1'] == 'A') > Rp.30 Miliar
                                        @elseif($item->esc_answers['1_1'] == 'B') Rp.3M - Rp.30M
                                        @else < Rp.3 Miliar
                                        @endif
                                    @endif
                                </p>
                            </div>

                            <div class="bg-gray-50 rounded p-3">
                                <p class="font-medium text-gray-700 mb-1">1.2. Anggaran operasional tahunan</p>
                                <p class="text-gray-900">
                                    <span class="font-bold">[{{ $item->esc_answers['1_2'] ?? '-' }}]</span>
                                    @if(isset($item->esc_answers['1_2']))
                                        @if($item->esc_answers['1_2'] == 'A') > Rp.10 Miliar
                                        @elseif($item->esc_answers['1_2'] == 'B') Rp.1M - Rp.10M
                                        @else < Rp.1 Miliar
                                        @endif
                                    @endif
                                </p>
                            </div>

                            <div class="bg-gray-50 rounded p-3">
                                <p class="font-medium text-gray-700 mb-1">1.3. Kepatuhan terhadap Peraturan</p>
                                <p class="text-gray-900">
                                    <span class="font-bold">[{{ $item->esc_answers['1_3'] ?? '-' }}]</span>
                                    @if(isset($item->esc_answers['1_3']))
                                        @if($item->esc_answers['1_3'] == 'A') Nasional & Internasional
                                        @elseif($item->esc_answers['1_3'] == 'B') Nasional
                                        @else Tidak ada
                                        @endif
                                    @endif
                                </p>
                            </div>

                            <div class="bg-gray-50 rounded p-3">
                                <p class="font-medium text-gray-700 mb-1">1.4. Teknik kriptografi</p>
                                <p class="text-gray-900">
                                    <span class="font-bold">[{{ $item->esc_answers['1_4'] ?? '-' }}]</span>
                                    @if(isset($item->esc_answers['1_4']))
                                        @if($item->esc_answers['1_4'] == 'A') Disertifikasi Negara
                                        @elseif($item->esc_answers['1_4'] == 'B') Standar industri
                                        @else Tidak ada
                                        @endif
                                    @endif
                                </p>
                            </div>

                            <div class="bg-gray-50 rounded p-3">
                                <p class="font-medium text-gray-700 mb-1">1.5. Jumlah pengguna</p>
                                <p class="text-gray-900">
                                    <span class="font-bold">[{{ $item->esc_answers['1_5'] ?? '-' }}]</span>
                                    @if(isset($item->esc_answers['1_5']))
                                        @if($item->esc_answers['1_5'] == 'A') > 5.000
                                        @elseif($item->esc_answers['1_5'] == 'B') 1.000 - 5.000
                                        @else < 1.000
                                        @endif
                                    @endif
                                </p>
                            </div>

                            <div class="bg-gray-50 rounded p-3">
                                <p class="font-medium text-gray-700 mb-1">1.6. Data pribadi yang dikelola</p>
                                <p class="text-gray-900">
                                    <span class="font-bold">[{{ $item->esc_answers['1_6'] ?? '-' }}]</span>
                                    @if(isset($item->esc_answers['1_6']))
                                        @if($item->esc_answers['1_6'] == 'A') Dengan hubungan
                                        @elseif($item->esc_answers['1_6'] == 'B') Individual
                                        @else Tidak ada
                                        @endif
                                    @endif
                                </p>
                            </div>

                            <div class="bg-gray-50 rounded p-3">
                                <p class="font-medium text-gray-700 mb-1">1.7. Klasifikasi Data</p>
                                <p class="text-gray-900">
                                    <span class="font-bold">[{{ $item->esc_answers['1_7'] ?? '-' }}]</span>
                                    @if(isset($item->esc_answers['1_7']))
                                        @if($item->esc_answers['1_7'] == 'A') Sangat Rahasia
                                        @elseif($item->esc_answers['1_7'] == 'B') Rahasia/Terbatas
                                        @else Biasa
                                        @endif
                                    @endif
                                </p>
                            </div>

                            <div class="bg-gray-50 rounded p-3">
                                <p class="font-medium text-gray-700 mb-1">1.8. Kekritisan proses</p>
                                <p class="text-gray-900">
                                    <span class="font-bold">[{{ $item->esc_answers['1_8'] ?? '-' }}]</span>
                                    @if(isset($item->esc_answers['1_8']))
                                        @if($item->esc_answers['1_8'] == 'A') Hajat hidup langsung
                                        @elseif($item->esc_answers['1_8'] == 'B') Tidak langsung
                                        @else Bisnis only
                                        @endif
                                    @endif
                                </p>
                            </div>

                            <div class="bg-gray-50 rounded p-3">
                                <p class="font-medium text-gray-700 mb-1">1.9. Dampak kegagalan</p>
                                <p class="text-gray-900">
                                    <span class="font-bold">[{{ $item->esc_answers['1_9'] ?? '-' }}]</span>
                                    @if(isset($item->esc_answers['1_9']))
                                        @if($item->esc_answers['1_9'] == 'A') Nasional
                                        @elseif($item->esc_answers['1_9'] == 'B') 1+ provinsi
                                        @else 1 kabupaten/kota
                                        @endif
                                    @endif
                                </p>
                            </div>

                            <div class="bg-gray-50 rounded p-3">
                                <p class="font-medium text-gray-700 mb-1">1.10. Potensi kerugian insiden</p>
                                <p class="text-gray-900">
                                    <span class="font-bold">[{{ $item->esc_answers['1_10'] ?? '-' }}]</span>
                                    @if(isset($item->esc_answers['1_10']))
                                        @if($item->esc_answers['1_10'] == 'A') Korban jiwa
                                        @elseif($item->esc_answers['1_10'] == 'B') Finansial
                                        @else Gangguan operasional
                                        @endif
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Supporting Document --}}
                    @if($item->esc_document_path)
                    <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded">
                        <p class="text-sm font-semibold text-blue-800 mb-1">Dokumen Pendukung:</p>
                        <a href="{{ asset('storage/' . $item->esc_document_path) }}"
                           target="_blank"
                           class="text-blue-600 hover:text-blue-800 underline text-sm flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            {{ $item->esc_document_name }}
                        </a>
                    </div>
                    @endif

                    <p class="text-xs text-gray-500 mt-3">
                        Diisi pada: {{ $item->esc_filled_at->format('d M Y H:i') }}
                    </p>
                </div>
            </div>
            @else
            <div class="bg-yellow-50 border border-yellow-200 rounded shadow p-4">
                <p class="text-yellow-800 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    Kuesioner Kategori Sistem Elektronik belum diisi oleh pemohon.
                </p>
            </div>
            @endif

            {{-- Section 7: Status Monitoring --}}
            @if($item->webMonitor)
            <div class="bg-white rounded shadow border-l-4 border-blue-500">
                <div class="bg-blue-600 text-white px-4 py-2 font-semibold flex items-center justify-between">
                    <span class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"></path>
                        </svg>
                        Status Monitoring Website
                    </span>
                    <a href="{{ route('admin.unified-subdomain.show', ['id' => $item->id, 'type' => 'request']) }}" class="text-white hover:text-blue-100 text-xs underline">
                        Lihat Detail Lengkap →
                    </a>
                </div>
                <div class="p-4">
                    <div class="grid md:grid-cols-2 gap-4">
                        <!-- Status Badge -->
                        <div class="md:col-span-2 bg-gray-50 rounded-lg p-4 flex items-center justify-between">
                            <div class="flex items-center">
                                @php
                                    $statusColors = [
                                        'active' => 'bg-green-100 text-green-800',
                                        'down' => 'bg-red-100 text-red-800',
                                        'no-domain' => 'bg-orange-100 text-orange-800',
                                        'checking' => 'bg-blue-100 text-blue-800',
                                    ];
                                @endphp
                                <div class="w-12 h-12 rounded-full {{ $item->webMonitor->status === 'active' ? 'bg-green-100' : 'bg-red-100' }} flex items-center justify-center mr-4">
                                    @if($item->webMonitor->status === 'active')
                                        <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                    @else
                                        <svg class="w-6 h-6 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                        </svg>
                                    @endif
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Current Website Status</p>
                                    <p class="text-2xl font-bold {{ $item->webMonitor->status === 'active' ? 'text-green-600' : 'text-red-600' }}">
                                        {{ ucfirst($item->webMonitor->status) }}
                                    </p>
                                </div>
                            </div>
                            <button onclick="checkWebsiteStatus({{ $item->webMonitor->id }})" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded-lg transition">
                                Check Now
                            </button>
                        </div>

                        <!-- Monitoring Details -->
                        <div>
                            <dt class="font-medium text-gray-700 text-sm">Last Checked</dt>
                            <dd class="mt-1 text-sm" id="last-checked-{{ $item->webMonitor->id }}">
                                {{ $item->webMonitor->last_checked_at ? $item->webMonitor->last_checked_at->format('d M Y H:i:s') : 'Belum pernah dicek' }}
                            </dd>
                        </div>

                        <div>
                            <dt class="font-medium text-gray-700 text-sm">Response Time</dt>
                            <dd class="mt-1 text-sm">
                                @if($item->webMonitor->response_time)
                                    <span class="font-mono {{ $item->webMonitor->response_time < 1000 ? 'text-green-600' : 'text-orange-600' }}">
                                        {{ number_format($item->webMonitor->response_time, 2) }} ms
                                    </span>
                                @else
                                    <span class="text-gray-500">-</span>
                                @endif
                            </dd>
                        </div>

                        <div>
                            <dt class="font-medium text-gray-700 text-sm">HTTP Code</dt>
                            <dd class="mt-1 text-sm">
                                @if($item->webMonitor->http_code)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium font-mono {{ $item->webMonitor->http_code >= 200 && $item->webMonitor->http_code < 300 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $item->webMonitor->http_code }}
                                    </span>
                                @else
                                    <span class="text-gray-500">-</span>
                                @endif
                            </dd>
                        </div>

                        <div>
                            <dt class="font-medium text-gray-700 text-sm">Monitoring Status</dt>
                            <dd class="mt-1 text-sm">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $item->webMonitor->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                    {{ $item->webMonitor->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </dd>
                        </div>

                        @if($item->webMonitor->check_error)
                        <div class="md:col-span-2 bg-red-50 border border-red-200 rounded-lg p-3">
                            <p class="text-xs font-semibold text-red-800 mb-1">⚠️ Error Details:</p>
                            <p class="text-xs text-red-700">{{ $item->webMonitor->check_error }}</p>
                        </div>
                        @endif

                        @if($item->webMonitor->ssl_valid_until)
                        <div class="md:col-span-2 bg-blue-50 border border-blue-200 rounded-lg p-3">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs font-semibold text-blue-800 mb-1">🔒 SSL Certificate</p>
                                    <p class="text-xs text-blue-700">Valid until: {{ $item->webMonitor->ssl_valid_until->format('d M Y') }}</p>
                                </div>
                                @php
                                    $daysRemaining = now()->diffInDays($item->webMonitor->ssl_valid_until, false);
                                @endphp
                                @if($daysRemaining < 30 && $daysRemaining > 0)
                                    <span class="text-xs text-orange-600 font-semibold">⚠️ Expires in {{ $daysRemaining }} days</span>
                                @elseif($daysRemaining <= 0)
                                    <span class="text-xs text-red-600 font-semibold">❌ Expired</span>
                                @else
                                    <span class="text-xs text-green-600 font-semibold">✓ Valid</span>
                                @endif
                            </div>
                        </div>
                        @endif
                    </div>

                    <div class="mt-4 pt-4 border-t flex gap-2">
                        <a href="{{ route('admin.web-monitor.edit', $item->webMonitor->id) }}" class="flex-1 text-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-lg text-sm font-medium transition">
                            Edit Monitoring
                        </a>
                        <a href="{{ route('admin.unified-subdomain.show', ['id' => $item->id, 'type' => 'request']) }}" class="flex-1 text-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition">
                            View Full Details
                        </a>
                    </div>
                </div>
            </div>
            @else
            <div class="bg-white rounded shadow border-l-4 border-yellow-500">
                <div class="bg-yellow-50 px-4 py-3">
                    <div class="flex items-start">
                        <svg class="w-6 h-6 text-yellow-600 mt-0.5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                        <div>
                            <h3 class="text-sm font-semibold text-yellow-800 mb-1">Monitoring Belum Aktif</h3>
                            <p class="text-sm text-yellow-700">Website ini belum terdaftar dalam sistem monitoring. Monitoring akan otomatis dibuat saat permohonan disetujui.</p>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- Section 7: Cloudflare --}}
            <div class="bg-white rounded shadow">
                <div class="bg-green-600 text-white px-4 py-2 font-semibold">
                    Konfigurasi Cloudflare
                </div>
                <div class="p-4">
                    <dl class="grid md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <dt class="font-medium text-gray-700">SSL Certificate</dt>
                            <dd class="mt-1">
                                @if($item->needs_ssl)
                                    <span class="text-green-600">✓ Aktif</span>
                                @else
                                    <span class="text-gray-500">✗ Tidak aktif</span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-700">Cloudflare Proxy</dt>
                            <dd class="mt-1">
                                @if($item->needs_proxy)
                                    <span class="text-green-600">✓ Aktif</span>
                                @else
                                    <span class="text-gray-500">✗ Tidak aktif</span>
                                @endif
                            </dd>
                        </div>
                        @if($item->cloudflare_record_id)
                        <div>
                            <dt class="font-medium text-gray-700">Cloudflare Record ID</dt>
                            <dd class="mt-1 font-mono text-xs">{{ $item->cloudflare_record_id }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-700">Proxy Status</dt>
                            <dd class="mt-1">
                                @if($item->is_proxied)
                                    <span class="px-2 py-1 rounded bg-orange-100 text-orange-800 text-xs">Proxied</span>
                                @else
                                    <span class="px-2 py-1 rounded bg-gray-100 text-gray-800 text-xs">DNS Only</span>
                                @endif
                            </dd>
                        </div>
                        @endif
                        @if($item->webMonitor)
                        <div class="md:col-span-2">
                            <dt class="font-medium text-gray-700">Web Monitor</dt>
                            <dd class="mt-1">
                                <a href="{{ route('admin.web-monitor.edit', $item->webMonitor->id) }}"
                                    class="text-green-600 hover:text-green-800 font-semibold">
                                    → Lihat di Web Monitor (ID: {{ $item->webMonitor->id }})
                                </a>
                            </dd>
                        </div>
                        @endif
                    </dl>
                </div>
            </div>

            {{-- Activity Log --}}
            <div class="bg-white rounded shadow">
                <div class="bg-gray-700 text-white px-4 py-2 font-semibold">
                    Riwayat Aktivitas
                </div>
                <div class="p-4">
                    @if($item->logs->count() > 0)
                        <ul class="text-sm space-y-2">
                            @foreach ($item->logs->sortByDesc('created_at') as $log)
                                <li class="border-l-4 border-gray-300 pl-3 py-1">
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono text-xs text-gray-500">
                                            {{ $log->created_at->format('Y-m-d H:i:s') }}
                                        </span>
                                        <span class="font-semibold text-gray-700">{{ $log->action }}</span>
                                    </div>
                                    @if ($log->note)
                                        <p class="text-gray-600 mt-1 italic">{{ $log->note }}</p>
                                    @endif
                                    @if ($log->actor)
                                        <p class="text-xs text-gray-500 mt-1">oleh {{ $log->actor->name }}</p>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-gray-500 text-sm">Belum ada aktivitas</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Right: Status Management (1 column) --}}
        <div class="md:col-span-1">
            <div class="bg-white rounded shadow sticky top-4">
                <div class="bg-blue-600 text-white px-4 py-2 rounded-t font-semibold">
                    Manajemen Status
                </div>
                <div class="p-4 space-y-4">
                    {{-- Current Status --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status Saat Ini</label>
                        <div>
                            <span class="px-3 py-2 rounded text-sm font-semibold inline-block
                                @switch($item->status)
                                    @case('menunggu') bg-yellow-100 text-yellow-800 @break
                                    @case('proses')   bg-blue-100 text-blue-800 @break
                                    @case('ditolak')  bg-red-100 text-red-800 @break
                                    @case('selesai')  bg-green-100 text-green-800 @break
                                @endswitch
                            ">{{ ucfirst($item->status) }}</span>
                        </div>
                    </div>

                    {{-- Timestamps --}}
                    <div class="text-sm space-y-2 border-t pt-3">
                        <div>
                            <span class="text-gray-600">Diajukan:</span>
                            <span class="font-medium">{{ optional($item->submitted_at)->format('Y-m-d H:i') ?? '-' }}</span>
                        </div>
                        @if($item->processing_at)
                        <div>
                            <span class="text-gray-600">Diproses:</span>
                            <span class="font-medium">{{ $item->processing_at->format('Y-m-d H:i') }}</span>
                        </div>
                        @endif
                        @if($item->rejected_at)
                        <div>
                            <span class="text-gray-600">Ditolak:</span>
                            <span class="font-medium text-red-600">{{ $item->rejected_at->format('Y-m-d H:i') }}</span>
                        </div>
                        @endif
                        @if($item->completed_at)
                        <div>
                            <span class="text-gray-600">Selesai:</span>
                            <span class="font-medium text-green-600">{{ $item->completed_at->format('Y-m-d H:i') }}</span>
                        </div>
                        @endif
                    </div>

                    {{-- Change Status Form --}}
                    <form action="{{ route('admin.subdomain.status', $item->id) }}" method="POST" class="space-y-3 border-t pt-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Ubah Status</label>
                            <select name="status" id="status" class="border rounded p-2 w-full" required>
                                @foreach (['menunggu', 'proses', 'ditolak', 'selesai'] as $st)
                                    <option value="{{ $st }}" @selected($item->status === $st)>{{ ucfirst($st) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div id="rejection_reason_wrapper" style="display: {{ $item->status === 'ditolak' ? 'block' : 'none' }}">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Alasan Penolakan <span class="text-red-500">*</span>
                            </label>
                            <textarea name="rejection_reason" id="rejection_reason" rows="3"
                                class="border rounded p-2 w-full text-sm"
                                placeholder="Jelaskan alasan penolakan...">{{ old('rejection_reason', $item->rejection_reason) }}</textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Catatan (opsional)</label>
                            <textarea name="note" class="border rounded p-2 w-full text-sm" rows="2"
                                placeholder="Catatan tambahan..."></textarea>
                        </div>

                        <button type="submit"
                            class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded font-semibold">
                            Simpan Perubahan Status
                        </button>
                    </form>

                    @if (session('status'))
                        <div class="mt-3 rounded border border-green-300 bg-green-50 p-3 text-sm">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if ($item->status === 'selesai' && $item->cloudflare_record_id)
                        <div class="mt-3 rounded border border-blue-300 bg-blue-50 p-3 text-sm">
                            <p class="font-semibold text-blue-800 mb-1">✓ DNS Aktif</p>
                            <p class="text-blue-700">Subdomain sudah terdaftar di Cloudflare dan aktif.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

@push('scripts')
<script>
// Show/hide rejection reason based on status
document.getElementById('status').addEventListener('change', function() {
    const wrapper = document.getElementById('rejection_reason_wrapper');
    const textarea = document.getElementById('rejection_reason');
    if (this.value === 'ditolak') {
        wrapper.style.display = 'block';
        textarea.required = true;
    } else {
        wrapper.style.display = 'none';
        textarea.required = false;
    }
});

// Check website status
function checkWebsiteStatus(monitorId) {
    const button = event.target;
    const originalText = button.innerHTML;

    button.disabled = true;
    button.innerHTML = '<svg class="animate-spin h-4 w-4 inline-block mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Checking...';

    fetch('/admin/web-monitor/' + monitorId + '/check-status', {
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
            alert('Error: ' + (data.message || 'Failed to check status'));
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
@endpush
@endsection
