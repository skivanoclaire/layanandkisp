<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>E-Layanan DKISP @yield('title')</title>
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('kaltara.svg') }}">
    <link rel="alternate icon" href="{{ asset('kaltara.png') }}">
    <script>
        // Pulihkan status sidebar desktop sebelum halaman dirender supaya tidak berkedip.
        try {
            if (localStorage.getItem('sidebarCollapsed') === '1') {
                document.documentElement.classList.add('sb-collapsed');
            }
        } catch (e) {}

        function appShell() {
            return {
                mobileOpen: false,
                collapsed: document.documentElement.classList.contains('sb-collapsed'),
                q: '',
                isMobile() {
                    return window.innerWidth < 768;
                },
                toggleSidebar() {
                    if (this.isMobile()) {
                        this.mobileOpen = !this.mobileOpen;
                        return;
                    }
                    this.collapsed = !this.collapsed;
                    document.documentElement.classList.toggle('sb-collapsed', this.collapsed);
                    try { localStorage.setItem('sidebarCollapsed', this.collapsed ? '1' : '0'); } catch (e) {}
                },
                match(text) {
                    const q = this.q.trim().toLowerCase();
                    return q === '' || text.includes(q);
                },
                searching() {
                    return this.q.trim() !== '';
                },
                focusSearch() {
                    if (this.isMobile()) this.mobileOpen = true;
                    if (this.collapsed && !this.isMobile()) this.toggleSidebar();
                    this.$nextTick(() => this.$refs.menuSearch?.focus());
                },
            };
        }
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        html, body {
            max-width: 100%;
            overflow-x: hidden;
        }

        [x-cloak] {
            display: none !important;
        }

        /* Sidebar: tertutup di mobile, terbuka di desktop kecuali diciutkan pengguna */
        .app-sidebar { transform: translateX(-100%); }
        .app-sidebar.is-mobile-open { transform: none; }
        @media (min-width: 768px) {
            html:not(.sb-collapsed) .app-sidebar { transform: none; }
            html:not(.sb-collapsed) .app-main { padding-left: 18rem; }
        }

        .app-sidebar nav::-webkit-scrollbar { width: 6px; }
        .app-sidebar nav::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, .18); border-radius: 9999px; }
        .app-sidebar nav { scrollbar-width: thin; scrollbar-color: rgba(255, 255, 255, .18) transparent; }
    </style>
    @stack('styles')
</head>

@php
    $authUser = auth()->user();
    $menuSections = $menuSections ?? [];
    $menuPendingItems = $menuPendingItems ?? [];
    $pendingTotal = $pendingCounts['total'] ?? 0;
    $showNotifications = !empty($pendingCounts);

    // Teks pencarian per section & seluruh menu (dipisah '|' agar tidak cocok lintas label)
    $allSearch = collect($menuSections)->flatMap(fn ($s) => collect($s['items'])->pluck('search'))->implode('|');

    // Lokasi halaman saat ini untuk breadcrumb di navbar
    $breadcrumb = [];
    foreach ($menuSections as $section) {
        foreach ($section['items'] as $item) {
            if ($item['active']) {
                $breadcrumb = array_values(array_filter([$section['label'], $item['children'] ? $item['label'] : null]));
                break 2;
            }
        }
    }

    $initials = collect(explode(' ', trim($authUser?->name ?? 'U')))
        ->filter()->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');
    $roleName = $authUser?->roles->pluck('name')->first();
@endphp

