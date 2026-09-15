@php($report = $report ?? null)
@php($selectedRequestId = old('dtsen_data_request_id', $report->dtsen_data_request_id ?? $selected?->id))
@php($variabelInit = array_map('intval', (array) old('variabel_ids', $report->variabel_ids ?? [])))

@include('partials.dtsen.errors')

<div class="bg-white rounded-lg shadow-sm border p-6 mb-6"
     x-data="{
        requestId: '{{ $selectedRequestId }}',
        variables: {{ Js::from($reportable->mapWithKeys(fn ($r) => [$r->id => $r->requestVariables->map(fn ($rv) => ['id' => $rv->dtsen_variable_id, 'label' => ($rv->variable->kode ?? '') . ' — ' . ($rv->variable->nama ?? '')])->values()])) }},
        selected: {{ Js::from($variabelInit) }},
        get options() { return this.variables[this.requestId] ?? [] },
        toggle(id, checked) {
            id = Number(id);
            if (checked) { if (!this.selected.includes(id)) this.selected.push(id) }
            else { this.selected = this.selected.filter((v) => v !== id) }
        },
     }">

    <h2 class="text-lg font-bold text-gray-800 mb-4">Laporan Pemanfaatan DTSEN</h2>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Permohonan / BAST Terkait <span class="text-red-500">*</span></label>
            <select name="dtsen_data_request_id" x-model="requestId" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg @error('dtsen_data_request_id') border-red-500 @enderror">
                @foreach ($reportable as $r)
                    <option value="{{ $r->id }}" @selected((int) $selectedRequestId === $r->id)>
                        {{ $r->ticket_no }} — {{ $r->nama_program }} ({{ $r->level_label }})
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Periode Pelaporan — Mulai <span class="text-red-500">*</span></label>
            <input type="date" name="periode_mulai" value="{{ old('periode_mulai', $report?->periode_mulai?->format('Y-m-d')) }}" required
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Periode Pelaporan — Akhir <span class="text-red-500">*</span></label>
            <input type="date" name="periode_akhir" value="{{ old('periode_akhir', $report?->periode_akhir?->format('Y-m-d')) }}" required
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg">
        </div>

        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Program/Kegiatan <span class="text-red-500">*</span></label>
            <input type="text" name="nama_program" value="{{ old('nama_program', $report->nama_program ?? $selected?->nama_program) }}" required
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg">
        </div>

        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-2">Variabel Data yang Dimanfaatkan</label>
            <div class="border rounded-lg p-3 max-h-56 overflow-y-auto space-y-1">
                <template x-for="opt in options" :key="opt.id">
                    <label class="flex items-start gap-2 text-sm">
                        <input type="checkbox" name="variabel_ids[]" :value="opt.id" class="mt-1"
                               :checked="selected.includes(Number(opt.id))"
                               @change="toggle(opt.id, $event.target.checked)">
                        <span x-text="opt.label"></span>
                    </label>
                </template>
                <p x-show="options.length === 0" x-cloak class="text-sm text-gray-500">
                    Tidak ada variabel tercatat pada permohonan ini.
                </p>
            </div>
        </div>

        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Hasil Pemanfaatan <span class="text-red-500">*</span></label>
            <textarea name="hasil_pemanfaatan" rows="5" required class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                      placeholder="Uraikan hasil pemanfaatan data: keluaran yang dihasilkan, keputusan/kebijakan yang didukung, jumlah sasaran yang terverifikasi, dsb.">{{ old('hasil_pemanfaatan', $report->hasil_pemanfaatan ?? '') }}</textarea>
        </div>

        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Kendala yang Dihadapi</label>
            <textarea name="kendala" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg">{{ old('kendala', $report->kendala ?? '') }}</textarea>
        </div>

        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Dokumentasi Pendukung</label>
            <input type="file" name="lampiran" accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg,.zip"
                   class="w-full text-sm border border-gray-300 rounded-lg p-2">
            <p class="text-xs text-gray-500 mt-1">Berkas laporan, tangkapan layar, atau rekap Excel (maks 10MB).</p>
            @if ($report && $report->file_path)
                <p class="text-xs text-green-600 mt-1">
                    Terlampir: <a href="{{ Storage::url($report->file_path) }}" target="_blank" class="underline">{{ basename($report->file_path) }}</a>
                </p>
            @endif
        </div>
    </div>
</div>

<div class="rounded-lg border border-blue-200 bg-blue-50 p-4 text-sm text-blue-900 mb-6">
    Laporan ini diteruskan secara berjenjang ke <strong>Bapperida</strong> selaku koordinator Forum Satu Data Daerah
    dan <strong>DKISP</strong> selaku prosesor untuk direkapitulasi.
</div>

<div class="flex flex-wrap gap-3">
    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2.5 rounded-lg font-semibold">Kirim Laporan</button>
    <a href="{{ route('user.dtsen.pemanfaatan.index') }}" class="px-6 py-2.5 rounded-lg font-semibold text-gray-600 hover:bg-gray-100">Batal</a>
</div>
