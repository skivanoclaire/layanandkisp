@extends('layouts.authenticated')
@section('title','- Formulir Subdomain')
@section('header-title', 'Formulir Subdomain')

@section('content')
<div class="max-w-7xl mx-auto">
  <div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-green-700">Formulir Subdomain – Pengajuan Anda</h1>
    <a href="{{ route('user.subdomain.create') }}"
       class="inline-flex items-center bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">
       + Buat Permohonan Baru
    </a>
  </div>

  @if (session('status'))
    <div class="mb-4 rounded border border-green-300 bg-green-50 p-3 text-sm">
      {{ session('status') }}
    </div>
  @endif

  @if (session('error'))
    <div class="mb-4 rounded border border-red-300 bg-red-50 p-3 text-sm text-red-700">
      {{ session('error') }}
    </div>
  @endif

  @if ($items->count() === 0)
    <div class="bg-white rounded shadow p-6 text-center">
      <p class="mb-4">Belum ada permohonan subdomain.</p>
      <a href="{{ route('user.subdomain.create') }}"
         class="inline-flex items-center bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">
         + Ajukan Sekarang
      </a>
    </div>
  @else
    <div class="bg-white rounded shadow overflow-x-auto">
      <table class="min-w-full table-auto">
        <thead class="bg-gray-100">
          <tr>
            <th class="px-3 py-2 text-left">Tiket</th>
            <th class="px-3 py-2 text-left">Subdomain</th>
            <th class="px-3 py-2 text-left">IP Address</th>
            <th class="px-3 py-2 text-left">Aplikasi</th>
            <th class="px-3 py-2 text-left">Status</th>
            <th class="px-3 py-2 text-left">Diajukan</th>
            <th class="px-3 py-2 text-left">Selesai</th>
            <th class="px-3 py-2 text-left">Aksi</th>
          </tr>
        </thead>
        <tbody>
        @foreach ($items as $it)
          <tr class="border-b hover:bg-gray-50">
            <td class="px-3 py-2 font-mono text-sm">{{ $it->ticket_no }}</td>
            <td class="px-3 py-2">
              <span class="font-semibold">{{ $it->subdomain_requested }}</span>
              <span class="text-gray-500 text-xs">.kaltaraprov.go.id</span>
            </td>
            <td class="px-3 py-2 font-mono text-sm">{{ $it->ip_address }}</td>
            <td class="px-3 py-2">{{ $it->nama_aplikasi }}</td>
            <td class="px-3 py-2 capitalize">
              <span class="px-2 py-1 rounded text-xs
                @switch($it->status)
                  @case('menunggu') bg-yellow-100 text-yellow-800 @break
                  @case('proses')   bg-blue-100 text-blue-800 @break
                  @case('ditolak')  bg-red-100 text-red-800 @break
                  @case('selesai')  bg-green-100 text-green-800 @break
                @endswitch
              ">{{ $it->status }}</span>
            </td>
            <td class="px-3 py-2 text-sm">{{ optional($it->submitted_at)->format('Y-m-d H:i') }}</td>
            <td class="px-3 py-2 text-sm">{{ optional($it->completed_at)->format('Y-m-d H:i') ?: '—' }}</td>

            {{-- Aksi --}}
            <td class="px-3 py-2">
              @if ($it->status === 'menunggu')
                <div class="flex gap-2">
                  <a href="{{ route('user.subdomain.edit', $it->id) }}"
                     class="px-3 py-1 rounded bg-blue-600 hover:bg-blue-700 text-white text-sm">Edit</a>

                  <form action="{{ route('user.subdomain.destroy', $it->id) }}" method="POST"
                        onsubmit="return confirm('Hapus permohonan subdomain ini?');">
                    @csrf @method('DELETE')
                    <button type="submit"
                            class="px-3 py-1 rounded bg-red-600 hover:bg-red-700 text-white text-sm">
                      Hapus
                    </button>
                  </form>
                </div>
              @else
                <div class="flex gap-2">
                  <span class="px-3 py-1 rounded bg-gray-200 text-gray-500 text-sm cursor-not-allowed" title="Tidak dapat diedit setelah diproses">Edit</span>
                  <span class="px-3 py-1 rounded bg-gray-200 text-gray-500 text-sm cursor-not-allowed" title="Tidak dapat dihapus setelah diproses">Hapus</span>
                </div>
              @endif

              @if ($it->status === 'ditolak' && $it->rejection_reason)
                <div class="mt-1">
                  <button type="button" onclick="alert('Alasan Penolakan:\n\n{{ addslashes($it->rejection_reason) }}')"
                          class="text-xs text-red-600 hover:text-red-800 underline">
                    Lihat Alasan Penolakan
                  </button>
                </div>
              @endif
            </td>
          </tr>
        @endforeach
        </tbody>
      </table>
    </div>

    <div class="mt-4">
      {{ $items->links() }}
    </div>
  @endif
</div>
@endsection
