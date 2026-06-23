@extends('layouts.authenticated')

@section('title', '- Edit Pembaruan Data Subdomain')
@section('header-title', 'Edit Pembaruan Data Subdomain')

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

        @if ($request->status === 'revisi' && $request->admin_notes)
            <div class="bg-orange-50 border border-orange-300 text-orange-800 px-4 py-3 rounded mb-4">
                <p class="font-semibold">Catatan revisi dari admin:</p>
                <p class="text-sm mt-1">{{ $request->admin_notes }}</p>
            </div>
        @endif

        <div class="mb-4">
            <h2 class="text-xl font-semibold">Edit Permohonan
                <span class="font-mono text-sm text-gray-500">({{ $request->ticket_number }})</span>
            </h2>
            <p class="text-gray-600">Subdomain: <span class="text-green-700 font-semibold">{{ $request->webMonitor->subdomain ?? '-' }}</span></p>
        </div>

        @php $values = $request->proposed_data ?? []; @endphp

        <form method="POST" action="{{ route('user.subdomain.data-update.update', $request->id) }}">
            @csrf
            @method('PUT')

            @include('user.subdomain.data-update._form', [
                'values' => $values,
                'reasonValue' => $request->reason,
                'currentDecommissioned' => $request->webMonitor->is_decommissioned ?? false,
                'proposedDecommissionValue' => $request->proposed_decommission === null ? '' : (string) (int) $request->proposed_decommission,
            ])

            <div class="flex items-center gap-3">
                <button type="submit"
                    class="bg-green-600 hover:bg-green-700 text-white font-semibold px-6 py-3 rounded-lg shadow transition">
                    Simpan & Ajukan Ulang
                </button>
                <a href="{{ route('user.subdomain.data-update.show', $request->id) }}" class="text-gray-600 hover:text-gray-800">Batal</a>
            </div>
        </form>
    </div>
@endsection

@include('user.subdomain.data-update._ckeditor')
