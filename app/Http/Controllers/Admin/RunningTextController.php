<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RunningText;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RunningTextController extends Controller
{
    public function index()
    {
        $runningTexts = RunningText::with(['pembuat:id,name', 'pengubah:id,name'])
            ->orderBy('urutan')
            ->orderByDesc('id')
            ->paginate(20);

        return view('admin.running-text.index', compact('runningTexts'));
    }

    public function create()
    {
        return view('admin.running-text.create');
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $runningText = RunningText::create($data + [
            'created_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
        ]);

        Log::info('Running text dibuat', [
            'admin' => $request->user()->email,
            'id'    => $runningText->id,
        ]);

        return redirect()->route('admin.running-text.index')
            ->with('success', 'Running text berhasil ditambahkan.');
    }

    public function edit(RunningText $runningText)
    {
        return view('admin.running-text.edit', compact('runningText'));
    }

    public function update(Request $request, RunningText $runningText)
    {
        $data = $this->validated($request);

        $runningText->update($data + ['updated_by' => $request->user()->id]);

        Log::info('Running text diperbarui', [
            'admin' => $request->user()->email,
            'id'    => $runningText->id,
        ]);

        return redirect()->route('admin.running-text.index')
            ->with('success', 'Running text berhasil diperbarui.');
    }

    /**
     * Aktif/nonaktif cepat dari halaman daftar.
     */
    public function toggle(Request $request, RunningText $runningText)
    {
        $runningText->update([
            'is_active'  => ! $runningText->is_active,
            'updated_by' => $request->user()->id,
        ]);

        return back()->with('success', $runningText->is_active
            ? 'Running text diaktifkan.'
            : 'Running text dinonaktifkan.');
    }

    public function destroy(Request $request, RunningText $runningText)
    {
        $runningText->delete();

        Log::info('Running text dihapus', [
            'admin' => $request->user()->email,
            'id'    => $runningText->id,
        ]);

        return redirect()->route('admin.running-text.index')
            ->with('success', 'Running text berhasil dihapus.');
    }

    /**
     * Validasi + sanitasi. Isi disanitasi dulu supaya batas panjang dihitung
     * dari teks bersih, dan input yang hanya berisi tag HTML ditolak sebagai kosong.
     */
    private function validated(Request $request): array
    {
        $tautanMentah = trim((string) $request->input('tautan'));
        $tautanBersih = RunningText::sanitizeTautan($tautanMentah);

        // Skema berbahaya (javascript:, data:, dll.) dibuang oleh sanitizeTautan.
        // Tetap laporkan sebagai error supaya admin tahu tautannya tidak tersimpan.
        if ($tautanMentah !== '' && $tautanBersih === null) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'tautan' => 'Tautan tidak valid. Gunakan URL yang diawali http:// atau https://.',
            ]);
        }

        $request->merge([
            'isi'    => RunningText::sanitizeIsi($request->input('isi')),
            'tautan' => $tautanBersih,
        ]);

        $validated = $request->validate([
            'isi'        => ['required', 'string', 'min:3', 'max:500'],
            'tautan'     => ['nullable', 'url', 'max:255', 'starts_with:http://,https://'],
            'urutan'     => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active'  => ['nullable', 'boolean'],
            'mulai_at'   => ['nullable', 'date'],
            'selesai_at' => ['nullable', 'date', 'after_or_equal:mulai_at'],
        ], [
            'isi.required'   => 'Isi teks wajib diisi (teks polos, tanpa tag HTML).',
            'isi.min'        => 'Isi teks terlalu pendek setelah dibersihkan dari tag HTML.',
            'isi.max'        => 'Isi teks maksimal 500 karakter.',
            'tautan.url'     => 'Tautan harus berupa URL yang valid.',
            'tautan.starts_with' => 'Tautan hanya boleh diawali http:// atau https://.',
            'selesai_at.after_or_equal' => 'Tanggal selesai tidak boleh lebih awal dari tanggal mulai.',
        ]);

        $validated['urutan']    = $validated['urutan'] ?? 0;
        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }
}
