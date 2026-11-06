<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MeetingRoom;
use Illuminate\Http\Request;

class RuangRapatAdminController extends Controller
{
    public function index()
    {
        $rooms = MeetingRoom::withCount('bookings')->orderBy('nama')->get();

        return view('admin.ruang-rapat.index', compact('rooms'));
    }

    public function store(Request $request)
    {
        MeetingRoom::create($this->validatedData($request));

        return redirect()->route('admin.ruang-rapat.index')
            ->with('success', 'Ruang rapat berhasil ditambahkan.');
    }

    public function update(Request $request, MeetingRoom $meetingRoom)
    {
        $meetingRoom->update($this->validatedData($request));

        return redirect()->route('admin.ruang-rapat.index')
            ->with('success', "Data {$meetingRoom->nama} berhasil diperbarui.");
    }

    public function destroy(MeetingRoom $meetingRoom)
    {
        if ($meetingRoom->bookings()->exists()) {
            return redirect()->route('admin.ruang-rapat.index')
                ->with('error', "{$meetingRoom->nama} sudah memiliki riwayat peminjaman. Nonaktifkan saja agar riwayat tetap tersimpan.");
        }

        $nama = $meetingRoom->nama;
        $meetingRoom->delete();

        return redirect()->route('admin.ruang-rapat.index')
            ->with('success', "Ruang rapat {$nama} berhasil dihapus.");
    }

    private function validatedData(Request $request): array
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'lokasi' => 'nullable|string|max:255',
            'kapasitas' => 'required|integer|min:1|max:5000',
            'fasilitas' => 'nullable|string|max:1000',
        ], [
            'nama.required' => 'Nama ruangan wajib diisi.',
            'kapasitas.required' => 'Kapasitas wajib diisi.',
            'kapasitas.min' => 'Kapasitas minimal 1 orang.',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }
}
