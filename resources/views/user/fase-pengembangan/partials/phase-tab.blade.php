<!-- Petunjuk Section -->
<div class="mb-6" x-data="{ showInstructions: false }">
    <button @click="showInstructions = !showInstructions"
        class="flex items-center text-blue-600 hover:text-blue-800 font-medium mb-2">
        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
        </svg>
        Petunjuk Pengisian
        <svg :class="showInstructions ? 'rotate-180' : ''" class="w-4 h-4 ml-2 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    <div x-show="showInstructions" x-transition class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <h3 class="font-semibold text-gray-800 mb-2">{{ $title }}</h3>
        <div class="text-sm text-gray-700 whitespace-pre-line">{{ $instructions }}</div>
    </div>
</div>

<!-- Upload Section -->
<div class="mb-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-3">Upload Dokumen</h3>

    <form action="{{ route('fase-pengembangan.upload', $proposalId) }}" method="POST" enctype="multipart/form-data" class="bg-gray-50 border-2 border-dashed border-gray-300 rounded-lg p-6">
        @csrf
        <input type="hidden" name="fase" value="{{ $fase }}">

        <div class="flex flex-col items-center">
            <svg class="w-12 h-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
            </svg>

            <div class="mb-3">
                <label for="dokumen_{{ $fase }}" class="cursor-pointer bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition inline-block">
                    Pilih File
                </label>
                <input type="file" id="dokumen_{{ $fase }}" name="dokumen" accept=".zip,.rar,.7z" class="hidden" required>
            </div>

            <p class="text-sm text-gray-600 mb-1">Format: ZIP, RAR, 7Z</p>
            <p class="text-xs text-gray-500">Maksimal 50MB</p>

            <button type="submit" class="mt-4 bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition">
                Upload Dokumen
            </button>
        </div>
    </form>

    <script>
        // Show selected filename
        document.getElementById('dokumen_{{ $fase }}').addEventListener('change', function() {
            const fileName = this.files[0]?.name;
            if (fileName) {
                const label = this.previousElementSibling;
                label.textContent = fileName;
            }
        });
    </script>
</div>

<!-- Documents List -->
<div>
    <h3 class="text-lg font-semibold text-gray-800 mb-3">Dokumen yang Telah Diupload</h3>

    @if ($documents->isEmpty())
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 text-center">
            <svg class="mx-auto h-10 w-10 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="text-sm text-gray-600">Belum ada dokumen yang diupload untuk fase ini.</p>
        </div>
    @else
        <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Nama File
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Ukuran
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Tanggal Upload
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Diupload Oleh
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach ($documents as $dokumen)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-gray-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="text-sm text-gray-900">{{ $dokumen->nama_file }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $dokumen->human_file_size }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $dokumen->created_at->format('d M Y H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $dokumen->uploader->name ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex gap-2">
                                    <!-- Download Button -->
                                    <a href="{{ route('fase-pengembangan.dokumen.download', [$proposalId, $dokumen->id]) }}"
                                        class="text-blue-600 hover:text-blue-900 inline-flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                        </svg>
                                        Download
                                    </a>

                                    <!-- Delete Button -->
                                    <form action="{{ route('fase-pengembangan.dokumen.delete', [$proposalId, $dokumen->id]) }}"
                                        method="POST" class="inline" onsubmit="return confirmDelete(event)">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900 inline-flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
