@extends('layouts.authenticated')
@section('title','- Permohonan Video Conference')
@section('header-title', 'Permohonan Fasilitasi Rapat Virtual/Webinar/Streaming (Zoom/YT)')

@section('content')
<div class="max-w-6xl mx-auto">

  <!-- Jam Layanan -->
  <div class="mb-5 bg-blue-50 border-l-4 border-blue-500 rounded-lg p-4 shadow-sm">
    <div class="flex items-start">
      <svg class="w-5 h-5 text-blue-500 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
      <div class="text-sm">
        <p class="font-semibold text-blue-800 mb-0.5">Jam Layanan</p>
        <p class="text-blue-700">
          <span class="font-semibold">Senin–Kamis:</span> 07.30 – 16.00 WITA &nbsp;|&nbsp;
          <span class="font-semibold">Jumat:</span> 07.30 – 16.30 WITA
        </p>
      </div>
    </div>
  </div>

  <!-- Judul + Tombol — stack di mobile, row di md+ -->
  <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-5">
    <div>
      <h1 class="text-lg sm:text-xl font-bold text-purple-700 leading-snug">
        Permohonan Fasilitasi Rapat Virtual / Webinar / Streaming
      </h1>
      <p class="text-sm text-gray-500 mt-0.5">Pengajuan Anda</p>
    </div>
    <a href="{{ route('user.vidcon.create') }}"
       class="inline-flex items-center justify-center gap-1 bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-sm font-semibold whitespace-nowrap self-start sm:self-auto flex-shrink-0">
      + Buat Permohonan Baru
    </a>
  </div>

  @if (session('success'))
    <div class="mb-4 rounded border border-green-300 bg-green-50 p-3 text-sm">{{ session('success') }}</div>
  @endif

  @if (session('error'))
    <div class="mb-4 rounded border border-red-300 bg-red-50 p-3 text-sm">{{ session('error') }}</div>
  @endif

  @if ($items->count() === 0)
    <div class="bg-white rounded-lg shadow p-8 text-center">
      <p class="text-gray-500 mb-4">Belum ada permohonan video conference.</p>
      <a href="{{ route('user.vidcon.create') }}"
         class="inline-flex items-center bg-purple-600 hover:bg-purple-700 text-white px-5 py-2 rounded-lg text-sm font-semibold">
        + Ajukan Sekarang
      </a>
    </div>
  @else
    <!-- Tabel dengan scroll horizontal -->
    <div class="bg-white rounded-lg shadow">
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-600 border-b border-gray-200">
            <tr>
              <th class="px-3 py-2 text-left whitespace-nowrap">Tiket</th>
              <th class="px-3 py-2 text-left whitespace-nowrap">Judul Kegiatan</th>
              <th class="px-3 py-2 text-left whitespace-nowrap">Tanggal</th>
              <th class="px-3 py-2 text-left whitespace-nowrap">Platform</th>
              <th class="px-3 py-2 text-left whitespace-nowrap">Status</th>
              <th class="px-3 py-2 text-left whitespace-nowrap hidden sm:table-cell">Diajukan</th>
              <th class="px-3 py-2 text-left whitespace-nowrap">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            @foreach ($items as $it)
              <tr class="hover:bg-gray-50 transition-colors">
                <td class="px-3 py-2 font-mono text-xs whitespace-nowrap align-top">{{ $it->ticket_no }}</td>

                <td class="px-3 py-2 align-top max-w-[180px]">
                  <span class="line-clamp-2">{{ $it->judul_kegiatan }}</span>
                </td>

                <td class="px-3 py-2 align-top whitespace-nowrap">
                  {{ $it->tanggal_mulai->format('d/m/Y') }}
                  @if($it->tanggal_mulai->format('Y-m-d') !== $it->tanggal_selesai->format('Y-m-d'))
                    <br>– {{ $it->tanggal_selesai->format('d/m/Y') }}
                  @endif
                  <br><span class="text-xs text-gray-500">{{ $it->jam_mulai }} – {{ $it->jam_selesai }}</span>
                </td>

                <td class="px-3 py-2 align-top whitespace-nowrap">{{ $it->platform_display }}</td>

                <td class="px-3 py-2 align-top whitespace-nowrap">{!! $it->status_badge !!}</td>

                <td class="px-3 py-2 align-top whitespace-nowrap text-xs text-gray-500 hidden sm:table-cell">
                  {{ optional($it->submitted_at)->format('d/m/Y H:i') }}
                </td>

                <td class="px-3 py-2 align-top whitespace-nowrap">
                  @if ($it->status === 'menunggu')
                    <div class="flex gap-1">
                      <a href="{{ route('user.vidcon.edit', $it->id) }}"
                         class="px-2 py-1 rounded bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium">Edit</a>
                      <form action="{{ route('user.vidcon.destroy', $it->id) }}" method="POST"
                            onsubmit="return confirm('Hapus permohonan ini?');">
                        @csrf @method('DELETE')
                        <button type="submit"
                                class="px-2 py-1 rounded bg-red-600 hover:bg-red-700 text-white text-xs font-medium">
                          Hapus
                        </button>
                      </form>
                    </div>
                  @elseif ($it->status === 'selesai')
                    <div class="flex gap-1">
                      <a href="{{ route('user.vidcon.edit', $it->id) }}"
                         class="px-2 py-1 rounded bg-green-600 hover:bg-green-700 text-white text-xs font-medium whitespace-nowrap">
                        Lihat Detail
                      </a>
                      <a href="{{ route('user.vidcon.survey', $it->id) }}"
                         class="px-2 py-1 rounded bg-amber-500 hover:bg-amber-600 text-white text-xs font-medium whitespace-nowrap">
                        Beri Penilaian
                      </a>
                    </div>
                  @else
                    <span class="text-gray-400 text-xs">—</span>
                  @endif
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>

    <div class="mt-4">{{ $items->links() }}</div>
  @endif
</div>
@endsection
