<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MeetingRoomBooking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PeminjamanRuangRapatAdminController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');

        $query = MeetingRoomBooking::with(['user', 'meetingRoom', 'unitKerja'])
            ->orderByDesc('created_at');

        if ($status) {
            $query->where('status', $status);
        }

        $bookings = $query->paginate(25);

        $tabs = [
            ''           => ['label' => 'Semua', 'active' => 'bg-teal-600 text-white'],
            'menunggu'   => ['label' => 'Menunggu', 'active' => 'bg-yellow-500 text-white'],
            'disetujui'  => ['label' => 'Disetujui', 'active' => 'bg-green-600 text-white'],
            'ditolak'    => ['label' => 'Ditolak', 'active' => 'bg-red-600 text-white'],
            'dibatalkan' => ['label' => 'Dibatalkan', 'active' => 'bg-gray-600 text-white'],
        ];

        $counts = MeetingRoomBooking::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');
        $counts[''] = $counts->sum();

        return view('admin.peminjaman-ruang-rapat.index', compact('bookings', 'status', 'tabs', 'counts'));
    }

    public function show($id)
    {
        $booking = MeetingRoomBooking::with(['user', 'meetingRoom', 'unitKerja', 'processedBy'])->findOrFail($id);

        return view('admin.peminjaman-ruang-rapat.show', compact('booking'));
    }

    public function approve(Request $request, $id)
    {
        $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $booking = MeetingRoomBooking::findOrFail($id);

        if ($booking->status !== 'menunggu') {
            return back()->with('error', 'Hanya permohonan berstatus menunggu yang dapat disetujui.');
        }

        // Cek ulang saat persetujuan: bisa saja permohonan lain di jam yang sama sudah disetujui lebih dulu.
        $bentrok = MeetingRoomBooking::bentrok(
            $booking->meeting_room_id,
            $booking->tanggal->format('Y-m-d'),
            $booking->jam_mulai,
            $booking->jam_selesai,
            $booking->id
        )->exists();

        if ($bentrok) {
            return back()->with('error', 'Tidak dapat disetujui: ruang sudah disetujui untuk peminjaman lain pada jam yang sama.');
        }

        $booking->update([
            'status' => 'disetujui',
            'processed_by' => auth()->id(),
            'processed_at' => now(),
            'admin_notes' => $request->admin_notes ?: $booking->admin_notes,
        ]);

        Log::info('Meeting room booking approved', [
            'ticket_no' => $booking->ticket_no,
            'approved_by' => auth()->id(),
            'user_id' => $booking->user_id,
        ]);

        return redirect()->route('admin.peminjaman-ruang-rapat.show', $booking->id)
            ->with('success', "Permohonan {$booking->ticket_no} telah disetujui.");
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'admin_notes' => 'required|string|max:1000',
        ], [
            'admin_notes.required' => 'Alasan penolakan wajib diisi.',
        ]);

        $booking = MeetingRoomBooking::findOrFail($id);

        if ($booking->status !== 'menunggu') {
            return back()->with('error', 'Hanya permohonan berstatus menunggu yang dapat ditolak.');
        }

        $booking->update([
            'status' => 'ditolak',
            'processed_by' => auth()->id(),
            'processed_at' => now(),
            'admin_notes' => $request->admin_notes,
        ]);

        Log::info('Meeting room booking rejected', [
            'ticket_no' => $booking->ticket_no,
            'rejected_by' => auth()->id(),
            'user_id' => $booking->user_id,
            'reason' => $request->admin_notes,
        ]);

        return redirect()->route('admin.peminjaman-ruang-rapat.show', $booking->id)
            ->with('success', "Permohonan {$booking->ticket_no} telah ditolak.");
    }
}
