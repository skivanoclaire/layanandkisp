@extends('layouts.authenticated')
@section('title', '- Detail Log Audit')
@section('header-title', 'Detail Log Audit')

@section('content')
<div class="container mx-auto px-4 max-w-4xl py-6">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-800">Detail Log Audit</h1>
        <a href="{{ route('admin.audit-logs.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-md text-sm">← Kembali</a>
    </div>

    <div class="bg-white rounded-lg shadow-sm border p-6 space-y-4 text-sm">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-gray-500">Waktu</label>
                <p class="text-gray-800">{{ $auditLog->created_at->format('d M Y, H:i:s') }} WITA</p>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500">Event</label>
                @php
                    $color = match($auditLog->event) {
                        'login'            => 'bg-green-100 text-green-800',
                        'logout'           => 'bg-gray-100 text-gray-800',
                        'failed'           => 'bg-yellow-100 text-yellow-800',
                        'lockout'          => 'bg-red-100 text-red-800',
                        'password_changed' => 'bg-blue-100 text-blue-800',
                        default            => 'bg-gray-100 text-gray-800',
                    };
                @endphp
                <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $color }}">
                    {{ $eventLabels[$auditLog->event] ?? ucfirst($auditLog->event) }}
                </span>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500">Pengguna</label>
                <p class="text-gray-800">{{ $auditLog->user->name ?? '—' }} <span class="text-gray-400 text-xs">(ID: {{ $auditLog->user_id ?? '—' }})</span></p>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500">Email</label>
                <p class="text-gray-800 font-mono text-xs">{{ $auditLog->email ?? '—' }}</p>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500">IP Address</label>
                <p class="text-gray-800 font-mono text-xs">{{ $auditLog->ip_address ?? '—' }}</p>
            </div>
        </div>

        <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1">User Agent</label>
            <pre class="bg-gray-50 border rounded p-3 text-xs text-gray-700 whitespace-pre-wrap break-words">{{ $auditLog->user_agent ?? '—' }}</pre>
        </div>

        @if(!empty($auditLog->meta))
        <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1">Meta</label>
            <pre class="bg-gray-50 border rounded p-3 text-xs text-gray-700">{{ json_encode($auditLog->meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
        </div>
        @endif
    </div>
</div>
@endsection
