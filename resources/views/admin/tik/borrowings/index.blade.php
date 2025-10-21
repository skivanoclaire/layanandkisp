@extends($layout)

@section('title','Laporan Peminjaman')

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

<div class="bg-white rounded shadow overflow-x-auto">
<table class="min-w-full table-auto">
  <thead class="bg-gray-100">
    <tr>
      <th class="px-3 py-2 text-left">Kode</th>
      <th class="px-3 py-2 text-left">Operator</th>
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
        <td class="px-3 py-2">{{ $b->operator?->name }}</td>
        <td class="px-3 py-2">{{ $b->status_label }}</td>
        <td class="px-3 py-2">{{ optional($b->started_at)->format('Y-m-d H:i') ?: '—' }}</td>
        <td class="px-3 py-2">{{ optional($b->finished_at)->format('Y-m-d H:i') ?: '—' }}</td>
        <td class="px-3 py-2">
          @foreach ($b->items as $it)
            <div>{{ $it->asset->name }} × {{ $it->qty }}</div>
          @endforeach
        </td>
        <td class="px-3 py-2 text-center">
          <a class="px-3 py-1 border rounded" href="{{ route('admin.tik.borrow.show',$b->id) }}">Detail</a>
        </td>
      </tr>
    @empty
      <tr><td class="px-3 py-4 text-center" colspan="7">Tidak ada data</td></tr>
    @endforelse
  </tbody>
</table>
</div>

<div class="mt-4">{{ $items->links() }}</div>
@endsection
