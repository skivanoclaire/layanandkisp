{{-- Petunjuk Pengisian + unduhan format dokumen (.docx) satu tahap; dipakai halaman pemohon dan admin.
     Parameter: $fase (kunci di config/fase_pengembangan.php). --}}
@php
    $tahap = config("fase_pengembangan.tahap.$fase");
    $formatPath = config('fase_pengembangan.folder_format') . '/' . ($tahap['format'] ?? '');
    $formatTersedia = !empty($tahap['format']) && file_exists(public_path($formatPath));
@endphp

@if ($tahap)
    <div class="mb-6" x-data="{ showInstructions: false }">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-2">
            <button type="button" @click="showInstructions = !showInstructions"
                class="flex items-center text-blue-600 hover:text-blue-800 font-medium">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                Petunjuk Pengisian
                <svg :class="showInstructions ? 'rotate-180' : ''" class="w-4 h-4 ml-2 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            @if ($formatTersedia)
                <a href="{{ asset($formatPath) }}" download
                    class="inline-flex items-center bg-white border border-blue-300 text-blue-700 px-3 py-1.5 rounded-lg hover:bg-blue-50 transition text-sm font-medium">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Unduh Format Dokumen {{ $tahap['judul'] }} (.docx)
                </a>
            @endif
        </div>

        <div x-show="showInstructions" x-transition class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <h3 class="font-semibold text-gray-800 mb-2">{{ $tahap['judul'] }}</h3>
            <div class="text-sm text-gray-700 whitespace-pre-line">{{ $tahap['petunjuk'] }}</div>
        </div>
    </div>
@endif
