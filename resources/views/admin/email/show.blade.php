@extends('layouts.admin')
@section('title', 'Detail Tiket ' . $item->ticket_no)

@section('content')
    <h1 class="text-2xl font-bold mb-4">Tiket {{ $item->ticket_no }}</h1>

    <div class="grid md:grid-cols-2 gap-6">
        {{-- Kiri: data --}}
        <div class="bg-white rounded shadow p-4">
            <h2 class="font-semibold mb-3">Data Permohonan</h2>
            <dl class="space-y-2 text-sm">
                <div>
                    <dt class="font-medium">Nama</dt>
                    <dd>{{ $item->nama }}</dd>
                </div>
                <div>
                    <dt class="font-medium">NIP</dt>
                    <dd>{{ $item->nip ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="font-medium">Instansi</dt>
                    <dd>{{ $item->instansi }}</dd>
                </div>
                <div>
                    <dt class="font-medium">Username</dt>
                    <dd>{{ $item->username }}@kaltaraprov.go.id</dd>
                </div>
                <div>
                    <dt class="font-medium">Email Alternatif</dt>
                    <dd>{{ $item->email_alternatif ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="font-medium">No. HP</dt>
                    <dd>{{ $item->no_hp }}</dd>
                </div>
                <div>
                    <dt class="font-medium">Password (plaintext)</dt>
                    <dd class="font-mono">{{ $item->getPlainPassword() }}</dd>
                </div>
                <div>
                    <dt class="font-medium">Status</dt>
                    <dd class="capitalize">{{ $item->status }}</dd>
                </div>
                <div>
                    <dt class="font-medium">Diajukan</dt>
                    <dd>{{ optional($item->submitted_at)->format('Y-m-d H:i') }}</dd>
                </div>
                <div>
                    <dt class="font-medium">Selesai</dt>
                    <dd>{{ optional($item->completed_at)->format('Y-m-d H:i') ?: '—' }}</dd>
                </div>
            </dl>
        </div>

        {{-- Kanan: ubah status --}}
        <div class="bg-white rounded shadow p-4">
            <h2 class="font-semibold mb-3">Ubah Status</h2>
            <form action="{{ route('admin.email.status', $item->id) }}" method="POST" class="space-y-3">
                @csrf
                <select name="status" class="border rounded p-2 w-full">
                    @foreach (['menunggu', 'proses', 'ditolak', 'selesai'] as $st)
                        <option value="{{ $st }}" @selected($item->status === $st)>{{ ucfirst($st) }}</option>
                    @endforeach
                </select>
                <textarea name="note" class="border rounded p-2 w-full" rows="3" placeholder="Catatan (opsional)"></textarea>
                <button class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">Simpan</button>
            </form>

            @if (session('status'))
                <div class="mt-3 rounded border border-green-300 bg-green-50 p-3 text-sm">{{ session('status') }}</div>
            @endif
        </div>
    </div>

    {{-- Riwayat --}}
    <div class="bg-white rounded shadow p-4 mt-6">
        <h2 class="font-semibold mb-3">Riwayat</h2>
        <ul class="text-sm space-y-2">
            @foreach ($item->logs as $log)
                <li>
                    <span class="font-mono">{{ $log->created_at->format('Y-m-d H:i') }}</span> —
                    <strong>{{ $log->action }}</strong>
                    @if ($log->note)
                        : <em>{{ $log->note }}</em>
                    @endif
                    @if ($log->actor)
                        (oleh {{ $log->actor->name }})
                    @endif
                </li>
            @endforeach
        </ul>
    </div>
@endsection
