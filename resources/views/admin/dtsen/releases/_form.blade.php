@php($item = $item ?? null)

@include('partials.dtsen.errors')

<div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Rilis <span class="text-red-500">*</span></label>
            <input type="text" name="nomor_rilis" value="{{ old('nomor_rilis', $item->nomor_rilis ?? '') }}" required
                   placeholder="mis. DTSEN-2026-01" class="w-full px-4 py-2 border border-gray-300 rounded-lg font-mono">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Rilis <span class="text-red-500">*</span></label>
            <input type="date" name="tanggal_rilis" value="{{ old('tanggal_rilis', optional($item->tanggal_rilis ?? null)->format('Y-m-d')) }}" required
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg">
        </div>
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label>
            <textarea name="keterangan" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg">{{ old('keterangan', $item->keterangan ?? '') }}</textarea>
        </div>
        <div class="md:col-span-2">
            <label class="flex items-center gap-2">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $item->is_active ?? true))>
                <span class="text-sm text-gray-700">Aktif — dipakai sebagai acuan katalog variabel pada permohonan baru</span>
            </label>
        </div>
    </div>
</div>

<div class="flex flex-wrap gap-3">
    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2.5 rounded-lg font-semibold">Simpan</button>
    <a href="{{ route('admin.dtsen.releases.index') }}" class="px-6 py-2.5 rounded-lg font-semibold text-gray-600 hover:bg-gray-100">Batal</a>
</div>
