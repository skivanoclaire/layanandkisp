<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\MeetingRoom;
use App\Models\MeetingRoomBooking;
use Illuminate\Http\Request;

class PeminjamanRuangRapatController extends Controller
{
    public function index()
    {
        $bookings = MeetingRoomBooking::with(['meetingRoom'])
            ->where('user_id', auth()->id())
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('user.peminjaman-ruang-rapat.index', compact('bookings'));
    }

    public function create()
    {
        $rooms = MeetingRoom::aktif()->orderBy('nama')->get();

        if ($rooms->isEmpty()) {
            return redirect()->route('user.peminjaman-ruang-rapat.index')
                ->with('error', 'Belum ada ruang rapat yang dapat dipinjam saat ini.');
        }

        return view('user.peminjaman-ruang-rapat.create', compact('rooms'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'meeting_room_id' => 'required|integer|exists:meeting_rooms,id',
            'no_hp' => 'required|string|max:20',
            'keperluan' => 'required|string|max:1000',
            'jumlah_peserta' => 'required|integer|min:1|max:1000',
            'tanggal' => 'required|date|after_or_equal:today',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
        ], [
            'meeting_room_id.required' => 'Ruang rapat wajib dipilih.',
            'meeting_room_id.exists' => 'Ruang rapat yang dipilih tidak valid.',
            'no_hp.required' => 'No. HP/WhatsApp wajib diisi.',
            'keperluan.required' => 'Keperluan peminjaman wajib diisi.',
            'jumlah_peserta.required' => 'Jumlah peserta wajib diisi.',
            'jumlah_peserta.min' => 'Jumlah peserta minimal 1 orang.',
            'tanggal.required' => 'Tanggal wajib diisi.',
            'tanggal.after_or_equal' => 'Tanggal tidak boleh kurang dari hari ini.',
            'jam_mulai.required' => 'Jam mulai wajib diisi.',
            'jam_selesai.required' => 'Jam selesai wajib diisi.',
            'jam_selesai.after' => 'Jam selesai harus setelah jam mulai.',
        ]);

        $room = MeetingRoom::aktif()->findOrFail($validated['meeting_room_id']);

        if ($validated['jumlah_peserta'] > $room->kapasitas) {
            return back()->withInput()
                ->withErrors(['jumlah_peserta' => "Jumlah peserta melebihi kapasitas {$room->nama} ({$room->kapasitas} orang)."]);
        }

        $sudahDipinjam = MeetingRoomBooking::bentrok(
            $room->id,
            $validated['tanggal'],
            $validated['jam_mulai'],
            $validated['jam_selesai']
        )->exists();

        if ($sudahDipinjam) {
            return back()->withInput()
                ->withErrors(['jam_mulai' => 'Ruang rapat sudah disetujui untuk peminjaman lain pada jam tersebut. Silakan pilih jam atau ruang lain.']);
        }

        MeetingRoomBooking::create([
            'user_id' => auth()->id(),
            'meeting_room_id' => $room->id,
            'unit_kerja_id' => auth()->user()->unit_kerja_id,
            'nama' => auth()->user()->name,
            'nip' => auth()->user()->nip,
            'no_hp' => $validated['no_hp'],
            'keperluan' => $validated['keperluan'],
            'jumlah_peserta' => $validated['jumlah_peserta'],
            'tanggal' => $validated['tanggal'],
            'jam_mulai' => $validated['jam_mulai'],
            'jam_selesai' => $validated['jam_selesai'],
            'status' => 'menunggu',
        ]);

        return redirect()->route('user.peminjaman-ruang-rapat.index')
            ->with('success', 'Permohonan peminjaman ruang rapat berhasil dikirim. Tim kami akan segera menindaklanjuti.');
    }

    public function show(MeetingRoomBooking $peminjamanRuangRapat)
    {
        abort_unless($peminjamanRuangRapat->user_id === auth()->id(), 403, 'Unauthorized action.');

        $peminjamanRuangRapat->load(['meetingRoom', 'processedBy']);

        return view('user.peminjaman-ruang-rapat.show', ['booking' => $peminjamanRuangRapat]);
    }

    public function cancel(MeetingRoomBooking $peminjamanRuangRapat)
    {
        abort_unless($peminjamanRuangRapat->user_id === auth()->id(), 403, 'Unauthorized action.');

        if ($peminjamanRuangRapat->status !== 'menunggu') {
            return back()->with('error', 'Hanya permohonan yang masih menunggu yang dapat dibatalkan.');
        }

        $peminjamanRuangRapat->update(['status' => 'dibatalkan']);

        return redirect()->route('user.peminjaman-ruang-rapat.show', $peminjamanRuangRapat->id)
            ->with('success', "Permohonan {$peminjamanRuangRapat->ticket_no} telah dibatalkan.");
    }
}
