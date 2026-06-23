{{--
    Partial form 18 field Pembaruan Data Subdomain.
    Variabel yang dibutuhkan:
      $values            => array nilai field saat ini (webMonitor attrs / proposed_data)
      $reasonValue       => string|null catatan pemohon saat ini
      $programmingLanguages, $frameworks, $databases, $serverLocations
--}}
@php
    $val = fn ($field, $default = '') => old($field, $values[$field] ?? $default);
@endphp

<!-- Informasi Aplikasi -->
<div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
    <h3 class="font-semibold text-green-800 mb-4">Informasi Aplikasi</h3>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label for="nama_aplikasi" class="block text-sm font-semibold text-gray-700 mb-2">Nama Aplikasi</label>
            <input type="text" id="nama_aplikasi" name="nama_aplikasi" value="{{ $val('nama_aplikasi') }}"
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                   placeholder="Contoh: SIMPEG, SIPD, E-Office">
            @error('nama_aplikasi') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="tahun_pembuatan" class="block text-sm font-semibold text-gray-700 mb-2">Tahun Pembuatan</label>
            <input type="number" id="tahun_pembuatan" name="tahun_pembuatan" value="{{ $val('tahun_pembuatan') }}"
                   min="2000" max="{{ date('Y') + 1 }}"
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                   placeholder="Contoh: 2024">
            @error('tahun_pembuatan') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="md:col-span-2">
            <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">Deskripsi Website/Aplikasi</label>
            <textarea id="description" name="description" rows="5"
                      class="ckeditor-field w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                      placeholder="Deskripsi singkat tentang website/aplikasi">{{ $val('description') }}</textarea>
        </div>

        <div class="md:col-span-2">
            <label for="latar_belakang" class="block text-sm font-semibold text-gray-700 mb-2">Latar Belakang Pembuatan</label>
            <textarea id="latar_belakang" name="latar_belakang" rows="5"
                      class="ckeditor-field w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                      placeholder="Jelaskan alasan dan latar belakang pembuatan aplikasi ini">{{ $val('latar_belakang') }}</textarea>
        </div>

        <div class="md:col-span-2">
            <label for="manfaat_aplikasi" class="block text-sm font-semibold text-gray-700 mb-2">Manfaat Aplikasi</label>
            <textarea id="manfaat_aplikasi" name="manfaat_aplikasi" rows="5"
                      class="ckeditor-field w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                      placeholder="Jelaskan manfaat dan kegunaan aplikasi ini">{{ $val('manfaat_aplikasi') }}</textarea>
        </div>

        <div>
            <label for="developer" class="block text-sm font-semibold text-gray-700 mb-2">Developer / Pengembang</label>
            <input type="text" id="developer" name="developer" value="{{ $val('developer') }}"
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                   placeholder="Nama developer atau perusahaan">
        </div>

        <div>
            <label for="contact_person" class="block text-sm font-semibold text-gray-700 mb-2">Contact Person</label>
            <input type="text" id="contact_person" name="contact_person" value="{{ $val('contact_person') }}"
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                   placeholder="Nama contact person">
        </div>

        <div>
            <label for="contact_phone" class="block text-sm font-semibold text-gray-700 mb-2">No. Telepon</label>
            <input type="text" id="contact_phone" name="contact_phone" value="{{ $val('contact_phone') }}"
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
            <label for="programming_language_id" class="block text-sm font-semibold text-gray-700 mb-2">Bahasa Pemrograman</label>
            <select id="programming_language_id" name="programming_language_id"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="">-- Pilih Bahasa --</option>
                @foreach($programmingLanguages as $lang)
                    <option value="{{ $lang->id }}" {{ (string) $val('programming_language_id') === (string) $lang->id ? 'selected' : '' }}>{{ $lang->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="programming_language_version" class="block text-sm font-semibold text-gray-700 mb-2">Versi Bahasa</label>
            <input type="text" id="programming_language_version" name="programming_language_version" value="{{ $val('programming_language_version') }}"
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                   placeholder="Contoh: 8.2, 3.11">
        </div>

        <div>
            <label for="framework_id" class="block text-sm font-semibold text-gray-700 mb-2">Framework</label>
            <select id="framework_id" name="framework_id"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="">-- Pilih Framework --</option>
                @foreach($frameworks as $framework)
                    <option value="{{ $framework->id }}" {{ (string) $val('framework_id') === (string) $framework->id ? 'selected' : '' }}>{{ $framework->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="framework_version" class="block text-sm font-semibold text-gray-700 mb-2">Versi Framework</label>
            <input type="text" id="framework_version" name="framework_version" value="{{ $val('framework_version') }}"
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                   placeholder="Contoh: 10.x, 5.4">
        </div>

        <div>
            <label for="database_id" class="block text-sm font-semibold text-gray-700 mb-2">Database</label>
            <select id="database_id" name="database_id"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="">-- Pilih Database --</option>
                @foreach($databases as $database)
                    <option value="{{ $database->id }}" {{ (string) $val('database_id') === (string) $database->id ? 'selected' : '' }}>{{ $database->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="database_version" class="block text-sm font-semibold text-gray-700 mb-2">Versi Database</label>
            <input type="text" id="database_version" name="database_version" value="{{ $val('database_version') }}"
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                   placeholder="Contoh: 8.0, 15.2">
        </div>

        <div class="md:col-span-2">
            <label for="frontend_tech" class="block text-sm font-semibold text-gray-700 mb-2">Teknologi Frontend</label>
            <input type="text" id="frontend_tech" name="frontend_tech" value="{{ $val('frontend_tech') }}"
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
            <label for="server_ownership" class="block text-sm font-semibold text-gray-700 mb-2">Kepemilikan Server</label>
            <select id="server_ownership" name="server_ownership"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="">-- Pilih --</option>
                <option value="Provinsi Kaltara" {{ $val('server_ownership') === 'Provinsi Kaltara' ? 'selected' : '' }}>Provinsi Kaltara</option>
                <option value="Pihak Ketiga" {{ $val('server_ownership') === 'Pihak Ketiga' ? 'selected' : '' }}>Pihak Ketiga</option>
            </select>
        </div>

        <div>
            <label for="server_owner_name" class="block text-sm font-semibold text-gray-700 mb-2">Nama Pemilik/Provider</label>
            <input type="text" id="server_owner_name" name="server_owner_name" value="{{ $val('server_owner_name') }}"
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                   placeholder="Contoh: Diskominfo Kaltara, AWS, DigitalOcean">
        </div>

        <div class="md:col-span-2">
            <label for="server_location_id" class="block text-sm font-semibold text-gray-700 mb-2">Lokasi Server</label>
            <select id="server_location_id" name="server_location_id"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="">-- Pilih Lokasi --</option>
                @foreach($serverLocations as $location)
                    <option value="{{ $location->id }}" {{ (string) $val('server_location_id') === (string) $location->id ? 'selected' : '' }}>{{ $location->name }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>

<!-- Usulan Status Subdomain -->
<div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
    <h3 class="font-semibold text-red-800 mb-2">Usulan Status Subdomain (opsional)</h3>
    <p class="text-xs text-red-700 mb-3">
        Anda dapat mengusulkan agar subdomain ini dinon-aktifkan (dipensiunkan).
        <strong>Data tidak akan dihapus</strong> — hanya ditandai non-aktif dan tetap tersimpan untuk pemeriksaan di masa depan.
        Usulan ini akan diterapkan setelah disetujui admin.
    </p>
    @php $pd = old('proposed_decommission', $proposedDecommissionValue ?? ''); @endphp
    <select id="proposed_decommission" name="proposed_decommission"
            class="w-full md:w-2/3 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500">
        <option value="" {{ $pd === '' || $pd === null ? 'selected' : '' }}>Tidak ada perubahan status</option>
        @if (!empty($currentDecommissioned))
            <option value="0" {{ (string) $pd === '0' ? 'selected' : '' }}>Usulkan aktifkan kembali subdomain ini</option>
        @else
            <option value="1" {{ (string) $pd === '1' ? 'selected' : '' }}>Usulkan non-aktifkan (pensiunkan) subdomain ini</option>
        @endif
    </select>
    @error('proposed_decommission') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
</div>

<!-- Catatan Pemohon -->
<div class="mb-6">
    <label for="reason" class="block text-sm font-semibold text-gray-700 mb-2">Catatan (opsional)</label>
    <textarea id="reason" name="reason" rows="3"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              placeholder="Penjelasan singkat perubahan yang diajukan (opsional)">{{ old('reason', $reasonValue ?? '') }}</textarea>
    @error('reason') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
</div>