<body class="bg-slate-100 text-gray-800 min-h-screen overflow-x-hidden max-w-full" x-data="appShell()"
    @keydown.window.slash="if (!['INPUT', 'TEXTAREA', 'SELECT'].includes($event.target.tagName) && !$event.target.isContentEditable) { $event.preventDefault(); focusSearch(); }"
    @keydown.window.escape="mobileOpen = false">

    <!-- Overlay untuk mobile -->
    <div x-show="mobileOpen" x-transition.opacity x-cloak class="fixed inset-0 bg-black/40 z-40 md:hidden"
        @click="mobileOpen = false">
    </div>

    <!-- Sidebar -->
    <aside :class="{ 'is-mobile-open': mobileOpen }"
        class="app-sidebar app-shell-ui fixed inset-y-0 left-0 z-50 w-72 flex flex-col bg-gradient-to-b from-emerald-900 via-green-900 to-green-950 text-emerald-50 shadow-xl transition-transform duration-300 ease-in-out">

        <!-- Brand -->
        <div class="flex items-center gap-3 px-5 pt-5 pb-4 flex-shrink-0">
            <a href="/" class="flex items-center gap-3 min-w-0">
                <img src="{{ asset('logokaltarafix.png') }}" alt="Logo Kaltara" class="w-8 h-10 flex-shrink-0">
                <span class="min-w-0">
                    <span class="block text-lg font-bold leading-tight text-white">E-Layanan</span>
                    <span class="block text-[11px] leading-tight text-emerald-200/80 truncate">DKISP Provinsi Kalimantan Utara</span>
                </span>
            </a>
            <button type="button" @click="mobileOpen = false" class="ml-auto p-1.5 rounded-lg text-emerald-200 hover:bg-white/10 md:hidden"
                aria-label="Tutup menu">
                <x-nav-icon name="x" />
            </button>
        </div>

        <!-- Pencarian menu -->
        <div class="px-4 pb-3 flex-shrink-0">
            <label class="relative block">
                <span class="sr-only">Cari menu</span>
                <x-nav-icon name="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-emerald-200/70 pointer-events-none" />
                <input type="search" x-ref="menuSearch" x-model="q" placeholder="Cari menu…" autocomplete="off"
                    @keydown.escape.stop="q = ''; $event.target.blur()"
                    class="w-full rounded-lg border-0 bg-white/10 py-2 pl-9 pr-9 text-sm text-white placeholder-emerald-200/60 focus:bg-white/15 focus:outline-none focus:ring-2 focus:ring-emerald-300/60">
                <kbd x-show="!searching()" class="hidden md:inline absolute right-2.5 top-1/2 -translate-y-1/2 rounded border border-white/20 px-1.5 text-[10px] text-emerald-200/70">/</kbd>
            </label>
        </div>

        <!-- Navigasi -->
        <nav class="flex-1 overflow-y-auto px-3 pb-4" aria-label="Menu utama"
            @click="if (isMobile() && $event.target.closest('a')) mobileOpen = false">

            @foreach ($menuSections as $section)
                @php
                    $sectionSearch = collect($section['items'])->pluck('search')->implode('|');
                @endphp
                <div class="{{ $loop->first ? '' : 'mt-5' }}" x-show="match(@js($sectionSearch))">
                    @if ($section['label'])
                        <p class="px-3 mb-1.5 text-[11px] font-semibold uppercase tracking-wider text-emerald-300/70">
                            {{ $section['label'] }}
                        </p>
                    @endif

                    <ul class="space-y-0.5">
                        @foreach ($section['items'] as $item)
                            @if (empty($item['children']))
                                <li x-show="match(@js($item['search']))">
                                    <a href="{{ $item['url'] }}" title="{{ $item['label'] }}"
                                        @if ($item['active']) aria-current="page" @endif
                                        class="relative flex items-center gap-3 rounded-lg px-3 py-2 text-sm transition-colors
                                            {{ $item['active'] ? 'bg-white/15 text-white font-semibold' : 'text-emerald-100/90 hover:bg-white/10 hover:text-white' }}">
                                        @if ($item['active'])
                                            <span class="absolute left-0 top-1.5 bottom-1.5 w-1 rounded-r bg-emerald-300"></span>
                                        @endif
                                        <x-nav-icon :name="$item['icon']" class="w-5 h-5 flex-shrink-0 {{ $item['active'] ? 'text-emerald-200' : 'text-emerald-300/80' }}" />
                                        <span class="flex-1 truncate">{{ $item['label'] }}</span>
                                        @if ($item['badge'] > 0)
                                            <span class="inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1.5 text-[10px] font-bold text-white bg-red-500 rounded-full">
                                                {{ $item['badge'] > 99 ? '99+' : $item['badge'] }}
                                            </span>
                                        @endif
                                    </a>
                                </li>
                            @else
                                @php
                                    $groupLabel = mb_strtolower($item['label']);
                                @endphp
                                <li x-data="{ open: @js($item['active']) }" x-show="match(@js($item['search']))">
                                    <button type="button" @click="open = !open" :aria-expanded="(open || searching()).toString()"
                                        class="relative w-full flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-left transition-colors
                                            {{ $item['active'] ? 'text-white font-semibold' : 'text-emerald-100/90 hover:bg-white/10 hover:text-white' }}">
                                        @if ($item['active'])
                                            <span class="absolute left-0 top-1.5 bottom-1.5 w-1 rounded-r bg-emerald-300"></span>
                                        @endif
                                        <x-nav-icon :name="$item['icon']" class="w-5 h-5 flex-shrink-0 {{ $item['active'] ? 'text-emerald-200' : 'text-emerald-300/80' }}" />
                                        <span class="flex-1 truncate" title="{{ $item['label'] }}">{{ $item['label'] }}</span>
                                        @if ($item['badge'] > 0)
                                            <span class="inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1.5 text-[10px] font-bold text-white bg-red-500 rounded-full">
                                                {{ $item['badge'] > 99 ? '99+' : $item['badge'] }}
                                            </span>
                                        @endif
                                        <x-nav-icon name="chevron-down" class="w-4 h-4 flex-shrink-0 text-emerald-300/70 transition-transform duration-200"
                                            x-bind:class="(open || searching()) && 'rotate-180'" />
                                    </button>
                                    <ul x-show="open || searching()" @if (!$item['active']) x-cloak @endif
                                        class="mt-0.5 mb-1 ml-[1.35rem] space-y-0.5 border-l border-white/15 pl-3">
                                        @foreach ($item['children'] as $child)
                                            <li x-show="match(@js($child['search'])) || match(@js($groupLabel))">
                                                <a href="{{ $child['url'] }}" title="{{ $child['label'] }}"
                                                    @if ($child['active']) aria-current="page" @endif
                                                    class="flex items-center gap-2 rounded-md px-2.5 py-1.5 text-[13px] transition-colors
                                                        {{ $child['active'] ? 'bg-white/15 text-white font-medium' : 'text-emerald-100/80 hover:bg-white/10 hover:text-white' }}">
                                                    <span class="flex-1 leading-snug">{{ $child['label'] }}</span>
                                                    @if ($child['badge'] > 0)
                                                        <span class="inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1.5 text-[10px] font-bold text-white bg-red-500 rounded-full">
                                                            {{ $child['badge'] > 99 ? '99+' : $child['badge'] }}
                                                        </span>
                                                    @endif
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </li>
                            @endif
                        @endforeach
                    </ul>
                </div>
            @endforeach

            <p x-show="searching() && !match(@js($allSearch))" x-cloak class="px-3 py-6 text-center text-sm text-emerald-200/70">
                Menu “<span x-text="q.trim()"></span>” tidak ditemukan.
            </p>

            @if ($authUser && !$authUser->is_verified)
                <div class="mt-5 mx-1 rounded-lg bg-amber-400/15 p-3 text-xs text-amber-100 ring-1 ring-amber-300/30">
                    <p class="font-semibold text-amber-200 mb-1">Akun belum diverifikasi</p>
                    Menu Layanan Digital akan muncul setelah akun Anda diverifikasi Administrator DKISP.
                </div>
            @endif
        </nav>

        <!-- Kartu pengguna -->
        @if ($authUser)
            <div class="flex-shrink-0 border-t border-white/10 p-3">
                <a href="{{ $authUser->hasPermission('user.profile') ? route('profile.edit') : '#' }}"
                    class="flex items-center gap-3 rounded-lg p-2 hover:bg-white/10 transition-colors">
                    <span class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-full bg-emerald-400/90 text-sm font-bold text-emerald-950">
                        {{ $initials }}
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="block truncate text-sm font-semibold text-white">{{ $authUser->name }}</span>
                        <span class="block truncate text-[11px] text-emerald-200/80">{{ $roleName ?? $authUser->email }}</span>
                    </span>
                </a>
            </div>
        @endif
    </aside>

    <!-- Main Content -->
    <div class="app-main min-h-screen flex flex-col max-w-full overflow-x-hidden transition-[padding] duration-300">

        <!-- Navbar -->
        <header class="app-shell-ui sticky top-0 z-30 flex items-center gap-3 border-b border-gray-200 bg-white/95 px-3 py-2.5 backdrop-blur md:px-5">
            <button type="button" @click="toggleSidebar()" class="flex-shrink-0 rounded-lg p-2 text-gray-600 hover:bg-gray-100 hover:text-gray-900"
                aria-label="Buka/tutup menu">
                <x-nav-icon name="menu" class="w-6 h-6" />
            </button>

            <div class="min-w-0 flex-1">
                @if ($breadcrumb)
                    <p class="hidden sm:block truncate text-xs text-gray-500">{{ implode(' / ', $breadcrumb) }}</p>
                @endif
                <h1 class="truncate text-base font-semibold text-gray-900 md:text-lg">@yield('header-title', 'E-Layanan')</h1>
            </div>

            <div class="flex flex-shrink-0 items-center gap-1 sm:gap-2">
                <button type="button" @click="focusSearch()" class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-800 md:hidden"
                    aria-label="Cari menu">
                    <x-nav-icon name="search" />
                </button>

                {{-- Notifikasi permohonan menunggu (hanya untuk pengelola layanan) --}}
                @if ($showNotifications)
                    <div class="relative" x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false">
                        <button type="button" @click="open = !open" class="relative rounded-lg p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-800"
                            aria-label="Permohonan menunggu" :aria-expanded="open.toString()">
                            <x-nav-icon name="bell" class="w-6 h-6" />
                            @if ($pendingTotal > 0)
                                <span class="absolute top-1 right-1 inline-flex min-w-[1.1rem] h-[1.1rem] items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold text-white ring-2 ring-white">
                                    {{ $pendingTotal > 99 ? '99+' : $pendingTotal }}
                                </span>
                            @endif
                        </button>
                        <div x-show="open" x-transition.origin.top.right x-cloak
                            class="absolute right-0 mt-2 w-80 max-w-[calc(100vw-1.5rem)] overflow-hidden rounded-xl bg-white shadow-lg ring-1 ring-black/10">
                            <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3">
                                <p class="text-sm font-semibold text-gray-900">Permohonan Menunggu</p>
                                <span class="rounded-full bg-red-50 px-2 py-0.5 text-xs font-semibold text-red-600">{{ $pendingTotal }}</span>
                            </div>
                            @if ($menuPendingItems)
                                <ul class="max-h-80 overflow-y-auto py-1">
                                    @foreach ($menuPendingItems as $pending)
                                        <li>
                                            <a href="{{ $pending['url'] }}" class="flex items-center gap-3 px-4 py-2.5 hover:bg-gray-50">
                                                <span class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-700">
                                                    <x-nav-icon :name="$pending['icon']" class="w-4 h-4" />
                                                </span>
                                                <span class="min-w-0 flex-1">
                                                    <span class="block truncate text-sm font-medium text-gray-800">{{ $pending['label'] }}</span>
                                                    @if ($pending['group'])
                                                        <span class="block truncate text-xs text-gray-500">{{ $pending['group'] }}</span>
                                                    @endif
                                                </span>
                                                <span class="rounded-full bg-red-500 px-2 py-0.5 text-xs font-bold text-white">{{ $pending['badge'] }}</span>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <div class="flex flex-col items-center gap-2 px-4 py-8 text-center text-sm text-gray-500">
                                    <x-nav-icon name="check-circle" class="w-8 h-8 text-emerald-500" />
                                    Semua permohonan sudah ditindaklanjuti.
                                </div>
                            @endif
                            @if ($authUser?->hasPermission('admin.dashboard'))
                                <a href="{{ route('admin.dashboard') }}" class="block border-t border-gray-100 px-4 py-2.5 text-center text-sm font-medium text-emerald-700 hover:bg-gray-50">
                                    Buka Dashboard Admin
                                </a>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Menu pengguna --}}
                @if ($authUser)
                    <div class="relative" x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false">
                        <button type="button" @click="open = !open" class="flex items-center gap-2 rounded-lg p-1 pr-1.5 hover:bg-gray-100"
                            :aria-expanded="open.toString()" aria-label="Menu akun">
                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-600 text-xs font-bold text-white">{{ $initials }}</span>
                            <span class="hidden lg:block max-w-[10rem] truncate text-sm font-medium text-gray-700">{{ $authUser->name }}</span>
                            <x-nav-icon name="chevron-down" class="hidden lg:block w-4 h-4 text-gray-400" />
                        </button>
                        <div x-show="open" x-transition.origin.top.right x-cloak
                            class="absolute right-0 mt-2 w-64 overflow-hidden rounded-xl bg-white shadow-lg ring-1 ring-black/10">
                            <div class="border-b border-gray-100 px-4 py-3">
                                <p class="truncate text-sm font-semibold text-gray-900">{{ $authUser->name }}</p>
                                <p class="truncate text-xs text-gray-500">{{ $authUser->email }}</p>
                                <div class="mt-2 flex flex-wrap gap-1">
                                    @if ($roleName)
                                        <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-medium text-emerald-700">{{ $roleName }}</span>
                                    @endif
                                    @if ($authUser->is_verified)
                                        <span class="rounded-full bg-blue-50 px-2 py-0.5 text-[11px] font-medium text-blue-700">Terverifikasi</span>
                                    @else
                                        <span class="rounded-full bg-amber-50 px-2 py-0.5 text-[11px] font-medium text-amber-700">Belum diverifikasi</span>
                                    @endif
                                </div>
                            </div>
                            <div class="py-1 text-sm">
                                @if ($authUser->hasPermission('user.dashboard'))
                                    <a href="{{ route('user.dashboard') }}" class="flex items-center gap-3 px-4 py-2 text-gray-700 hover:bg-gray-50">
                                        <x-nav-icon name="home" class="w-4 h-4 text-gray-400" /> Dashboard Pengguna
                                    </a>
                                @endif
                                @if ($authUser->hasPermission('admin.dashboard'))
                                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-4 py-2 text-gray-700 hover:bg-gray-50">
                                        <x-nav-icon name="chart" class="w-4 h-4 text-gray-400" /> Dashboard Admin
                                    </a>
                                @endif
                                @if ($authUser->hasPermission('user.profile'))
                                    <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-4 py-2 text-gray-700 hover:bg-gray-50">
                                        <x-nav-icon name="user" class="w-4 h-4 text-gray-400" /> Profil Saya
                                    </a>
                                @endif
                            </div>
                            <form method="POST" action="{{ route('logout') }}" class="border-t border-gray-100 py-1">
                                @csrf
                                <button type="submit" class="flex w-full items-center gap-3 px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-50">
                                    <x-nav-icon name="logout" class="w-4 h-4" /> Keluar
                                </button>
                            </form>
                        </div>
                    </div>
                @endif
            </div>
        </header>

        <!-- Running Text (dikelola admin di /admin/running-text) -->
        @include('partials.running-text')

        <!-- Page Content -->
        <main class="flex-1 max-w-full overflow-x-hidden p-3 md:p-5">
            <div class="max-w-full overflow-x-hidden rounded-xl border border-gray-200 bg-white p-3 shadow-sm md:p-5">
                @yield('content')
            </div>
        </main>

        <!-- Footer -->
        <footer class="flex flex-col items-center justify-between gap-2 border-t border-gray-200 bg-white px-5 py-3 text-xs text-gray-500 sm:flex-row">
            <p>&copy; {{ date('Y') }} DKISP Kalimantan Utara &middot; Bidang Aplikasi Informatika</p>
            <div class="flex items-center gap-3">
                <span>Dikembangkan oleh: Bayu Adi H.</span>
                <a href="https://www.linkedin.com/in/noclaire/" target="_blank" rel="noopener"
                    class="text-gray-400 hover:text-blue-600" aria-label="LinkedIn">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M4.98 3.5C4.98 4.88 3.87 6 2.5 6S0 4.88 0 3.5 1.12 1 2.5 1s2.48 1.12 2.48 2.5zM.5 8.5h4V24h-4V8.5zm7.5 0h3.8v2.1h.05c.53-1 1.82-2.05 3.75-2.05 4 0 4.75 2.63 4.75 6v9.45h-4V15.5c0-2.13 0-4.88-3-4.88s-3.48 2.25-3.48 4.73V24h-4V8.5z" />
                    </svg>
                </a>
                <a href="https://github.com/skivanoclaire" target="_blank" rel="noopener" class="text-gray-400 hover:text-gray-900"
                    aria-label="GitHub">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M12 .5C5.73.5.5 5.73.5 12c0 5.1 3.29 9.41 7.86 10.94.58.11.79-.25.79-.56 0-.27-.01-1.15-.02-2.09-3.2.7-3.88-1.54-3.88-1.54-.53-1.35-1.3-1.7-1.3-1.7-1.06-.72.08-.7.08-.7 1.17.08 1.78 1.2 1.78 1.2 1.04 1.78 2.73 1.27 3.4.97.11-.76.41-1.27.74-1.56-2.55-.29-5.23-1.28-5.23-5.7 0-1.26.45-2.28 1.19-3.08-.12-.29-.52-1.47.11-3.06 0 0 .97-.31 3.18 1.18a11.1 11.1 0 0 1 2.9-.39c.99 0 1.98.13 2.9.39 2.2-1.49 3.17-1.18 3.17-1.18.64 1.59.24 2.77.12 3.06.74.8 1.19 1.82 1.19 3.08 0 4.43-2.69 5.41-5.26 5.69.42.37.8 1.1.8 2.22 0 1.6-.02 2.89-.02 3.28 0 .31.21.67.8.56A10.99 10.99 0 0 0 23.5 12C23.5 5.73 18.27.5 12 .5z" />
                    </svg>
                </a>
            </div>
        </footer>
    </div>

    @stack('scripts')

    @include('partials.accessibility-widget')
</body>

</html>
