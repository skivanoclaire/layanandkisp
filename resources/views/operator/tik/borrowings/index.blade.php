@extends('layouts.authenticated')

@section('title','- Peminjaman Saya')
@section('header-title', 'Peminjaman Saya')

@section('content')
<div class="flex justify-between mb-4">
  <h1 class="text-2xl font-bold text-green-700">Peminjaman Saya</h1>
  <a href="{{ route('op.tik.borrow.create') }}" class="px-4 py-2 rounded bg-green-600 text-white">+ Buat Peminjaman</a>
</div>

@if (session('status'))
  <div class="mb-4 p-3 text-sm bg-green-50 border border-green-300 rounded">{{ session('status') }}</div>
@endif
@if ($errors->any())
  <div class="mb-4 p-3 text-sm bg-red-50 border border-red-300 rounded">
    {{ $errors->first() }}
  </div>
@endif

<div class="bg-white rounded shadow overflow-x-auto">
<table class="min-w-full table-auto">
  <thead class="bg-gray-100">
    <tr>
      <th class="px-3 py-2 text-left">Kode</th>
      <th class="px-3 py-2 text-left">Status</th>
      <th class="px-3 py-2 text-left">Mulai</th>
      <th class="px-3 py-2 text-left">Selesai</th>
      <th class="px-3 py-2 text-left">Barang</th>
      <th class="px-3 py-2">Aksi</th>
    </tr>
  </thead>
  <tbody>
    @forelse ($items as $b)
      <tr class="border-b">
        <td class="px-3 py-2 font-mono">{{ $b->code }}</td>
        <td class="px-3 py-2 capitalize">
          @php 
            $color = [
              'ongoing'=>'bg-blue-100 text-blue-800',
              'returned'=>'bg-green-100 text-green-800',
              'pending'=>'bg-yellow-100 text-yellow-800'
            ][$b->status] ?? 'bg-gray-100'; 
          @endphp
          <span class="px-2 py-1 text-xs rounded {{ $color }}">{{ $b->status_label }}</span>
        </td>
        <td class="px-3 py-2">{{ optional($b->started_at)->format('Y-m-d H:i') ?: '—' }}</td>
        <td class="px-3 py-2">{{ optional($b->finished_at)->format('Y-m-d H:i') ?: '—' }}</td>
        <td class="px-3 py-2">
          @foreach ($b->items as $it)
            <div>{{ $it->asset->name }} × {{ $it->qty }}</div>
          @endforeach
        </td>
        <td class="px-3 py-2 text-center">
          <div class="flex gap-2 justify-center">
            <a class="px-3 py-1 border rounded" href="{{ route('op.tik.borrow.show',$b->id) }}">Detail</a>

            {{-- Tombol Edit: hanya muncul kalau masih pending atau ongoing --}}
            @if (in_array($b->status, ['pending','ongoing']))
              <a class="px-3 py-1 border rounded bg-blue-50 hover:bg-blue-100" 
                 href="{{ route('op.tik.borrow.edit',$b->id) }}">Edit</a>
            @endif

            @if ($b->status === 'ongoing')
              <a class="px-3 py-1 border rounded bg-yellow-50 hover:bg-yellow-100" 
                 href="{{ route('op.tik.borrow.return.form',$b->id) }}">Kembalikan</a>
            @endif
          </div>
        </td>
      </tr>
    @empty
      <tr><td class="px-3 py-4 text-center" colspan="6">Belum ada transaksi</td></tr>
    @endforelse
  </tbody>
</table>
</div>

<div class="mt-4">{{ $items->links() }}</div>
@endsection
