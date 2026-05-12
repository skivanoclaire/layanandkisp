<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ShortlinkRequest;
use App\Models\ShortlinkRequestLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ShortlinkRequestController extends Controller
{
    /** Aturan validasi kode pendek yang diusulkan (charset YOURLS base62 + tanda hubung). */
    private const KEYWORD_REGEX = '/^[A-Za-z0-9-]+$/';

    public function index()
    {
        $requests = ShortlinkRequest::where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('user.shortlink.index', compact('requests'));
    }

    public function create()
    {
        return view('user.shortlink.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        $user = Auth::user();

        $item = new ShortlinkRequest($data);
        $item->user_id      = $user->id;
        $item->nama         = $user->name;
        $item->nip          = $user->nik;
        $item->instansi     = $user->unitKerja->nama ?? null;
        $item->status       = 'menunggu';
        $item->submitted_at = now();
        $item->save();

        ShortlinkRequestLog::create([
            'shortlink_request_id' => $item->id,
            'actor_id'             => $user->id,
            'action'               => 'created',
            'note'                 => 'Permohonan diajukan oleh pemohon.',
        ]);

        return redirect()->route('user.shortlink.index')
            ->with('success', "Permohonan pemendek tautan {$item->ticket_no} berhasil diajukan.");
    }

    public function show(ShortlinkRequest $shortlink)
    {
        $this->authorizeOwner($shortlink);
        $shortlink->load(['logs.actor', 'processedBy']);

        return view('user.shortlink.show', ['item' => $shortlink]);
    }

    public function edit(ShortlinkRequest $shortlink)
    {
        $this->authorizeOwner($shortlink);
        abort_unless($shortlink->status === 'menunggu', 403, 'Permohonan sudah diproses, tidak dapat diubah.');

        return view('user.shortlink.edit', ['item' => $shortlink]);
    }

    public function update(Request $request, ShortlinkRequest $shortlink)
    {
        $this->authorizeOwner($shortlink);
        abort_unless($shortlink->status === 'menunggu', 403, 'Permohonan sudah diproses, tidak dapat diubah.');

        $shortlink->fill($this->validateData($request));
        $shortlink->save();

        ShortlinkRequestLog::create([
            'shortlink_request_id' => $shortlink->id,
            'actor_id'             => Auth::id(),
            'action'               => 'updated',
            'note'                 => 'Permohonan diperbarui oleh pemohon.',
        ]);

        return redirect()->route('user.shortlink.show', $shortlink)
            ->with('success', 'Permohonan berhasil diperbarui.');
    }

    public function destroy(ShortlinkRequest $shortlink)
    {
        $this->authorizeOwner($shortlink);
        abort_unless($shortlink->status === 'menunggu', 403, 'Permohonan sudah diproses, tidak dapat dibatalkan.');

        $shortlink->delete();

        return redirect()->route('user.shortlink.index')->with('success', 'Permohonan dibatalkan.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'long_url'          => ['required', 'url', 'max:2048'],
            'title'             => ['nullable', 'string', 'max:191'],
            'requested_keyword' => ['nullable', 'string', 'max:40', 'regex:' . self::KEYWORD_REGEX],
            'keperluan'         => ['required', 'string', 'max:2000'],
        ], [
            'long_url.url'              => 'URL tujuan harus berupa alamat web yang valid (diawali http:// atau https://).',
            'requested_keyword.regex'   => 'Kode pendek hanya boleh berisi huruf, angka, dan tanda hubung (-).',
        ]);
    }

    private function authorizeOwner(ShortlinkRequest $shortlink): void
    {
        abort_unless($shortlink->user_id === Auth::id(), 403);
    }
}
