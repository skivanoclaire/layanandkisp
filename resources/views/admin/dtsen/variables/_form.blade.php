@php($item = $item ?? null)

@include('partials.dtsen.errors')

<div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Kode <span class="text-red-500">*</span></label>
            <input type="text" name="kode" value="{{ old('kode', $item->kode ?? '') }}" required
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg font-mono">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
            <input type="text" name="kategori" value="{{ old('kategori', $item->kategori ?? '') }}"
                   list="kategori-list" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
            <datalist id="kategori-list">
                @foreach (\App\Models\DtsenVariable::whereNotNull('kategori')->distinct()->pluck('kategori') as $k)
                    <option value="{{ $k }}"></option>
                @endforeach
            </datalist>
        </div>
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Variabel <span class="text-red-500">*</span></label>
            <input type="text" name="nama" value="{{ old('nama', $item->nama ?? '') }}" required
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg">
        </div>
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
            <textarea name="deskripsi" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg">{{ old('deskripsi', $item->deskripsi ?? '') }}</textarea>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Level Hak Akses Minimal <span class="text-red-500">*</span></label>
            <select name="level_minimal" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                @foreach (\App\Models\DtsenDataRequest::levelLabels() as $lvl => $label)
                    <option value="{{ $lvl }}" @selected((int) old('level_minimal', $item->level_minimal ?? 2) === $lvl)>{{ $label }}</option>
                @endforeach
            </select>
            <p class="text-xs text-gray-500 mt-1">Menentukan kelengkapan dokumen yang wajib dipenuhi pemohon.</p>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Satuan</label>
            <input type="text" name="satuan" value="{{ old('satuan', $item->satuan ?? '') }}"
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Rilis DTSEN</label>
            <select name="dtsen_release_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                <option value="">-- Tidak terikat rilis --</option>
                @foreach ($releases as $r)
                    <option value="{{ $r->id }}" @selected((string) old('dtsen_release_id', $item->dtsen_release_id ?? '') === (string) $r->id)>
                        {{ $r->nomor_rilis }} ({{ $r->tanggal_rilis->format('d/m/Y') }})
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Urutan Tampil</label>
            <input type="number" name="urutan" min="0" value="{{ old('urutan', $item->urutan ?? 0) }}"
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg">
        </div>
        <div class="md:col-span-2">
            <label class="flex items-center gap-2">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $item->is_active ?? true))>
                <span class="text-sm text-gray-700">Aktif — tampil pada katalog pemilihan variabel</span>
            </label>
        </div>
    </div>
</div>

<div class="flex flex-wrap gap-3">
    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2.5 rounded-lg font-semibold">Simpan</button>
    <a href="{{ route('admin.dtsen.variables.index') }}" class="px-6 py-2.5 rounded-lg font-semibold text-gray-600 hover:bg-gray-100">Batal</a>
</div>
