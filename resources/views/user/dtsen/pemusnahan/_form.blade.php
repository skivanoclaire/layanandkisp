@php($report = $report ?? null)
@php($selectedRequestId = old('dtsen_data_request_id', $report->dtsen_data_request_id ?? $selected?->id))

@include('partials.dtsen.errors')

<div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
    <h2 class="text-lg font-bold text-gray-800 mb-1">Berita Acara Pemusnahan Data DTSEN</h2>
    <p class="text-sm text-gray-500 mb-4">
        Identitas data yang dimusnahkan (level, variabel, cakupan wilayah) diambil otomatis dari permohonan/BAST terkait.
    </p>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Permohonan / BAST Terkait <span class="text-red-500">*</span></label>
            <select name="dtsen_data_request_id" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg @error('dtsen_data_request_id') border-red-500 @enderror">
                @foreach ($reportable as $r)
                    <option value="{{ $r->id }}" @selected((int) $selectedRequestId === $r->id)>
                        {{ $r->ticket_no }} — {{ $r->nama_program }} ({{ $r->level_label }})
                    </option>
                @endforeach
            </select>
        </div>

        @if ($selected)
            <div class="md:col-span-2 rounded-lg border border-gray-200 bg-gray-50 p-3 text-sm text-gray-700">
                <p><span class="text-gray-500">Level:</span> {{ $selected->level_label }}</p>
                <p><span class="text-gray-500">Cakupan wilayah:</span> {{ implode('; ', $selected->cakupanWilayahLabels()) ?: '-' }}</p>
                <p><span class="text-gray-500">Variabel:</span> {{ $selected->requestVariables->count() }} variabel</p>
                @if ($selected->kak_retensi_batas_waktu)
                    <p><span class="text-gray-500">Batas retensi pada KAK:</span> {{ $selected->kak_retensi_batas_waktu->format('d/m/Y') }}</p>
                @endif
            </div>
        @endif

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Dasar / Alasan Pemusnahan <span class="text-red-500">*</span></label>
            <select name="dasar_pemusnahan" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                @foreach (\App\Models\DtsenDestructionReport::dasarLabels() as $val => $label)
                    <option value="{{ $val }}" @selected(old('dasar_pemusnahan', $report->dasar_pemusnahan ?? 'habis_retensi') === $val)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Waktu Pelaksanaan <span class="text-red-500">*</span></label>
            <input type="datetime-local" name="waktu_pelaksanaan" required
                   value="{{ old('waktu_pelaksanaan', $report?->waktu_pelaksanaan?->format('Y-m-d\TH:i')) }}"
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg">
            <p class="text-xs text-gray-500 mt-1">
                Salinan berita acara wajib disampaikan ke DKISP maksimal
                {{ \App\Models\DtsenDestructionReport::BATAS_PENYAMPAIAN_HARI }} hari kalender sejak pemusnahan.
            </p>
        </div>

        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Metode Pemusnahan <span class="text-red-500">*</span></label>
            <textarea name="metode_pemusnahan" rows="3" required class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                      placeholder="mis. penghapusan permanen berkas elektronik beserta seluruh salinan cadangan, disertai penimpaan media penyimpanan">{{ old('metode_pemusnahan', $report->metode_pemusnahan ?? '') }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Petugas Pelaksana <span class="text-red-500">*</span></label>
            <input type="text" name="petugas_nama" value="{{ old('petugas_nama', $report->petugas_nama ?? '') }}" required
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg">
            <p class="text-xs text-gray-500 mt-1">Petugas yang ditunjuk Kepala Perangkat Daerah.</p>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">NIP Petugas</label>
            <input type="text" name="petugas_nip" value="{{ old('petugas_nip', $report->petugas_nip ?? '') }}"
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Saksi (opsional)</label>
            <input type="text" name="saksi_nama" value="{{ old('saksi_nama', $report->saksi_nama ?? '') }}"
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Unit Saksi</label>
            <input type="text" name="saksi_unit" value="{{ old('saksi_unit', $report->saksi_unit ?? '') }}"
                   placeholder="mis. unit keamanan informasi/persandian"
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg">
        </div>

        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Berita Acara Bertanda Tangan (PDF)</label>
            <input type="file" name="berita_acara" accept=".pdf" class="w-full text-sm border border-gray-300 rounded-lg p-2">
            @if ($report && $report->file_path)
                <p class="text-xs text-green-600 mt-1">
                    Terlampir: <a href="{{ Storage::url($report->file_path) }}" target="_blank" class="underline">{{ basename($report->file_path) }}</a>
                </p>
            @endif
        </div>
    </div>
</div>

<div class="flex flex-wrap gap-3">
    <button type="submit" name="action" value="draft"
            class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-6 py-2.5 rounded-lg font-semibold">Simpan sebagai Draft</button>
    <button type="submit" name="action" value="lapor"
            class="bg-green-600 hover:bg-green-700 text-white px-6 py-2.5 rounded-lg font-semibold">Sampaikan ke DKISP</button>
    <a href="{{ route('user.dtsen.pemusnahan.index') }}" class="px-6 py-2.5 rounded-lg font-semibold text-gray-600 hover:bg-gray-100">Batal</a>
</div>
