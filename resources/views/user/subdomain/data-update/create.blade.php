@extends('layouts.authenticated')

@section('title', '- Ajukan Pembaruan Data Subdomain')
@section('header-title', 'Ajukan Pembaruan Data Subdomain')

@section('content')
    <div class="bg-white shadow rounded-lg p-6">
        @if (session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <p class="font-semibold mb-1">Periksa kembali isian Anda:</p>
                <ul class="list-disc list-inside text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (!$webMonitor)
            {{-- Langkah 1: pilih subdomain --}}
            <h2 class="text-xl font-semibold mb-4">Pilih Subdomain</h2>
            <p class="text-gray-600 mb-4">Pilih subdomain milik unit kerja Anda yang datanya ingin diperbarui.</p>
            <form method="GET" action="{{ route('user.subdomain.data-update.create') }}" class="max-w-xl space-y-4">
                <div>
                    <label for="web_monitor_id" class="block text-sm font-semibold text-gray-700 mb-2">Subdomain</label>
                    <select id="web_monitor_id" name="web_monitor_id" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                        <option value="">-- Pilih Subdomain --</option>
                        @foreach ($subdomains as $sd)
                            @php
                                $statusLabel = match ($sd->status) {
                                    'active' => 'Aktif',
                                    'inactive' => 'Non-aktif',
                                    'no-domain' => 'Tanpa Domain',
                                    default => $sd->status,
                                };
                                if ($sd->is_decommissioned) { $statusLabel .= ', Dipensiunkan'; }
                            @endphp
                            <option value="{{ $sd->id }}">{{ $sd->subdomain }}@if($sd->nama_aplikasi) — {{ $sd->nama_aplikasi }}@endif [{{ $statusLabel }}]</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit"
                    class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white font-semibold px-6 py-3 rounded-lg shadow transition">
                    Lanjutkan
                </button>
                <a href="{{ route('user.subdomain.data-update.index') }}" class="ml-2 text-gray-600 hover:text-gray-800">Batal</a>
            </form>
        @else
            {{-- Langkah 2: form pembaruan data --}}
            @php
                $values = collect(\App\Models\SubdomainDataUpdateRequest::EDITABLE_FIELDS)
                    ->mapWithKeys(fn ($f) => [$f => $webMonitor->{$f}])->all();
            @endphp
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold">Perbarui Data: <span class="text-green-700">{{ $webMonitor->subdomain }}</span></h2>
                <a href="{{ route('user.subdomain.data-update.create') }}" class="text-sm text-gray-600 hover:text-gray-800">Ganti subdomain</a>
            </div>

            <form method="POST" action="{{ route('user.subdomain.data-update.store') }}">
                @csrf
                <input type="hidden" name="web_monitor_id" value="{{ $webMonitor->id }}">

                @include('user.subdomain.data-update._form', [
                    'values' => $values,
                    'reasonValue' => null,
                    'currentDecommissioned' => $webMonitor->is_decommissioned,
                    'proposedDecommissionValue' => null,
                ])

                <div class="flex items-center gap-3">
                    <button type="submit"
                        class="bg-green-600 hover:bg-green-700 text-white font-semibold px-6 py-3 rounded-lg shadow transition">
                        Ajukan Pembaruan
                    </button>
                    <a href="{{ route('user.subdomain.data-update.index') }}" class="text-gray-600 hover:text-gray-800">Batal</a>
                </div>
            </form>
        @endif
    </div>
@endsection

@if ($webMonitor)
    @include('user.subdomain.data-update._ckeditor')
@endif
