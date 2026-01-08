@extends('layouts.authenticated')

@section('title', '- Master Data Email')
@section('header-title', 'Master Data Email')

@section('content')
<div class="container mx-auto px-4">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Master Data Email Accounts</h1>
        <div class="flex gap-2">
            <form action="{{ route('admin.email-accounts.test-connection') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded font-semibold">
                    Test Koneksi WHM
                </button>
            </form>
            <form action="{{ route('admin.email-accounts.sync') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded font-semibold">
                    Sinkronisasi dari WHM
                </button>
            </form>
            <a href="{{ route('admin.email-accounts.import-nip.show') }}" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded font-semibold inline-block">
                Import NIP
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
            {{ session('error') }}
        </div>
    @endif

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-md p-4 mb-6">
        <form method="GET" action="{{ route('admin.email-accounts.index') }}">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Cari Email</label>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Ketik email..."
                        class="w-full border-gray-300 rounded-md shadow-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Cari NIP</label>
                    <input type="text" name="nip" value="{{ request('nip') }}"
                        placeholder="Ketik NIP..."
                        class="w-full border-gray-300 rounded-md shadow-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Cari Nama</label>
                    <input type="text" name="name" value="{{ request('name') }}"
                        placeholder="Ketik nama pemohon..."
                        class="w-full border-gray-300 rounded-md shadow-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="suspended" class="w-full border-gray-300 rounded-md shadow-sm">
                        <option value="">-- Semua Status --</option>
                        <option value="0" {{ request('suspended') === '0' ? 'selected' : '' }}>Aktif</option>
                        <option value="1" {{ request('suspended') === '1' ? 'selected' : '' }}>Suspended</option>
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded font-semibold w-full">
                        Filter
                    </button>
                    <a href="{{ route('admin.email-accounts.index') }}" class="bg-gray-400 hover:bg-gray-500 text-white px-4 py-2 rounded font-semibold text-center">
                        Reset
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm mb-1">Total Email</p>
                    <p class="text-3xl font-bold">{{ $emailAccounts->total() }}</p>
                </div>
                <div class="text-blue-200">
                    <svg class="w-12 h-12" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"></path>
                        <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-green-500 to-green-600 text-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-sm mb-1">Aktif</p>
                    <p class="text-3xl font-bold">{{ \App\Models\EmailAccount::where('suspended', 0)->count() }}</p>
                </div>
                <div class="text-green-200">
                    <svg class="w-12 h-12" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-red-500 to-red-600 text-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-red-100 text-sm mb-1">Suspended</p>
                    <p class="text-3xl font-bold">{{ \App\Models\EmailAccount::where('suspended', 1)->count() }}</p>
                </div>
                <div class="text-red-200">
                    <svg class="w-12 h-12" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M13.477 14.89A6 6 0 015.11 6.524l8.367 8.368zm1.414-1.414L6.524 5.11a6 6 0 018.367 8.367zM18 10a8 8 0 11-16 0 8 8 0 0116 0z" clip-rule="evenodd"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-purple-500 to-purple-600 text-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-100 text-sm mb-1">Total Domain</p>
                    <p class="text-3xl font-bold">{{ $domains->count() }}</p>
                </div>
                <div class="text-purple-200">
                    <svg class="w-12 h-12" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                        <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                            <a href="{{ route('admin.email-accounts.index', array_merge(request()->query(), ['sort' => 'email', 'direction' => request('sort') == 'email' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}" class="flex items-center hover:text-gray-900">
                                Email
                                @if(request('sort') == 'email')
                                    @if(request('direction') == 'asc')
                                        <svg class="w-4 h-4 ml-1" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg>
                                    @else
                                        <svg class="w-4 h-4 ml-1" fill="currentColor" viewBox="0 0 20 20"><path d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z"/></svg>
                                    @endif
                                @endif
                            </a>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Nama Pemohon</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Instansi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">NIP</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                            <a href="{{ route('admin.email-accounts.index', array_merge(request()->query(), ['sort' => 'disk_used', 'direction' => request('sort') == 'disk_used' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}" class="flex items-center hover:text-gray-900">
                                Disk Usage
                                @if(request('sort') == 'disk_used')
                                    @if(request('direction') == 'asc')
                                        <svg class="w-4 h-4 ml-1" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg>
                                    @else
                                        <svg class="w-4 h-4 ml-1" fill="currentColor" viewBox="0 0 20 20"><path d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z"/></svg>
                                    @endif
                                @endif
                            </a>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Quota</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                            <a href="{{ route('admin.email-accounts.index', array_merge(request()->query(), ['sort' => 'suspended', 'direction' => request('sort') == 'suspended' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}" class="flex items-center hover:text-gray-900">
                                Status
                                @if(request('sort') == 'suspended')
                                    @if(request('direction') == 'asc')
                                        <svg class="w-4 h-4 ml-1" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg>
                                    @else
                                        <svg class="w-4 h-4 ml-1" fill="currentColor" viewBox="0 0 20 20"><path d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z"/></svg>
                                    @endif
                                @endif
                            </a>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                            <a href="{{ route('admin.email-accounts.index', array_merge(request()->query(), ['sort' => 'last_synced_at', 'direction' => request('sort') == 'last_synced_at' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}" class="flex items-center hover:text-gray-900">
                                Terakhir Sync
                                @if(request('sort') == 'last_synced_at')
                                    @if(request('direction') == 'asc')
                                        <svg class="w-4 h-4 ml-1" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg>
                                    @else
                                        <svg class="w-4 h-4 ml-1" fill="currentColor" viewBox="0 0 20 20"><path d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z"/></svg>
                                    @endif
                                @endif
                            </a>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($emailAccounts as $account)
                    <tr class="{{ $account->isSuspended() ? 'bg-red-50' : '' }}">
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">
                            {{ $account->email }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            @if($account->requester_name)
                                {{ $account->requester_name }}
                            @else
                                <span class="text-gray-400 italic">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            @if($account->requester_instansi)
                                {{ $account->requester_instansi }}
                            @else
                                <span class="text-gray-400 italic">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            @if($account->requester_nip)
                                <span class="font-mono">{{ $account->requester_nip }}</span>
                            @else
                                <span class="text-gray-400 italic">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            {{ $account->diskused_readable ?? '-' }}
                            @if($account->disk_usage_percentage > 0)
                                <div class="w-full bg-gray-200 rounded-full h-2 mt-1">
                                    <div class="bg-blue-600 h-2 rounded-full" style="width: {{ min($account->disk_usage_percentage, 100) }}%"></div>
                                </div>
                                <span class="text-xs text-gray-500">{{ $account->disk_usage_percentage }}%</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            {{ $account->diskquota_readable ?? '-' }}
                        </td>
                        <td class="px-6 py-4 text-sm">
                            @if($account->isSuspended())
                                <span class="inline-block px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">
                                    Suspended
                                </span>
                            @else
                                <span class="inline-block px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">
                                    Aktif
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            {{ $account->last_synced_at ? $account->last_synced_at->format('d M Y H:i') : '-' }}
                        </td>
                        <td class="px-6 py-4 text-sm font-medium">
                            <a href="{{ route('admin.email-accounts.show', $account) }}?return_url={{ urlencode(request()->fullUrl()) }}" class="text-blue-600 hover:text-blue-900 mr-3">
                                Detail
                            </a>
                            <form action="{{ route('admin.email-accounts.destroy', $account) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900"
                                    onclick="return confirm('Hapus {{ $account->email }} dari database lokal?')">
                                    Hapus
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                            Belum ada data email. Klik tombol "Sinkronisasi dari WHM" untuk mengambil data.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $emailAccounts->appends(request()->query())->links() }}
    </div>

    @if($emailAccounts->total() > 0)
    <div class="mt-6 flex justify-end">
        <form action="{{ route('admin.email-accounts.destroy-all') }}" method="POST" class="inline">
            @csrf
            @method('DELETE')
            <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded font-semibold"
                onclick="return confirm('Hapus SEMUA data email dari database lokal? Data di server WHM tidak akan terpengaruh.')">
                Hapus Semua Data Lokal
            </button>
        </form>
    </div>
    @endif
</div>
@endsection
