@extends('layouts.authenticated')

@section('title', '- Edit Web Monitor')
@section('header-title', 'Edit Web Monitor')

@section('content')
<div class="container mx-auto p-6">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Edit Website Monitoring</h1>
        <p class="text-gray-600 mt-2">Edit data website {{ $webMonitor->nama_instansi }}</p>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <form action="{{ route('admin.web-monitor.update', $webMonitor) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label for="nama_instansi" class="block text-sm font-semibold text-gray-700 mb-2">
                    Nama Instansi / Sistem
                </label>
                <input type="text"
                       id="nama_instansi"
                       name="nama_instansi"
                       value="{{ old('nama_instansi', $webMonitor->nama_instansi) }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('nama_instansi') border-red-500 @enderror"
                       list="unit-kerja-list"
                       placeholder="Ketik atau pilih nama instansi/sistem">
                <datalist id="unit-kerja-list">
                    @foreach($unitKerjas as $unit)
                        <option value="{{ $unit->nama }}">{{ $unit->tipe }}</option>
                    @endforeach
                </datalist>
                @error('nama_instansi')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
                <p class="text-gray-500 text-xs mt-1">Opsional: Ketik manual atau pilih dari daftar unit kerja</p>
            </div>

            <div class="mb-4">
                <label for="subdomain" class="block text-sm font-semibold text-gray-700 mb-2">
                    Subdomain
                </label>
                <input type="text"
                       id="subdomain"
                       name="subdomain"
                       value="{{ old('subdomain', $webMonitor->subdomain) }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('subdomain') border-red-500 @enderror"
                       placeholder="Contoh: diskominfo.kaltaraprov.go.id">
                @error('subdomain')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
                <p class="text-gray-500 text-xs mt-1">Opsional: Kosongkan jika IP hanya untuk VM/server tanpa domain</p>
            </div>

            <div class="mb-4">
                <label for="ip_address" class="block text-sm font-semibold text-gray-700 mb-2">
                    IP Address <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       id="ip_address"
                       name="ip_address"
                       value="{{ old('ip_address', $webMonitor->ip_address) }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('ip_address') border-red-500 @enderror"
                       required
                       placeholder="Contoh: 103.144.82.251">
                @error('ip_address')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="jenis" class="block text-sm font-semibold text-gray-700 mb-2">
                    Jenis <span class="text-red-500">*</span>
                </label>
                <select id="jenis"
                        name="jenis"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('jenis') border-red-500 @enderror"
                        required>
                    <option value="">-- Pilih Jenis --</option>
                    @foreach($jenisOptions as $option)
                        <option value="{{ $option }}" {{ old('jenis', $webMonitor->jenis) === $option ? 'selected' : '' }}>
                            {{ $option }}
                        </option>
                    @endforeach
                </select>
                @error('jenis')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="keterangan" class="block text-sm font-semibold text-gray-700 mb-2">
                    Keterangan / Deskripsi
                </label>
                <textarea id="keterangan"
                          name="keterangan"
                          rows="3"
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('keterangan') border-red-500 @enderror"
                          placeholder="Jelaskan penggunaan IP ini (contoh: VM Database Server, Server Backup, Mailserver Internal, dll)">{{ old('keterangan', $webMonitor->keterangan) }}</textarea>
                @error('keterangan')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
                <p class="text-gray-500 text-xs mt-1">Disarankan diisi untuk memudahkan identifikasi penggunaan IP</p>
            </div>

            <!-- Informasi Aplikasi -->
            <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                <h3 class="font-semibold text-green-800 mb-4">Informasi Aplikasi</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="nama_aplikasi" class="block text-sm font-semibold text-gray-700 mb-2">
                            Nama Aplikasi
                        </label>
                        <input type="text"
                               id="nama_aplikasi"
                               name="nama_aplikasi"
                               value="{{ old('nama_aplikasi', $webMonitor->nama_aplikasi) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="Contoh: SIMPEG, SIPD, E-Office">
                    </div>

                    <div>
                        <label for="developer" class="block text-sm font-semibold text-gray-700 mb-2">
                            Developer / Pengembang
                        </label>
                        <input type="text"
                               id="developer"
                               name="developer"
                               value="{{ old('developer', $webMonitor->developer) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="Nama developer atau perusahaan">
                    </div>

                    <div>
                        <label for="contact_person" class="block text-sm font-semibold text-gray-700 mb-2">
                            Contact Person
                        </label>
                        <input type="text"
                               id="contact_person"
                               name="contact_person"
                               value="{{ old('contact_person', $webMonitor->contact_person) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="Nama contact person">
                    </div>

                    <div>
                        <label for="contact_phone" class="block text-sm font-semibold text-gray-700 mb-2">
                            No. Telepon
                        </label>
                        <input type="text"
                               id="contact_phone"
                               name="contact_phone"
                               value="{{ old('contact_phone', $webMonitor->contact_phone) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="Contoh: 081234567890">
                    </div>
                </div>
            </div>

            <!-- Teknologi yang Digunakan -->
            <div class="mb-6 p-4 bg-purple-50 border border-purple-200 rounded-lg">
                <h3 class="font-semibold text-purple-800 mb-4">Teknologi yang Digunakan</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="programming_language_id" class="block text-sm font-semibold text-gray-700 mb-2">
                            Bahasa Pemrograman
                        </label>
                        <select id="programming_language_id"
                                name="programming_language_id"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">-- Pilih Bahasa --</option>
                            @foreach($programmingLanguages as $lang)
                                <option value="{{ $lang->id }}" {{ old('programming_language_id', $webMonitor->programming_language_id) == $lang->id ? 'selected' : '' }}>
                                    {{ $lang->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="programming_language_version" class="block text-sm font-semibold text-gray-700 mb-2">
                            Versi Bahasa
                        </label>
                        <input type="text"
                               id="programming_language_version"
                               name="programming_language_version"
                               value="{{ old('programming_language_version', $webMonitor->programming_language_version) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="Contoh: 8.2, 3.11">
                    </div>

                    <div>
                        <label for="framework_id" class="block text-sm font-semibold text-gray-700 mb-2">
                            Framework
                        </label>
                        <select id="framework_id"
                                name="framework_id"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">-- Pilih Framework --</option>
                            @foreach($frameworks as $framework)
                                <option value="{{ $framework->id }}" {{ old('framework_id', $webMonitor->framework_id) == $framework->id ? 'selected' : '' }}>
                                    {{ $framework->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="framework_version" class="block text-sm font-semibold text-gray-700 mb-2">
                            Versi Framework
                        </label>
                        <input type="text"
                               id="framework_version"
                               name="framework_version"
                               value="{{ old('framework_version', $webMonitor->framework_version) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="Contoh: 10.x, 5.4">
                    </div>

                    <div>
                        <label for="database_id" class="block text-sm font-semibold text-gray-700 mb-2">
                            Database
                        </label>
                        <select id="database_id"
                                name="database_id"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">-- Pilih Database --</option>
                            @foreach($databases as $database)
                                <option value="{{ $database->id }}" {{ old('database_id', $webMonitor->database_id) == $database->id ? 'selected' : '' }}>
                                    {{ $database->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="database_version" class="block text-sm font-semibold text-gray-700 mb-2">
                            Versi Database
                        </label>
                        <input type="text"
                               id="database_version"
                               name="database_version"
                               value="{{ old('database_version', $webMonitor->database_version) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="Contoh: 8.0, 15.2">
                    </div>

                    <div class="md:col-span-2">
                        <label for="frontend_tech" class="block text-sm font-semibold text-gray-700 mb-2">
                            Teknologi Frontend
                        </label>
                        <input type="text"
                               id="frontend_tech"
                               name="frontend_tech"
                               value="{{ old('frontend_tech', $webMonitor->frontend_tech) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="Contoh: React, Vue.js, Bootstrap, jQuery">
                    </div>
                </div>
            </div>

            <!-- Informasi Server -->
            <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                <h3 class="font-semibold text-yellow-800 mb-4">Informasi Server</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="server_ownership" class="block text-sm font-semibold text-gray-700 mb-2">
                            Kepemilikan Server
                        </label>
                        <select id="server_ownership"
                                name="server_ownership"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">-- Pilih --</option>
                            <option value="Provinsi Kaltara" {{ old('server_ownership', $webMonitor->server_ownership) == 'Provinsi Kaltara' ? 'selected' : '' }}>Provinsi Kaltara</option>
                            <option value="Pihak Ketiga" {{ old('server_ownership', $webMonitor->server_ownership) == 'Pihak Ketiga' ? 'selected' : '' }}>Pihak Ketiga</option>
                        </select>
                    </div>

                    <div>
                        <label for="server_owner_name" class="block text-sm font-semibold text-gray-700 mb-2">
                            Nama Pemilik/Provider
                        </label>
                        <input type="text"
                               id="server_owner_name"
                               name="server_owner_name"
                               value="{{ old('server_owner_name', $webMonitor->server_owner_name) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="Contoh: Diskominfo Kaltara, AWS, DigitalOcean">
                    </div>

                    <div class="md:col-span-2">
                        <label for="server_location_id" class="block text-sm font-semibold text-gray-700 mb-2">
                            Lokasi Server
                        </label>
                        <select id="server_location_id"
                                name="server_location_id"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">-- Pilih Lokasi --</option>
                            @foreach($serverLocations as $location)
                                <option value="{{ $location->id }}" {{ old('server_location_id', $webMonitor->server_location_id) == $location->id ? 'selected' : '' }}>
                                    {{ $location->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="mb-4 p-4 bg-blue-50 rounded-lg">
                <label class="flex items-start gap-3">
                    <input type="checkbox"
                           name="is_proxied"
                           class="mt-1"
                           {{ old('is_proxied', $webMonitor->is_proxied) ? 'checked' : '' }}>
                    <div>
                        <span class="font-semibold text-gray-800">Aktifkan Cloudflare Proxy</span>
                        <p class="text-sm text-gray-600 mt-1">
                            Jika dicentang, traffic akan melalui Cloudflare untuk keamanan dan performa lebih baik
                        </p>
                    </div>
                </label>
            </div>

            @if($webMonitor->cloudflare_record_id)
                <div class="mb-6 p-4 bg-orange-50 border border-orange-200 rounded-lg">
                    <label class="flex items-start gap-3">
                        <input type="checkbox"
                               name="update_cloudflare"
                               class="mt-1"
                               {{ old('update_cloudflare') ? 'checked' : '' }}>
                        <div>
                            <span class="font-semibold text-orange-800">Update DNS Record di Cloudflare</span>
                            <p class="text-sm text-orange-700 mt-1">
                                Jika dicentang, perubahan akan diterapkan ke Cloudflare DNS record
                            </p>
                            <p class="text-xs text-orange-600 mt-1">
                                Cloudflare Record ID: <code class="bg-white px-2 py-1 rounded">{{ $webMonitor->cloudflare_record_id }}</code>
                            </p>
                        </div>
                    </label>
                </div>
            @endif

            @if($webMonitor->last_checked_at)
                <div class="mb-4 p-4 bg-gray-50 rounded-lg">
                    <h4 class="font-semibold text-gray-800 mb-2">Informasi Status Terakhir</h4>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-gray-600">Status:</span>
                            <span class="ml-2 font-semibold {{ $webMonitor->status === 'active' ? 'text-green-600' : 'text-red-600' }}">
                                {{ $webMonitor->status === 'active' ? 'Aktif' : 'Tidak Aktif' }}
                            </span>
                        </div>
                        <div>
                            <span class="text-gray-600">Terakhir Dicek:</span>
                            <span class="ml-2">{{ $webMonitor->last_checked_at->format('d/m/Y H:i:s') }}</span>
                        </div>
                        @if($webMonitor->check_error)
                            <div class="col-span-2">
                                <span class="text-gray-600">Error:</span>
                                <span class="ml-2 text-red-600">{{ $webMonitor->check_error }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Hidden field to track return URL -->
            <input type="hidden" name="return_to" value="{{ request()->query('from') }}">

            <div class="flex gap-3">
                <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-semibold">
                    Update
                </button>
                @php
                    $backUrl = request()->query('from') === 'check-ip'
                        ? route('admin.web-monitor.check-ip-publik')
                        : route('admin.web-monitor.index');
                @endphp
                <a href="{{ $backUrl }}"
                   class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-2 rounded-lg font-semibold">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
