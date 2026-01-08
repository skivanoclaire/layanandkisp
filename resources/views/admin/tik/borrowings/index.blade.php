@extends('layouts.authenticated')

@section('title','- Laporan Peminjaman')
@section('header-title', 'Laporan Peminjaman')

@section('content')
<h1 class="text-2xl font-bold text-green-700 mb-4">Laporan Peminjaman</h1>

<form method="GET" class="bg-white p-4 rounded shadow mb-4 grid md:grid-cols-4 gap-3">
  <div>
    <label class="block text-sm mb-1">Operator</label>
    <select name="operator" class="border rounded p-2 w-full">
      <option value="">Semua</option>
      @foreach ($operators as $op)
        <option value="{{ $op->id }}" @selected(request('operator')==$op->id)>{{ $op->name }}</option>
      @endforeach
    </select>
  </div>
<div>
  <label class="block text-sm mb-1">Status</label>
  <select name="status" class="border rounded p-2 w-full">
    <option value="">Semua</option>
    @foreach (['ongoing' => 'Sedang dipinjam', 'returned' => 'Dikembalikan'] as $val => $label)
      <option value="{{ $val }}" @selected(request('status')==$val)>{{ $label }}</option>
    @endforeach
  </select>
</div>
  <div>
    <label class="block text-sm mb-1">Dari Tanggal</label>
    <input type="date" name="date_from" value="{{ request('date_from') }}" class="border rounded p-2 w-full">
  </div>
  <div>
    <label class="block text-sm mb-1">Sampai Tanggal</label>
    <input type="date" name="date_to" value="{{ request('date_to') }}" class="border rounded p-2 w-full">
  </div>
  <div class="md:col-span-4">
    <button class="px-4 py-2 border rounded">Filter</button>
  </div>
</form>

{{-- Legend Keterangan Warna --}}
<div class="mb-4 bg-white p-4 rounded shadow">
  <h3 class="text-sm font-semibold text-gray-700 mb-2">Keterangan Durasi Peminjaman:</h3>
  <div class="flex flex-wrap gap-4 text-sm">
    <div class="flex items-center gap-2">
      <div class="w-4 h-4 rounded border-l-4 border-yellow-500 bg-yellow-50"></div>
      <span>7-13 hari (Perlu perhatian)</span>
    </div>
    <div class="flex items-center gap-2">
      <div class="w-4 h-4 rounded border-l-4 border-orange-500 bg-orange-50"></div>
      <span>14-29 hari (Terlambat)</span>
    </div>
    <div class="flex items-center gap-2">
      <div class="w-4 h-4 rounded border-l-4 border-red-500 bg-red-50"></div>
      <span>≥ 30 hari (Sangat terlambat - perlu force close)</span>
    </div>
  </div>
</div>

<div class="bg-white rounded shadow overflow-x-auto">
<table class="min-w-full table-auto">
  <thead class="bg-gray-100">
    <tr>
      <th class="px-3 py-2 text-left">Kode</th>
      <th class="px-3 py-2 text-left">Operator</th>
      <th class="px-3 py-2 text-left">Status</th>
      <th class="px-3 py-2 text-left">Mulai</th>
      <th class="px-3 py-2 text-left">Selesai</th>
      <th class="px-3 py-2 text-left">Durasi</th>
      <th class="px-3 py-2 text-left">Barang</th>
      <th class="px-3 py-2">Aksi</th>
    </tr>
  </thead>
  <tbody>
    @forelse ($items as $b)
      @php
        // Calculate duration
        $daysOngoing = 0;
        $warningLevel = '';

        if ($b->status === 'ongoing' && $b->started_at) {
          $daysOngoing = $b->started_at->diffInDays(now());

          if ($daysOngoing >= 30) {
            $warningLevel = 'bg-red-50 border-l-4 border-red-500';
          } elseif ($daysOngoing >= 14) {
            $warningLevel = 'bg-orange-50 border-l-4 border-orange-500';
          } elseif ($daysOngoing >= 7) {
            $warningLevel = 'bg-yellow-50 border-l-4 border-yellow-500';
          }
        }
      @endphp

      <tr class="border-b {{ $warningLevel }} hover:bg-gray-50">
        <td class="px-3 py-2 font-mono">
          {{ $b->code }}
          @if($b->closed_by)
            <span class="ml-1 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800" title="Ditutup paksa oleh admin">
              <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
              </svg>
              Paksa Tutup
            </span>
          @endif
        </td>
        <td class="px-3 py-2">{{ $b->operator?->name }}</td>
        <td class="px-3 py-2">
          <span class="inline-flex px-2 py-1 text-xs rounded-full
            {{ $b->status === 'ongoing' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
            {{ $b->status_label }}
          </span>
        </td>
        <td class="px-3 py-2 text-sm">{{ optional($b->started_at)->format('d M Y H:i') ?: '—' }}</td>
        <td class="px-3 py-2 text-sm">{{ optional($b->finished_at)->format('d M Y H:i') ?: '—' }}</td>
        <td class="px-3 py-2">
          @if($b->status === 'ongoing' && $b->started_at)
            <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded
              {{ $daysOngoing >= 30 ? 'bg-red-100 text-red-800' : '' }}
              {{ $daysOngoing >= 14 && $daysOngoing < 30 ? 'bg-orange-100 text-orange-800' : '' }}
              {{ $daysOngoing >= 7 && $daysOngoing < 14 ? 'bg-yellow-100 text-yellow-800' : '' }}
              {{ $daysOngoing < 7 ? 'bg-gray-100 text-gray-800' : '' }}">
              {{ $daysOngoing }} hari
              @if($daysOngoing >= 30)
                <svg class="w-4 h-4 ml-1" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
              @endif
            </span>
          @else
            <span class="text-gray-400 text-sm">—</span>
          @endif
        </td>
        <td class="px-3 py-2 text-sm">
          @foreach ($b->items as $it)
            <div>{{ $it->asset->name }} × {{ $it->qty }}</div>
          @endforeach
        </td>
        <td class="px-3 py-2 text-center">
          <a class="px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded inline-flex items-center"
             href="{{ route('admin.tik.borrow.show',$b->id) }}">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            </svg>
            Detail
          </a>
        </td>
      </tr>
    @empty
      <tr><td class="px-3 py-4 text-center text-gray-500" colspan="8">Tidak ada data</td></tr>
    @endforelse
  </tbody>
</table>
</div>

<div class="mt-4">{{ $items->links() }}</div>
@endsection
