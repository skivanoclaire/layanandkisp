@extends('layouts.authenticated')

@section('title', '- Manajemen API')
@section('header-title', 'Manajemen API')

@section('content')
    <div x-data="{ tab: '{{ session('new_api_key') ? 'keys' : 'whitelist' }}' }">
        <div class="mb-6">
            <h1 class="text-2xl font-bold">Manajemen API</h1>
            <p class="text-sm text-gray-600 mt-1">
                Kelola whitelist IP, API key, dan lihat daftar endpoint yang tersedia untuk diregistrasikan pada SPLP
                (Sistem Penghubung Layanan Pemerintah).
            </p>
        </div>

        {{-- Alerts --}}
        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif
        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Banner API key baru (tampil sekali) --}}
        @if (session('new_api_key'))
            <div class="bg-amber-50 border border-amber-300 rounded-lg p-4 mb-6" x-data="{ copied: false }">
                <p class="font-semibold text-amber-800 mb-2">API Key berhasil dibuat — salin sekarang!</p>
                <p class="text-sm text-amber-700 mb-3">
                    Key ini hanya ditampilkan <strong>satu kali</strong>. Simpan di tempat aman; Anda tidak akan bisa
                    melihatnya lagi.
                </p>
                <div class="flex items-center gap-2">
                    <code class="flex-1 bg-white border border-amber-300 rounded px-3 py-2 text-sm break-all"
                        x-ref="apikey">{{ session('new_api_key') }}</code>
                    <button type="button"
                        @click="navigator.clipboard.writeText($refs.apikey.innerText); copied = true; setTimeout(() => copied = false, 2000)"
                        class="bg-amber-600 hover:bg-amber-700 text-white text-sm font-semibold px-4 py-2 rounded whitespace-nowrap">
                        <span x-show="!copied">Salin</span>
                        <span x-show="copied" x-cloak>Tersalin!</span>
                    </button>
                </div>
            </div>
        @endif

        {{-- Tab navigation --}}
        <div class="border-b border-gray-200 mb-6">
            <nav class="-mb-px flex gap-6">
                <button @click="tab = 'whitelist'"
                    :class="tab === 'whitelist' ? 'border-green-600 text-green-700' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="py-3 px-1 border-b-2 font-medium text-sm transition">
                    Whitelist IP
                </button>
                <button @click="tab = 'keys'"
                    :class="tab === 'keys' ? 'border-green-600 text-green-700' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="py-3 px-1 border-b-2 font-medium text-sm transition">
                    API Key
                </button>
                <button @click="tab = 'endpoints'"
                    :class="tab === 'endpoints' ? 'border-green-600 text-green-700' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="py-3 px-1 border-b-2 font-medium text-sm transition">
                    Daftar Endpoint
                </button>
            </nav>
        </div>

        {{-- ============ TAB: WHITELIST ============ --}}
        <div x-show="tab === 'whitelist'" x-cloak>
            <div class="bg-white rounded-lg shadow p-5 mb-6">
                <h2 class="font-semibold mb-3">Tambah IP ke Whitelist</h2>
                <form action="{{ route('admin.api-management.whitelist.store') }}" method="POST"
                    class="flex flex-col sm:flex-row gap-3">
                    @csrf
                    <input type="text" name="ip_address" value="{{ old('ip_address') }}" placeholder="mis. 103.170.104.48"
                        class="flex-1 border border-gray-300 rounded-lg px-3 py-2 focus:ring-green-500 focus:border-green-500" required>
                    <input type="text" name="description" value="{{ old('description') }}" placeholder="Keterangan (opsional)"
                        class="flex-1 border border-gray-300 rounded-lg px-3 py-2 focus:ring-green-500 focus:border-green-500">
                    <button type="submit"
                        class="bg-green-600 hover:bg-green-700 text-white font-semibold px-6 py-2 rounded-lg whitespace-nowrap">
                        Tambah
                    </button>
                </form>
            </div>

            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full table-auto">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Alamat IP</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Keterangan</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-700 uppercase">Status</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-700 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($whitelists as $wl)
                                <tr>
                                    <td class="px-6 py-3 font-mono text-sm">{{ $wl->ip_address }}</td>
                                    <td class="px-6 py-3 text-sm text-gray-600">{{ $wl->description ?: '-' }}</td>
                                    <td class="px-6 py-3 text-center">
                                        @if ($wl->is_active)
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Aktif</span>
                                        @else
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-200 text-gray-600">Nonaktif</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-3 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <form action="{{ route('admin.api-management.whitelist.toggle', $wl) }}" method="POST">
                                                @csrf @method('PATCH')
                                                <button type="submit" class="text-sm text-blue-600 hover:underline">
                                                    {{ $wl->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                                </button>
                                            </form>
                                            <form action="{{ route('admin.api-management.whitelist.destroy', $wl) }}" method="POST"
                                                onsubmit="return confirm('Hapus IP {{ $wl->ip_address }} dari whitelist?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-sm text-red-600 hover:underline">Hapus</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-6 text-center text-gray-500">Belum ada IP di whitelist.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ============ TAB: API KEY ============ --}}
        <div x-show="tab === 'keys'" x-cloak>
            <div class="bg-white rounded-lg shadow p-5 mb-6">
                <h2 class="font-semibold mb-3">Buat API Key Baru</h2>
                <form action="{{ route('admin.api-management.keys.store') }}" method="POST"
                    class="flex flex-col sm:flex-row gap-3">
                    @csrf
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="Nama key, mis. SPLP Produksi"
                        class="flex-1 border border-gray-300 rounded-lg px-3 py-2 focus:ring-green-500 focus:border-green-500" required>
                    <button type="submit"
                        class="bg-green-600 hover:bg-green-700 text-white font-semibold px-6 py-2 rounded-lg whitespace-nowrap">
                        Generate Key
                    </button>
                </form>
            </div>

            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full table-auto">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Nama</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Prefix</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Terakhir Dipakai</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-700 uppercase">Status</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-700 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($apiKeys as $key)
                                <tr>
                                    <td class="px-6 py-3 text-sm font-medium">{{ $key->name }}</td>
                                    <td class="px-6 py-3 font-mono text-sm text-gray-600">{{ $key->key_prefix }}…</td>
                                    <td class="px-6 py-3 text-sm text-gray-600">
                                        {{ $key->last_used_at ? $key->last_used_at->diffForHumans() : 'Belum pernah' }}
                                    </td>
                                    <td class="px-6 py-3 text-center">
                                        @if ($key->is_active)
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Aktif</span>
                                        @else
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-200 text-gray-600">Nonaktif</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-3 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <form action="{{ route('admin.api-management.keys.toggle', $key) }}" method="POST">
                                                @csrf @method('PATCH')
                                                <button type="submit" class="text-sm text-blue-600 hover:underline">
                                                    {{ $key->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                                </button>
                                            </form>
                                            <form action="{{ route('admin.api-management.keys.destroy', $key) }}" method="POST"
                                                onsubmit="return confirm('Hapus API key {{ $key->name }}? Aplikasi yang memakainya akan kehilangan akses.')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-sm text-red-600 hover:underline">Hapus</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-6 text-center text-gray-500">Belum ada API key.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ============ TAB: DAFTAR ENDPOINT ============ --}}
        <div x-show="tab === 'endpoints'" x-cloak>
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6 text-sm text-blue-800">
                <p class="font-semibold mb-1">Cara mengakses</p>
                <p>Setiap request wajib datang dari IP yang ada di whitelist <strong>dan</strong> menyertakan header
                    <code class="bg-white px-1 rounded">X-API-Key: &lt;api-key-anda&gt;</code>. Daftarkan URL di bawah ini
                    pada SPLP.
                </p>
            </div>

            <div class="space-y-4">
                @foreach ($endpoints as $ep)
                    <div class="bg-white rounded-lg shadow p-5">
                        <div class="flex items-center gap-3 mb-2">
                            <span class="inline-flex px-2 py-1 text-xs font-bold rounded bg-green-100 text-green-800">{{ $ep['method'] }}</span>
                            <h3 class="font-semibold">{{ $ep['name'] }}</h3>
                        </div>
                        <p class="text-sm text-gray-600 mb-3">{{ $ep['description'] }}</p>
                        <div class="mb-3">
                            <span class="text-xs font-medium text-gray-500 uppercase">URL</span>
                            <code class="block bg-gray-50 border border-gray-200 rounded px-3 py-2 text-sm break-all mt-1">{{ $ep['url'] }}</code>
                        </div>
                        <details>
                            <summary class="text-sm text-blue-600 cursor-pointer">Contoh response</summary>
                            <pre class="bg-gray-900 text-gray-100 text-xs rounded p-3 mt-2 overflow-x-auto"><code>{{ $ep['sample'] }}</code></pre>
                        </details>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection
