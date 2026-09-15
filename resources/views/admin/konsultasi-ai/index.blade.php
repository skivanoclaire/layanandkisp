@extends('layouts.authenticated')

@section('title', '- Knowledge Base Konsultasi SPBE AI')
@section('header-title', 'Knowledge Base Konsultasi SPBE AI')

@section('content')
<div class="container mx-auto px-4 max-w-7xl" x-data="{ tab: '{{ $errors->any() ? (old('pertanyaan') ? 'faq' : (old('system_prompt') ? 'pengaturan' : (old('nama') || $errors->has('kategori') ? 'kategori' : 'dokumen'))) : 'dokumen' }}' }">

    <div class="mb-5">
        <h1 class="text-2xl font-bold text-green-700">Knowledge Base Konsultasi SPBE Berbasis AI</h1>
        <p class="text-gray-600 text-sm mt-1">
            Dasar jawaban layanan <strong>Tanya Langsung</strong>. Unggah dokumen, susun pertanyaan contoh,
            dan atur perilaku asisten.
        </p>
    </div>

    {{-- Status layanan --}}
    <div class="mb-5 grid gap-4 md:grid-cols-4">
        <div class="bg-white rounded-lg shadow p-4 border-t-4 {{ $aiAktif ? 'border-green-500' : 'border-amber-500' }}">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Status Asisten</p>
            <p class="text-lg font-bold {{ $aiAktif ? 'text-green-700' : 'text-amber-600' }} mt-1">
                {{ $aiAktif ? 'AI Aktif' : 'Mode Prototipe' }}
            </p>
            <p class="text-xs text-gray-500 mt-1">
                {{ $aiAktif ? 'Pertanyaan dijawab model ' . $settings['model'] : 'Jawaban dari knowledge base lokal' }}
            </p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-t-4 border-blue-500">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Dokumen Aktif</p>
            <p class="text-lg font-bold text-blue-700 mt-1">{{ $dokumen->where('is_active', true)->count() }} / {{ $dokumen->count() }}</p>
            <p class="text-xs text-gray-500 mt-1">{{ number_format($ukuranKonteks) }} karakter konteks</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-t-4 border-purple-500">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Pertanyaan Contoh</p>
            <p class="text-lg font-bold text-purple-700 mt-1">{{ $faqs->where('is_active', true)->count() }} / {{ $faqs->count() }}</p>
            <p class="text-xs text-gray-500 mt-1">Aktif dari total tersimpan</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-t-4 border-indigo-500">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Percakapan</p>
            <p class="text-lg font-bold text-indigo-700 mt-1">{{ number_format($statistik['total_chat']) }}</p>
            <p class="text-xs text-gray-500 mt-1">
                {{ number_format($statistik['chat_terjawab']) }} terjawab &middot; {{ number_format($statistik['chat_gagal']) }} belum
            </p>
        </div>
    </div>

    @if (session('status'))
        <div class="mb-4 p-3 text-sm bg-green-50 border border-green-300 text-green-800 rounded-lg">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="mb-4 p-3 text-sm bg-red-50 border border-red-300 text-red-700 rounded-lg">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @unless ($hasApiKey)
        <div class="mb-5 flex items-start gap-2 rounded-lg border border-amber-300 bg-amber-50 p-4 text-sm text-amber-800">
            <svg class="w-5 h-5 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            <div>
                <p class="font-semibold">API key Anthropic belum tersedia — layanan berjalan pada mode prototipe.</p>
                <p class="mt-1">
                    Pertanyaan pengguna dijawab dari pertanyaan contoh dan dokumen di halaman ini. Setelah anggaran
                    API key tersedia, isi <code class="bg-amber-100 px-1 rounded">ANTHROPIC_API_KEY</code> pada berkas
                    <code class="bg-amber-100 px-1 rounded">.env</code>, jalankan
                    <code class="bg-amber-100 px-1 rounded">php artisan config:clear</code>, lalu aktifkan asisten pada tab Pengaturan.
                    Seluruh dokumen yang diunggah sekarang akan otomatis menjadi konteks jawaban AI.
                </p>
            </div>
        </div>
    @endunless

    {{-- Tab --}}
    <div class="border-b border-gray-200 mb-5">
        <nav class="flex flex-wrap gap-1 -mb-px">
            @foreach ([
                'dokumen' => 'Dokumen Dasar',
                'faq' => 'Pertanyaan Contoh',
                'kategori' => 'Kategori',
                'pengaturan' => 'Pengaturan Asisten',
                'evaluasi' => 'Evaluasi',
            ] as $key => $label)
                <button type="button" @click="tab = '{{ $key }}'"
                        :class="tab === '{{ $key }}' ? 'border-green-600 text-green-700 font-semibold' : 'border-transparent text-gray-500 hover:text-gray-700'"
                        class="px-4 py-2.5 border-b-2 text-sm transition">
                    {{ $label }}
                </button>
            @endforeach
        </nav>
    </div>

    {{-- ============================================================ DOKUMEN --}}
    <div x-show="tab === 'dokumen'" x-cloak class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow p-5">
                <h2 class="font-semibold text-gray-800 mb-1">Unggah Dokumen</h2>
                <p class="text-xs text-gray-500 mb-4">
                    Dokumen menjadi dasar jawaban asisten. Teks TXT, MD, CSV, dan DOCX diekstrak otomatis;
                    untuk PDF, tempelkan isinya pada kolom teks.
                </p>

                <form method="POST" action="{{ route('admin.konsultasi-ai.dokumen.store') }}" enctype="multipart/form-data" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-sm text-gray-700 mb-1">Judul <span class="text-red-500">*</span></label>
                        <input type="text" name="judul" value="{{ old('judul') }}" required maxlength="200"
                               placeholder="Perpres 95/2018 tentang SPBE"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-700 mb-1">Kategori</label>
                        <select name="kategori" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white">
                            <option value="">— Tanpa kategori —</option>
                            @foreach ($kategoriDokumen as $opsi)
                                <option value="{{ $opsi->nama }}" @selected(old('kategori') === $opsi->nama)>{{ $opsi->nama }}</option>
                            @endforeach
                        </select>
                        @if ($kategoriDokumen->isEmpty())
                            <p class="text-xs text-amber-600 mt-1">Belum ada kategori dokumen. Tambahkan pada tab <strong>Kategori</strong>.</p>
                        @endif
                    </div>
                    <div>
                        <label class="block text-sm text-gray-700 mb-1">Deskripsi Singkat</label>
                        <textarea name="deskripsi" rows="2" maxlength="1000"
                                  class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">{{ old('deskripsi') }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-700 mb-1">Berkas</label>
                        <input type="file" name="file" accept=".pdf,.doc,.docx,.txt,.md,.csv"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        <p class="text-xs text-gray-500 mt-1">PDF, DOC, DOCX, TXT, MD, CSV — maksimal 10 MB.</p>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-700 mb-1">Isi Teks (opsional)</label>
                        <textarea name="konten" rows="6"
                                  placeholder="Tempelkan isi dokumen di sini bila berkasnya PDF atau ingin diringkas terlebih dahulu."
                                  class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono">{{ old('konten') }}</textarea>
                        <p class="text-xs text-gray-500 mt-1">Kolom ini yang dibaca asisten. Bila dikosongkan, sistem mencoba mengekstrak dari berkas.</p>
                    </div>
                    <button type="submit" class="w-full px-4 py-2.5 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-lg">
                        Simpan Dokumen
                    </button>
                </form>
            </div>
        </div>

        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-200">
                    <h2 class="font-semibold text-gray-800">Daftar Dokumen ({{ $dokumen->count() }})</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-gray-600">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold">Dokumen</th>
                                <th class="px-4 py-3 text-left font-semibold">Isi Teks</th>
                                <th class="px-4 py-3 text-left font-semibold">Status</th>
                                <th class="px-4 py-3 text-right font-semibold">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($dokumen as $dok)
                                <tr>
                                    <td class="px-4 py-3">
                                        <p class="font-medium text-gray-800">{{ $dok->judul }}</p>
                                        <p class="text-xs text-gray-500 mt-0.5">
                                            @if ($dok->kategori)
                                                <span class="inline-block bg-gray-100 rounded px-1.5 py-0.5 mr-1">{{ $dok->kategori }}</span>
                                            @endif
                                            {{ $dok->uploader?->name ?? 'Sistem' }} &middot; {{ $dok->created_at->format('d/m/Y') }}
                                        </p>
                                        @if ($dok->file_path)
                                            <a href="{{ asset('storage/' . $dok->file_path) }}" target="_blank"
                                               class="text-xs text-blue-600 hover:underline inline-flex items-center mt-1">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                                                </svg>
                                                {{ Str::limit($dok->file_name, 32) }} ({{ $dok->ukuranTerbaca() }})
                                            </a>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        @if ($dok->hasKonten())
                                            <span class="inline-flex items-center text-xs text-green-700 bg-green-50 border border-green-200 rounded-full px-2 py-0.5">
                                                {{ number_format(mb_strlen($dok->konten)) }} karakter
                                            </span>
                                        @else
                                            <span class="inline-flex items-center text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded-full px-2 py-0.5">
                                                Belum ada teks
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <form method="POST" action="{{ route('admin.konsultasi-ai.dokumen.toggle', $dok) }}">
                                            @csrf
                                            <button type="submit"
                                                    class="text-xs px-2.5 py-1 rounded-full border {{ $dok->is_active ? 'bg-green-50 border-green-300 text-green-700' : 'bg-gray-100 border-gray-300 text-gray-500' }}">
                                                {{ $dok->is_active ? 'Aktif' : 'Nonaktif' }}
                                            </button>
                                        </form>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex justify-end gap-2">
                                            <a href="{{ route('admin.konsultasi-ai.dokumen.edit', $dok) }}"
                                               class="text-xs px-3 py-1.5 border border-gray-300 rounded hover:bg-gray-50">Edit</a>
                                            <form method="POST" action="{{ route('admin.konsultasi-ai.dokumen.destroy', $dok) }}"
                                                  onsubmit="return confirm('Hapus dokumen ini dari knowledge base?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-xs px-3 py-1.5 border border-red-300 text-red-600 rounded hover:bg-red-50">Hapus</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-10 text-center text-gray-500">
                                        Belum ada dokumen. Unggah dokumen pertama melalui formulir di samping.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- ================================================== PERTANYAAN CONTOH --}}
    <div x-show="tab === 'faq'" x-cloak class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow p-5">
                <h2 class="font-semibold text-gray-800 mb-1">Tambah Pertanyaan Contoh</h2>
                <p class="text-xs text-gray-500 mb-4">
                    Dipakai sebagai jawaban otomatis selama mode prototipe, dan sebagai konteks tambahan setelah AI aktif.
                </p>

                <form method="POST" action="{{ route('admin.konsultasi-ai.faq.store') }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-sm text-gray-700 mb-1">Pertanyaan <span class="text-red-500">*</span></label>
                        <input type="text" name="pertanyaan" value="{{ old('pertanyaan') }}" required maxlength="255"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-700 mb-1">Jawaban <span class="text-red-500">*</span></label>
                        <textarea name="jawaban" rows="7" required
                                  class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">{{ old('jawaban') }}</textarea>
                        <p class="text-xs text-gray-500 mt-1">Mendukung format ringan: <code>**tebal**</code>, baris <code>- </code> untuk poin, dan <code>1. </code> untuk penomoran.</p>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm text-gray-700 mb-1">Kategori</label>
                            <select name="kategori" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white">
                                <option value="">— Tanpa kategori —</option>
                                @foreach ($kategoriFaq as $opsi)
                                    <option value="{{ $opsi->nama }}" @selected(old('kategori') === $opsi->nama)>{{ $opsi->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-700 mb-1">Urutan</label>
                            <input type="number" name="urutan" value="{{ old('urutan') }}" min="0" max="9999"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-700 mb-1">Kata Kunci Pemicu</label>
                        <input type="text" name="kata_kunci" value="{{ old('kata_kunci') }}" maxlength="255"
                               placeholder="subdomain, domain, website"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        <p class="text-xs text-gray-500 mt-1">Pisahkan dengan koma. Makin spesifik, makin akurat pencocokannya.</p>
                    </div>
                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" name="is_active" value="1" checked class="rounded border-gray-300">
                        Aktifkan
                    </label>
                    <button type="submit" class="w-full px-4 py-2.5 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-lg">
                        Simpan Pertanyaan
                    </button>
                </form>
            </div>
        </div>

        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-200">
                    <h2 class="font-semibold text-gray-800">Daftar Pertanyaan Contoh ({{ $faqs->count() }})</h2>
                </div>
                <div class="divide-y divide-gray-100">
                    @forelse ($faqs as $faq)
                        <div class="px-5 py-4">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="font-medium text-gray-800">
                                        <span class="text-gray-400 text-xs mr-1">#{{ $faq->urutan }}</span>
                                        {{ $faq->pertanyaan }}
                                    </p>
                                    <p class="text-sm text-gray-600 mt-1">{{ Str::limit(strip_tags($faq->jawaban), 220) }}</p>
                                    <div class="flex flex-wrap items-center gap-2 mt-2 text-xs">
                                        @if ($faq->kategori)
                                            <span class="bg-purple-50 text-purple-700 border border-purple-200 rounded-full px-2 py-0.5">{{ $faq->kategori }}</span>
                                        @endif
                                        <span class="{{ $faq->is_active ? 'bg-green-50 text-green-700 border-green-200' : 'bg-gray-100 text-gray-500 border-gray-300' }} border rounded-full px-2 py-0.5">
                                            {{ $faq->is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                        <span class="text-gray-400">{{ $faq->hit_count }}x dijawab</span>
                                        @if ($faq->kata_kunci)
                                            <span class="text-gray-400 truncate">Kunci: {{ $faq->kata_kunci }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex flex-shrink-0 gap-2">
                                    <a href="{{ route('admin.konsultasi-ai.faq.edit', $faq) }}"
                                       class="text-xs px-3 py-1.5 border border-gray-300 rounded hover:bg-gray-50">Edit</a>
                                    <form method="POST" action="{{ route('admin.konsultasi-ai.faq.destroy', $faq) }}"
                                          onsubmit="return confirm('Hapus pertanyaan contoh ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-xs px-3 py-1.5 border border-red-300 text-red-600 rounded hover:bg-red-50">Hapus</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="px-5 py-10 text-center text-gray-500">Belum ada pertanyaan contoh.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- =========================================================== KATEGORI --}}
    <div x-show="tab === 'kategori'" x-cloak class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow p-5">
                <h2 class="font-semibold text-gray-800 mb-1">Tambah Kategori</h2>
                <p class="text-xs text-gray-500 mb-4">
                    Kategori menjadi pilihan pada formulir dokumen dasar dan pertanyaan contoh.
                    Taksonomi keduanya terpisah.
                </p>

                <form method="POST" action="{{ route('admin.konsultasi-ai.kategori.store') }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-sm text-gray-700 mb-1">Dipakai Untuk <span class="text-red-500">*</span></label>
                        <select name="tipe" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white">
                            @foreach ($tipeKategori as $nilai => $label)
                                <option value="{{ $nilai }}" @selected(old('tipe') === $nilai)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-700 mb-1">Nama Kategori <span class="text-red-500">*</span></label>
                        <input type="text" name="nama" value="{{ old('nama') }}" required maxlength="100"
                               placeholder="Regulasi / SOP / Subdomain"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-700 mb-1">Keterangan</label>
                        <input type="text" name="deskripsi" value="{{ old('deskripsi') }}" maxlength="255"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-700 mb-1">Urutan</label>
                        <input type="number" name="urutan" value="{{ old('urutan') }}" min="0" max="9999"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        <p class="text-xs text-gray-500 mt-1">Kosongkan untuk otomatis di urutan terakhir.</p>
                    </div>
                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" name="is_active" value="1" checked class="rounded border-gray-300">
                        Aktifkan
                    </label>
                    <button type="submit" class="w-full px-4 py-2.5 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-lg">
                        Simpan Kategori
                    </button>
                </form>
            </div>
        </div>

        <div class="lg:col-span-2 space-y-6">
            @foreach ($tipeKategori as $nilai => $label)
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-200">
                        <h2 class="font-semibold text-gray-800">Kategori {{ $label }} ({{ ($semuaKategori[$nilai] ?? collect())->count() }})</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50 text-gray-600">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold">Nama</th>
                                    <th class="px-4 py-3 text-left font-semibold">Dipakai</th>
                                    <th class="px-4 py-3 text-left font-semibold">Status</th>
                                    <th class="px-4 py-3 text-right font-semibold">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($semuaKategori[$nilai] ?? [] as $kat)
                                    <tr>
                                        <td class="px-4 py-3">
                                            <p class="font-medium text-gray-800">
                                                <span class="text-gray-400 text-xs mr-1">#{{ $kat->urutan }}</span>
                                                {{ $kat->nama }}
                                            </p>
                                            @if ($kat->deskripsi)
                                                <p class="text-xs text-gray-500 mt-0.5">{{ $kat->deskripsi }}</p>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-gray-600">
                                            {{ $pemakaianKategori[$nilai][$kat->nama] ?? 0 }} data
                                        </td>
                                        <td class="px-4 py-3">
                                            <form method="POST" action="{{ route('admin.konsultasi-ai.kategori.toggle', $kat) }}">
                                                @csrf
                                                <button type="submit"
                                                        class="text-xs px-2.5 py-1 rounded-full border {{ $kat->is_active ? 'bg-green-50 border-green-300 text-green-700' : 'bg-gray-100 border-gray-300 text-gray-500' }}">
                                                    {{ $kat->is_active ? 'Aktif' : 'Nonaktif' }}
                                                </button>
                                            </form>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex justify-end gap-2">
                                                <a href="{{ route('admin.konsultasi-ai.kategori.edit', $kat) }}"
                                                   class="text-xs px-3 py-1.5 border border-gray-300 rounded hover:bg-gray-50">Edit</a>
                                                <form method="POST" action="{{ route('admin.konsultasi-ai.kategori.destroy', $kat) }}"
                                                      onsubmit="return confirm('Hapus kategori ini?')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="text-xs px-3 py-1.5 border border-red-300 text-red-600 rounded hover:bg-red-50">Hapus</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-8 text-center text-gray-500">
                                            Belum ada kategori untuk bagian ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach

            <p class="text-xs text-gray-500">
                Mengganti nama kategori ikut memperbarui dokumen dan pertanyaan yang memakainya.
                Kategori yang masih dipakai tidak bisa dihapus — nonaktifkan saja bila ingin
                menyembunyikannya dari formulir.
            </p>
        </div>
    </div>

    {{-- ========================================================= PENGATURAN --}}
    <div x-show="tab === 'pengaturan'" x-cloak class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow p-5">
                <h2 class="font-semibold text-gray-800 mb-4">Pengaturan Asisten</h2>

                <form method="POST" action="{{ route('admin.konsultasi-ai.settings.update') }}" class="space-y-4">
                    @csrf @method('PUT')

                    <div class="rounded-lg border {{ $hasApiKey ? 'border-gray-200' : 'border-amber-300 bg-amber-50' }} p-4">
                        <label class="flex items-start gap-3">
                            <input type="checkbox" name="ai_enabled" value="1" class="mt-1 rounded border-gray-300"
                                   @checked(old('ai_enabled', $settings['ai_enabled']) === '1' || old('ai_enabled') === '1')
                                   @disabled(! $hasApiKey)>
                            <span>
                                <span class="block text-sm font-semibold text-gray-800">Aktifkan jawaban oleh AI (Claude)</span>
                                <span class="block text-xs text-gray-600 mt-0.5">
                                    @if ($hasApiKey)
                                        API key terdeteksi. Saat aktif, pertanyaan pengguna diteruskan ke Claude
                                        beserta seluruh knowledge base sebagai konteks.
                                    @else
                                        Tidak dapat diaktifkan — <code class="bg-amber-100 px-1 rounded">ANTHROPIC_API_KEY</code> belum diisi pada berkas .env.
                                    @endif
                                </span>
                            </span>
                        </label>
                    </div>

                    <div>
                        <label class="block text-sm text-gray-700 mb-1">Model</label>
                        <select name="model" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                            @foreach ([
                                'claude-sonnet-5' => 'Claude Sonnet 5 — seimbang antara kualitas dan biaya',
                                'claude-opus-5' => 'Claude Opus 5 — kualitas penalaran tertinggi',
                                'claude-haiku-4-5' => 'Claude Haiku 4.5 — paling cepat dan hemat',
                            ] as $value => $label)
                                <option value="{{ $value }}" @selected(old('model', $settings['model']) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Biaya ditagih per token oleh Anthropic. Sonnet memadai untuk sebagian besar konsultasi.</p>
                    </div>

                    <div>
                        <label class="block text-sm text-gray-700 mb-1">Instruksi Sistem (System Prompt)</label>
                        <textarea name="system_prompt" rows="12" required maxlength="20000"
                                  class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono">{{ old('system_prompt', $settings['system_prompt']) }}</textarea>
                        <p class="text-xs text-gray-500 mt-1">Menentukan persona, batasan, dan gaya bahasa asisten.</p>
                    </div>

                    <div>
                        <label class="block text-sm text-gray-700 mb-1">Pesan Bila Jawaban Tidak Ditemukan</label>
                        <textarea name="fallback_message" rows="5" required maxlength="5000"
                                  class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">{{ old('fallback_message', $settings['fallback_message']) }}</textarea>
                    </div>

                    <div class="flex flex-wrap gap-2 pt-1">
                        <button type="submit" class="px-5 py-2.5 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-lg">
                            Simpan Pengaturan
                        </button>
                    </div>
                </form>

                <form method="POST" action="{{ route('admin.konsultasi-ai.settings.test') }}" class="mt-3">
                    @csrf
                    <button type="submit" class="px-5 py-2.5 border border-gray-300 text-sm rounded-lg hover:bg-gray-50 disabled:opacity-50"
                            @disabled(! $aiAktif)>
                        Uji Koneksi ke Anthropic
                    </button>
                    @unless ($aiAktif)
                        <span class="text-xs text-gray-500 ml-2">Tersedia setelah asisten AI diaktifkan.</span>
                    @endunless
                </form>
            </div>
        </div>

        <div class="lg:col-span-1 space-y-4">
            <div class="bg-white rounded-lg shadow p-5">
                <h3 class="font-semibold text-gray-800 mb-3">Langkah Aktivasi Nanti</h3>
                <ol class="text-sm text-gray-600 space-y-2 list-decimal list-inside">
                    <li>Anggarkan dan beli API key pada <span class="font-mono text-xs">console.anthropic.com</span>.</li>
                    <li>Isi <code class="bg-gray-100 px-1 rounded text-xs">ANTHROPIC_API_KEY</code> pada berkas <code class="bg-gray-100 px-1 rounded text-xs">.env</code> server.</li>
                    <li>Jalankan <code class="bg-gray-100 px-1 rounded text-xs">php artisan config:clear</code>.</li>
                    <li>Kembali ke halaman ini, centang <em>Aktifkan jawaban oleh AI</em>, lalu simpan.</li>
                    <li>Klik <em>Uji Koneksi</em> untuk memastikan sambungan berhasil.</li>
                </ol>
                <p class="text-xs text-gray-500 mt-3">
                    Tidak ada perubahan kode yang diperlukan. Seluruh dokumen dan pertanyaan contoh yang sudah
                    disiapkan otomatis menjadi konteks jawaban AI.
                </p>
            </div>

            <div class="bg-white rounded-lg shadow p-5">
                <h3 class="font-semibold text-gray-800 mb-2">Ukuran Konteks</h3>
                <p class="text-2xl font-bold text-green-700">{{ number_format($ukuranKonteks) }}</p>
                <p class="text-xs text-gray-500 mt-1">
                    karakter dikirim sebagai konteks setiap pertanyaan (± {{ number_format(ceil($ukuranKonteks / 3.5)) }} token).
                    Konteks di-cache oleh Anthropic sehingga biaya pertanyaan berikutnya jauh lebih murah.
                </p>
            </div>
        </div>
    </div>

    {{-- =========================================================== EVALUASI --}}
    <div x-show="tab === 'evaluasi'" x-cloak>
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-200">
                <h2 class="font-semibold text-gray-800">Pertanyaan yang Belum Terjawab</h2>
                <p class="text-xs text-gray-500 mt-1">
                    Daftar pertanyaan pengguna yang tidak ditemukan pada knowledge base. Gunakan sebagai bahan
                    menambah pertanyaan contoh atau dokumen baru.
                </p>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse ($pertanyaanTakTerjawab as $chat)
                    <div class="px-5 py-3 flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-sm text-gray-800">{{ $chat->pertanyaan }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $chat->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                        <span class="text-xs text-amber-600 bg-amber-50 border border-amber-200 rounded-full px-2 py-0.5 flex-shrink-0">
                            Belum terjawab
                        </span>
                    </div>
                @empty
                    <p class="px-5 py-10 text-center text-gray-500">
                        Belum ada pertanyaan yang gagal dijawab. Seluruh pertanyaan pengguna berhasil dilayani.
                    </p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<style>[x-cloak] { display: none !important; }</style>
@endsection
