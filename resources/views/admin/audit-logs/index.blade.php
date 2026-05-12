@extends('layouts.authenticated')
@section('title', '- Log Audit')
@section('header-title', 'Log Audit')

@section('content')
<div class="container mx-auto px-4 max-w-7xl py-6">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Log Audit</h1>
            <p class="text-sm text-gray-500">Catatan aktivitas autentikasi: login, logout, gagal, lockout, dan ganti password.</p>
        </div>
    </div>

    <form method="GET" class="bg-white rounded-lg shadow-sm border p-4 mb-4 grid grid-cols-1 md:grid-cols-5 gap-3 items-end">
        <div>
            <label class="block text-xs font-semibold text-gray-700 mb-1">Event</label>
            <select name="event" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                <option value="">Semua</option>
                @foreach($eventLabels as $key => $label)
                    <option value="{{ $key }}" {{ request('event') === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-700 mb-1">Dari Tanggal</label>
            <input type="date" name="from" value="{{ request('from') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-700 mb-1">Sampai Tanggal</label>
            <input type="date" name="to" value="{{ request('to') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
        </div>
        <div class="md:col-span-2">
            <label class="block text-xs font-semibold text-gray-700 mb-1">Cari (email / nama / IP)</label>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="user@example.com / John / 192.168..." class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
        </div>
        <div class="md:col-span-5 flex gap-2">
            <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-md text-sm font-semibold">Filter</button>
            <a href="{{ route('admin.audit-logs.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-md text-sm">Reset</a>
        </div>
    </form>

    <div class="bg-white rounded-lg shadow-sm border overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-gray-700">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold">Waktu</th>
                    <th class="px-4 py-3 text-left font-semibold">Event</th>
                    <th class="px-4 py-3 text-left font-semibold">Pengguna</th>
                    <th class="px-4 py-3 text-left font-semibold">Email</th>
                    <th class="px-4 py-3 text-left font-semibold">IP</th>
                    <th class="px-4 py-3 text-left font-semibold">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr class="border-t hover:bg-gray-50">
                    <td class="px-4 py-3 whitespace-nowrap text-gray-700">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                    <td class="px-4 py-3">
                        @php
                            $color = match($log->event) {
                                'login'            => 'bg-green-100 text-green-800',
                                'logout'           => 'bg-gray-100 text-gray-800',
                                'failed'           => 'bg-yellow-100 text-yellow-800',
                                'lockout'          => 'bg-red-100 text-red-800',
                                'password_changed' => 'bg-blue-100 text-blue-800',
                                default            => 'bg-gray-100 text-gray-800',
                            };
                        @endphp
                        <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $color }}">
                            {{ $eventLabels[$log->event] ?? ucfirst($log->event) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-gray-700">{{ $log->user->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-gray-700 font-mono text-xs">{{ $log->email ?? '—' }}</td>
                    <td class="px-4 py-3 text-gray-600 font-mono text-xs">{{ $log->ip_address ?? '—' }}</td>
                    <td class="px-4 py-3">
                        <a href="{{ route('admin.audit-logs.show', $log) }}" class="text-emerald-600 hover:text-emerald-800 hover:underline text-xs font-semibold">Detail</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">Tidak ada data audit.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $logs->links() }}
    </div>
</div>
@endsection
