<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\VidconRequest;
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
        // Hanya cek NIP (sudah jadi guard sebelumnya). Untuk Instansi & No HP
        // tidak redirect — view akan menampilkan banner peringatan saja.
        $user = auth()->user();
        if (empty($user->nip)) {
            return redirect()->route('user.vidcon.index')
                ->with('error', 'NIP Anda belum terdaftar. Silakan hubungi Administrator untuk memperbarui data NIP Anda.');
        }

        return view('user.vidcon.create');
    }

    public function store(Request $r)
    {
        $user = auth()->user();
        if (empty($user->nip) || empty($user->unit_kerja_id) || empty($user->phone)) {
            throw ValidationException::withMessages([
                'profil' => 'Profil Anda belum lengkap (NIP/Instansi/No. HP). Silakan lengkapi profil terlebih dahulu.'
            ]);
        }

        $data = $r->validate([
            'judul_kegiatan'     => ['required', 'string', 'max:255'],
            'deskripsi_kegiatan' => ['nullable', 'string'],
            'lokasi_kegiatan'    => ['required', 'string', 'max:255'],
            'tanggal_mulai'      => ['required', 'date', 'after_or_equal:today'],
            'tanggal_selesai'    => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'jam_mulai'          => ['required', 'date_format:H:i'],
            'jam_selesai'        => ['required', 'date_format:H:i', 'after:jam_mulai'],
            'platform'           => ['required', Rule::in(['Zoom', 'Google Meet', 'Microsoft Teams', 'YouTube Live', 'Lainnya'])],
            'platform_lainnya'   => ['required_if:platform,Lainnya', 'nullable', 'string', 'max:100'],
            'jenis_layanan'      => ['required', Rule::in(['link_host', 'link_host_operator', 'operator'])],
            'pemohon_link_meeting'     => ['required_if:jenis_layanan,operator', 'nullable', 'string', 'max:2000'],
            'pemohon_meeting_id'       => ['nullable', 'string', 'max:255'],
            'pemohon_meeting_password' => ['nullable', 'string', 'max:255'],
            'jumlah_peserta'     => ['nullable', 'integer', 'min:1', 'max:10000'],
            'keperluan_khusus'   => ['nullable', 'string'],
        ], [
            'judul_kegiatan.required'          => 'Judul Kegiatan wajib diisi.',
            'lokasi_kegiatan.required'         => 'Lokasi Kegiatan wajib diisi.',
            'tanggal_mulai.required'           => 'Tanggal Mulai wajib diisi.',
            'tanggal_mulai.after_or_equal'     => 'Tanggal Mulai tidak boleh kurang dari hari ini.',
            'tanggal_selesai.required'         => 'Tanggal Selesai wajib diisi.',
            'tanggal_selesai.after_or_equal'   => 'Tanggal Selesai tidak boleh kurang dari Tanggal Mulai.',
            'jam_mulai.required'               => 'Jam Mulai wajib diisi.',
            'jam_selesai.required'             => 'Jam Selesai wajib diisi.',
            'jam_selesai.after'                => 'Jam Selesai harus lebih besar dari Jam Mulai.',
            'platform.required'                => 'Platform wajib dipilih.',
            'platform_lainnya.required_if'     => 'Nama platform wajib diisi jika memilih "Lainnya".',
            'jenis_layanan.required'           => 'Jenis Layanan wajib dipilih.',
            'pemohon_link_meeting.required_if' => 'Link Meeting wajib diisi jika memilih "Operator saja".',
        ]);

        // Create vidcon request — instansi & no_hp dipaksa dari profil user
        $req = new VidconRequest($data);
        $req->user_id       = $user->id;
        $req->unit_kerja_id = $user->unit_kerja_id;
        $req->nama          = $user->name;
        $req->nip           = $user->nip;
        $req->email_pemohon = $user->email;
        $req->no_hp         = $user->phone;
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

    /**
     * Halaman "Beri Penilaian" — embed survei kepuasan layanan untuk permohonan yang sudah selesai.
     */
    public function survey(VidconRequest $vidconRequest)
    {
        // Hanya pemilik permohonan yang boleh mengakses
        if ($vidconRequest->user_id !== auth()->id()) {
            abort(403);
        }

        // Penilaian hanya tersedia untuk permohonan yang sudah selesai
        if ($vidconRequest->status !== 'selesai') {
            abort(403, 'Penilaian hanya tersedia untuk permohonan yang sudah selesai.');
        }

        // URL survei dari pengaturan terpusat (token dikelola via Manajemen Survei Digital)
        $surveyUrl = \App\Services\SurveiDigitalService::urlFor('vidcon');

        return view('user.vidcon.survey', [
            'vidconRequest' => $vidconRequest,
            'surveyUrl'     => $surveyUrl,
            'ticket'        => $vidconRequest->ticket_no,
        ]);
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

        return view('user.vidcon.edit', compact('vidconRequest'));
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
            'judul_kegiatan'     => ['required', 'string', 'max:255'],
            'deskripsi_kegiatan' => ['nullable', 'string'],
            'lokasi_kegiatan'    => ['required', 'string', 'max:255'],
            'tanggal_mulai'      => ['required', 'date', 'after_or_equal:today'],
            'tanggal_selesai'    => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'jam_mulai'          => ['required', 'date_format:H:i'],
            'jam_selesai'        => ['required', 'date_format:H:i', 'after:jam_mulai'],
            'platform'           => ['required', Rule::in(['Zoom', 'Google Meet', 'Microsoft Teams', 'YouTube Live', 'Lainnya'])],
            'platform_lainnya'   => ['required_if:platform,Lainnya', 'nullable', 'string', 'max:100'],
            'jenis_layanan'      => ['required', Rule::in(['link_host', 'link_host_operator', 'operator'])],
            'pemohon_link_meeting'     => ['required_if:jenis_layanan,operator', 'nullable', 'string', 'max:2000'],
            'pemohon_meeting_id'       => ['nullable', 'string', 'max:255'],
            'pemohon_meeting_password' => ['nullable', 'string', 'max:255'],
            'jumlah_peserta'     => ['nullable', 'integer', 'min:1', 'max:10000'],
            'keperluan_khusus'   => ['nullable', 'string'],
        ]);

        // Sinkronkan instansi & no_hp dari profil user (server-side enforcement)
        $user = auth()->user();
        $data['unit_kerja_id'] = $user->unit_kerja_id;
        $data['no_hp']         = $user->phone;

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
