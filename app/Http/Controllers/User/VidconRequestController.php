<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\VidconRequest;
use App\Models\UnitKerja;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class VidconRequestController extends Controller
{
    public function index()
    {
        // Daftar pengajuan milik user aktif
        $items = VidconRequest::where('user_id', auth()->id())
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('user.vidcon.index', compact('items'));
    }

    public function create()
    {
        // Check if user has NIP
        $user = auth()->user();
        if (empty($user->nip)) {
            return redirect()->route('user.vidcon.index')
                ->with('error', 'NIP Anda belum terdaftar. Silakan hubungi Administrator untuk memperbarui data NIP Anda.');
        }

        // Get master data for dropdowns
        $unitKerjaList = UnitKerja::active()->orderBy('nama')->get();

        return view('user.vidcon.create', compact('unitKerjaList'));
    }

    public function store(Request $r)
    {
        // Check if user has NIP
        $user = auth()->user();
        if (empty($user->nip)) {
            throw ValidationException::withMessages([
                'nip' => 'NIP Anda belum terdaftar. Silakan hubungi Administrator.'
            ]);
        }

        $data = $r->validate([
            // Informasi Pemohon
            'unit_kerja_id'      => ['required', 'exists:unit_kerjas,id'],
            'no_hp'              => ['required', 'string', 'max:30'],

            // Video Conference Details
            'judul_kegiatan'     => ['required', 'string', 'max:255'],
            'deskripsi_kegiatan' => ['nullable', 'string'],
            'tanggal_mulai'      => ['required', 'date', 'after_or_equal:today'],
            'tanggal_selesai'    => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'jam_mulai'          => ['required', 'date_format:H:i'],
            'jam_selesai'        => ['required', 'date_format:H:i', 'after:jam_mulai'],
            'platform'           => ['required', Rule::in(['Zoom', 'Google Meet', 'Microsoft Teams', 'YouTube Live', 'Lainnya'])],
            'platform_lainnya'   => ['required_if:platform,Lainnya', 'nullable', 'string', 'max:100'],
            'jumlah_peserta'     => ['nullable', 'integer', 'min:1', 'max:10000'],
            'keperluan_khusus'   => ['nullable', 'string'],
        ], [
            'unit_kerja_id.required'           => 'Instansi wajib diisi.',
            'judul_kegiatan.required'          => 'Judul Kegiatan wajib diisi.',
            'tanggal_mulai.required'           => 'Tanggal Mulai wajib diisi.',
            'tanggal_mulai.after_or_equal'     => 'Tanggal Mulai tidak boleh kurang dari hari ini.',
            'tanggal_selesai.required'         => 'Tanggal Selesai wajib diisi.',
            'tanggal_selesai.after_or_equal'   => 'Tanggal Selesai tidak boleh kurang dari Tanggal Mulai.',
            'jam_mulai.required'               => 'Jam Mulai wajib diisi.',
            'jam_selesai.required'             => 'Jam Selesai wajib diisi.',
            'jam_selesai.after'                => 'Jam Selesai harus lebih besar dari Jam Mulai.',
            'platform.required'                => 'Platform wajib dipilih.',
            'platform_lainnya.required_if'     => 'Nama platform wajib diisi jika memilih "Lainnya".',
        ]);

        // Create vidcon request
        $req = new VidconRequest($data);
        $req->user_id       = auth()->id();
        $req->nama          = $user->name;
        $req->nip           = $user->nip;
        $req->email_pemohon = $user->email;
        $req->no_hp         = $data['no_hp'];
        $req->ticket_no     = VidconRequest::nextTicket();
        $req->submitted_at  = now();
        $req->status        = 'menunggu';
        $req->save();

        return redirect()->route('user.vidcon.thanks', $req->id);
    }

    public function thanks(VidconRequest $vidconRequest)
    {
        // Only allow user to view their own request
        if ($vidconRequest->user_id !== auth()->id()) {
            abort(403);
        }

        return view('user.vidcon.thanks', compact('vidconRequest'));
    }

    public function edit(VidconRequest $vidconRequest)
    {
        // Load operators relation
        $vidconRequest->load('operators');

        // Only allow editing if request is still pending or viewing if completed
        if ($vidconRequest->status !== 'menunggu' && $vidconRequest->status !== 'selesai') {
            return redirect()->route('user.vidcon.index')
                ->with('error', 'Permohonan tidak dapat diakses.');
        }

        // Only allow user to edit their own request
        if ($vidconRequest->user_id !== auth()->id()) {
            abort(403);
        }

        $unitKerjaList = UnitKerja::active()->orderBy('nama')->get();

        return view('user.vidcon.edit', compact('vidconRequest', 'unitKerjaList'));
    }

    public function update(Request $r, VidconRequest $vidconRequest)
    {
        // Only allow editing if request is still pending
        if ($vidconRequest->status !== 'menunggu') {
            return redirect()->route('user.vidcon.index')
                ->with('error', 'Permohonan yang sudah diproses tidak dapat diubah.');
        }

        // Only allow user to update their own request
        if ($vidconRequest->user_id !== auth()->id()) {
            abort(403);
        }

        $data = $r->validate([
            // Informasi Pemohon
            'unit_kerja_id'      => ['required', 'exists:unit_kerjas,id'],
            'no_hp'              => ['required', 'string', 'max:30'],

            // Video Conference Details
            'judul_kegiatan'     => ['required', 'string', 'max:255'],
            'deskripsi_kegiatan' => ['nullable', 'string'],
            'tanggal_mulai'      => ['required', 'date', 'after_or_equal:today'],
            'tanggal_selesai'    => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'jam_mulai'          => ['required', 'date_format:H:i'],
            'jam_selesai'        => ['required', 'date_format:H:i', 'after:jam_mulai'],
            'platform'           => ['required', Rule::in(['Zoom', 'Google Meet', 'Microsoft Teams', 'YouTube Live', 'Lainnya'])],
            'platform_lainnya'   => ['required_if:platform,Lainnya', 'nullable', 'string', 'max:100'],
            'jumlah_peserta'     => ['nullable', 'integer', 'min:1', 'max:10000'],
            'keperluan_khusus'   => ['nullable', 'string'],
        ]);

        $vidconRequest->update($data);

        return redirect()->route('user.vidcon.index')
            ->with('success', 'Permohonan berhasil diperbarui.');
    }

    public function destroy(VidconRequest $vidconRequest)
    {
        // Only allow deletion if request is still pending
        if ($vidconRequest->status !== 'menunggu') {
            return redirect()->route('user.vidcon.index')
                ->with('error', 'Permohonan yang sudah diproses tidak dapat dihapus.');
        }

        // Only allow user to delete their own request
        if ($vidconRequest->user_id !== auth()->id()) {
            abort(403);
        }

        $vidconRequest->delete();

        return redirect()->route('user.vidcon.index')
            ->with('success', 'Permohonan berhasil dihapus.');
    }
}
