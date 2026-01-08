@extends('layouts.authenticated')
@section('title','- Permohonan Video Conference')
@section('header-title', 'Permohonan Video Conference')

@section('content')
<div class="max-w-6xl mx-auto">
  <!-- Operating Hours Information -->
  <div class="mb-6 bg-blue-50 border-l-4 border-blue-500 rounded-lg p-4 shadow-sm">
    <div class="flex items-start">
      <svg class="w-6 h-6 text-blue-500 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
      <div>
        <h3 class="text-lg font-semibold text-blue-800 mb-1">Jam Layanan</h3>
        <p class="text-blue-700">
          <span class="font-semibold">Senin s.d. Kamis:</span> 07.30 - 16.00 WITA<br>
          <span class="font-semibold">Jumat:</span> 07.30 - 16.30 WITA
        </p>
      </div>
    </div>
  </div>

  <div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-purple-700">Permohonan Video Conference – Pengajuan Anda</h1>
    <a href="{{ route('user.vidcon.create') }}"
       class="inline-flex items-center bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded">
       + Buat Permohonan Baru
    </a>
  </div>

  @if (session('success'))
    <div class="mb-4 rounded border border-green-300 bg-green-50 p-3 text-sm">
      {{ session('success') }}
    </div>
  @endif

  @if (session('error'))
    <div class="mb-4 rounded border border-red-300 bg-red-50 p-3 text-sm">
      {{ session('error') }}
    </div>
  @endif

  @if ($items->count() === 0)
    <div class="bg-white rounded shadow p-6 text-center">
      <p class="mb-4">Belum ada permohonan video conference.</p>
      <a href="{{ route('user.vidcon.create') }}"
         class="inline-flex items-center bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded">
         + Ajukan Sekarang
      </a>
    </div>
  @else
    <div class="bg-white rounded shadow overflow-x-auto">
      <table class="min-w-full table-auto">
        <thead class="bg-gray-100">
          <tr>
            <th class="px-3 py-2 text-left">Tiket</th>
            <th class="px-3 py-2 text-left">Judul Kegiatan</th>
            <th class="px-3 py-2 text-left">Tanggal</th>
            <th class="px-3 py-2 text-left">Platform</th>
            <th class="px-3 py-2 text-left">Status</th>
            <th class="px-3 py-2 text-left">Diajukan</th>
            <th class="px-3 py-2 text-left">Aksi</th>
          </tr>
        </thead>
        <tbody>
        @foreach ($items as $it)
          <tr class="border-b hover:bg-gray-50">
            <td class="px-3 py-2 font-mono">{{ $it->ticket_no }}</td>
            <td class="px-3 py-2">{{ $it->judul_kegiatan }}</td>
            <td class="px-3 py-2">
              {{ $it->tanggal_mulai->format('d/m/Y') }}
              @if($it->tanggal_mulai->format('Y-m-d') !== $it->tanggal_selesai->format('Y-m-d'))
                - {{ $it->tanggal_selesai->format('d/m/Y') }}
              @endif
              <br>
              <span class="text-xs text-gray-600">{{ $it->jam_mulai }} - {{ $it->jam_selesai }}</span>
            </td>
            <td class="px-3 py-2">{{ $it->platform_display }}</td>
            <td class="px-3 py-2">{!! $it->status_badge !!}</td>
            <td class="px-3 py-2">{{ optional($it->submitted_at)->format('d/m/Y H:i') }}</td>

            <td class="px-3 py-2">
              @if ($it->status === 'menunggu')
                <div class="flex gap-2">
                  <a href="{{ route('user.vidcon.edit', $it->id) }}"
                     class="px-3 py-1 rounded bg-blue-600 hover:bg-blue-700 text-white text-sm">Edit</a>

                  <form action="{{ route('user.vidcon.destroy', $it->id) }}" method="POST"
                        onsubmit="return confirm('Hapus permohonan ini?');">
                    @csrf @method('DELETE')
                    <button type="submit"
                            class="px-3 py-1 rounded bg-red-600 hover:bg-red-700 text-white text-sm">
                      Hapus
                    </button>
                  </form>
                </div>
              @elseif ($it->status === 'selesai')
                <a href="{{ route('user.vidcon.edit', $it->id) }}"
                   class="px-3 py-1 rounded bg-green-600 hover:bg-green-700 text-white text-sm">Lihat Detail</a>
              @else
                <span class="px-3 py-1 rounded bg-gray-200 text-gray-500 text-sm">-</span>
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
