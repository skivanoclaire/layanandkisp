@extends($layout)

@section('title', 'Pengembalian')

@section('content')
    <h1 class="text-2xl font-bold text-green-700 mb-4">Pengembalian {{ $borrowing->code }}</h1>

    <div class="bg-white p-4 rounded shadow mb-4">
        <h2 class="font-semibold mb-2">Checklist barang</h2>
        <ul class="list-disc pl-5 text-sm">
            @foreach ($borrowing->items as $it)
                <li>{{ $it->asset->name }} × {{ $it->qty }}</li>
            @endforeach
        </ul>
    </div>

    <form method="POST" action="{{ route('op.tik.borrow.return.do', $borrowing->id) }}" enctype="multipart/form-data"
        class="bg-white p-4 rounded shadow space-y-4">
        @csrf
        <div>
            <label class="block text-sm mb-1">Foto saat mengembalikan (multi diperbolehkan)</label>
            <input type="file" name="photos[]" accept="image/*" multiple class="border rounded p-2 w-full">
        </div>
        <div>
            <label class="block text-sm mb-1">Catatan (opsional)</label>
            <textarea name="notes" rows="3" class="border rounded p-2 w-full"></textarea>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('op.tik.borrow.show', $borrowing->id) }}" class="px-4 py-2 border rounded">Batal</a>
            <button class="px-4 py-2 bg-green-600 text-white rounded">Selesai & Kembalikan</button>
        </div>
    </form>
@endsection
