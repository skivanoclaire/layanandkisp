<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\YourlsException;
use App\Http\Controllers\Controller;
use App\Models\ShortlinkRequest;
use App\Models\ShortlinkRequestLog;
use App\Services\YourlsClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ShortlinkRequestAdminController extends Controller
{
    public function __construct(private YourlsClient $yourls) {}

    // GET /admin/digital/shortlink
    public function index(Request $r)
    {
        $query = ShortlinkRequest::with(['user', 'processedBy'])->orderByDesc('created_at');

        if ($r->filled('status')) {
            $query->where('status', $r->status);
        }
        if ($r->filled('search')) {
            $s = $r->search;
            $query->where(function ($q) use ($s) {
                $q->where('ticket_no', 'like', "%{$s}%")
                  ->orWhere('nama', 'like', "%{$s}%")
                  ->orWhere('nip', 'like', "%{$s}%")
                  ->orWhere('long_url', 'like', "%{$s}%")
                  ->orWhere('keyword', 'like', "%{$s}%");
            });
        }

        $requests = $query->paginate(20)->withQueryString();
        $status   = $r->status;

        return view('admin.shortlink.index', compact('requests', 'status'));
    }

    // GET /admin/digital/shortlink/{shortlink}
    public function show(ShortlinkRequest $shortlink)
    {
        $shortlink->load(['user', 'processedBy', 'logs.actor']);
        return view('admin.shortlink.show', ['item' => $shortlink]);
    }

    // POST /admin/digital/shortlink/{shortlink}/process
    public function process(Request $r, ShortlinkRequest $shortlink)
    {
        if ($shortlink->status !== 'menunggu') {
            return back()->with('error', 'Permohonan tidak dalam status menunggu.');
        }

        $shortlink->update([
            'status'        => 'proses',
            'processing_at' => now(),
            'processed_by'  => Auth::id(),
            'admin_note'    => $r->input('note') ?: $shortlink->admin_note,
        ]);
        $this->log($shortlink, 'status:menunggu->proses', $r->input('note'));

        return back()->with('success', 'Permohonan ditandai sedang diproses.');
    }

    // POST /admin/digital/shortlink/{shortlink}/approve
    public function approve(Request $r, ShortlinkRequest $shortlink)
    {
        if (!in_array($shortlink->status, ['menunggu', 'proses'], true)) {
            return back()->with('error', 'Permohonan ini sudah final, tidak dapat disetujui lagi.');
        }

        $r->validate([
            'keyword' => ['nullable', 'string', 'max:40', 'regex:/^[A-Za-z0-9-]+$/'],
            'note'    => ['nullable', 'string', 'max:1000'],
        ], [
            'keyword.regex' => 'Kode pendek hanya boleh berisi huruf, angka, dan tanda hubung (-).',
        ]);

        $keyword = $r->filled('keyword') ? $r->keyword : ($shortlink->requested_keyword ?: null);
        $title   = $shortlink->title ?: null;

        try {
            $res = $this->yourls->createShortUrl($shortlink->long_url, $keyword, $title);
        } catch (YourlsException $e) {
            Log::error('Shortlink approve: YOURLS error', ['id' => $shortlink->id, 'error' => $e->getMessage()]);
            return back()->with('error', 'Gagal membuat short link di YOURLS: ' . $e->getMessage());
        }

        $code = $res['code'] ?? '';

        // Kode sudah dipakai -> minta admin ganti kode
        if (($res['status'] ?? '') === 'fail' && $code === 'error:keyword') {
            return back()->with('error', "Kode pendek \"{$keyword}\" sudah dipakai atau dicadangkan. Ubah kode lalu coba lagi.");
        }

        // URL sudah pernah dipendekkan -> pakai link yang sudah ada
        $reused = false;
        if (($res['status'] ?? '') === 'fail' && $code === 'error:url') {
            $reused      = true;
            $finalKeyword = $res['url']['keyword'] ?? null;
            $finalShort   = $res['shorturl'] ?? ($finalKeyword ? $this->yourls->baseUrl() . '/' . $finalKeyword : null);
            if (!$finalKeyword) {
                return back()->with('error', 'URL ini sudah pernah dipendekkan tetapi YOURLS tidak mengembalikan kode. Periksa di panel YOURLS.');
            }
        } elseif (($res['status'] ?? '') === 'success') {
            $finalKeyword = $res['url']['keyword'] ?? $keyword;
            $finalShort   = $res['shorturl'] ?? ($finalKeyword ? $this->yourls->baseUrl() . '/' . $finalKeyword : null);
        } else {
            return back()->with('error', 'YOURLS menolak permintaan: ' . ($res['message'] ?? 'penyebab tidak diketahui') . ($code ? " ({$code})" : ''));
        }

        $shortlink->update([
            'status'       => 'selesai',
            'keyword'      => $finalKeyword,
            'short_url'    => $finalShort,
            'is_active'    => true,
            'completed_at' => now(),
            'processed_by' => Auth::id(),
            'admin_note'   => $r->input('note') ?: $shortlink->admin_note,
        ]);

        $this->log(
            $shortlink,
            $reused ? 'reused_existing_in_yourls' : 'created_in_yourls',
            ($reused ? 'Memakai short link yang sudah ada: ' : 'Short link dibuat di YOURLS: ') . $finalShort
            . ($r->filled('note') ? ' — ' . $r->input('note') : '')
        );

        return back()->with('success', "Permohonan disetujui. Short link: {$finalShort}" . ($reused ? ' (memakai link yang sudah ada)' : ''));
    }

    // POST /admin/digital/shortlink/{shortlink}/reject
    public function reject(Request $r, ShortlinkRequest $shortlink)
    {
        if (!in_array($shortlink->status, ['menunggu', 'proses'], true)) {
            return back()->with('error', 'Permohonan ini sudah final, tidak dapat ditolak.');
        }

        $r->validate(['note' => ['required', 'string', 'max:1000']], ['note.required' => 'Alasan penolakan wajib diisi.']);

        $shortlink->update([
            'status'       => 'ditolak',
            'rejected_at'  => now(),
            'processed_by' => Auth::id(),
            'admin_note'   => $r->note,
        ]);
        $this->log($shortlink, 'status:->ditolak', $r->note);

        return back()->with('success', 'Permohonan ditolak.');
    }

    // POST /admin/digital/shortlink/{shortlink}/update-destination
    public function updateDestination(Request $r, ShortlinkRequest $shortlink)
    {
        if ($shortlink->status !== 'selesai' || !$shortlink->keyword || !$shortlink->is_active) {
            return back()->with('error', 'Hanya link aktif yang sudah dibuat yang bisa diubah tujuannya.');
        }

        $r->validate([
            'new_url' => ['required', 'url', 'max:2048'],
            'note'    => ['nullable', 'string', 'max:1000'],
        ], ['new_url.url' => 'URL tujuan baru harus berupa alamat web yang valid.']);

        try {
            $res = $this->yourls->updateUrl($shortlink->keyword, $r->new_url, $shortlink->title ?: '');
        } catch (YourlsException $e) {
            return back()->with('error', 'Gagal mengubah tujuan di YOURLS: ' . $e->getMessage());
        }

        if (($res['status'] ?? '') !== 'success') {
            return back()->with('error', 'YOURLS menolak perubahan: ' . ($res['message'] ?? 'penyebab tidak diketahui'));
        }

        $old = $shortlink->long_url;
        $shortlink->update(['long_url' => $r->new_url]);
        $this->log($shortlink, 'destination_updated', "Tujuan diubah dari {$old} ke {$r->new_url}" . ($r->filled('note') ? ' — ' . $r->note : ''));

        return back()->with('success', 'URL tujuan berhasil diubah di YOURLS.');
    }

    // POST /admin/digital/shortlink/{shortlink}/disable
    public function disable(Request $r, ShortlinkRequest $shortlink)
    {
        if (!$shortlink->keyword || !$shortlink->is_active) {
            return back()->with('error', 'Link tidak aktif atau belum dibuat.');
        }

        try {
            $res = $this->yourls->delete($shortlink->keyword);
        } catch (YourlsException $e) {
            return back()->with('error', 'Gagal menghapus link di YOURLS: ' . $e->getMessage());
        }

        if (($res['status'] ?? '') !== 'success') {
            return back()->with('error', 'YOURLS menolak penghapusan: ' . ($res['message'] ?? 'penyebab tidak diketahui'));
        }

        $shortlink->update(['is_active' => false]);
        $this->log($shortlink, 'disabled_in_yourls', "Short link {$shortlink->short_url} dihapus dari YOURLS." . ($r->filled('note') ? ' — ' . $r->note : ''));

        return back()->with('success', 'Short link dinonaktifkan (dihapus) di YOURLS.');
    }

    // POST /admin/digital/shortlink/{shortlink}/enable
    public function enable(Request $r, ShortlinkRequest $shortlink)
    {
        if (!$shortlink->keyword || $shortlink->is_active) {
            return back()->with('error', 'Link sudah aktif atau tidak punya kode.');
        }

        try {
            $res = $this->yourls->createShortUrl($shortlink->long_url, $shortlink->keyword, $shortlink->title ?: null);
        } catch (YourlsException $e) {
            return back()->with('error', 'Gagal membuat ulang link di YOURLS: ' . $e->getMessage());
        }

        $code = $res['code'] ?? '';
        if (($res['status'] ?? '') === 'fail' && $code === 'error:keyword') {
            return back()->with('error', "Kode \"{$shortlink->keyword}\" kini sudah dipakai pihak lain. Tidak bisa diaktifkan dengan kode yang sama.");
        }
        if (($res['status'] ?? '') !== 'success' && $code !== 'error:url') {
            return back()->with('error', 'YOURLS menolak permintaan: ' . ($res['message'] ?? 'penyebab tidak diketahui'));
        }

        $shortlink->update(['is_active' => true]);
        $this->log($shortlink, 'enabled_in_yourls', "Short link {$shortlink->short_url} dibuat ulang di YOURLS.");

        return back()->with('success', 'Short link diaktifkan kembali di YOURLS.');
    }

    // POST /admin/digital/shortlink/{shortlink}/refresh-stats
    public function refreshStats(ShortlinkRequest $shortlink)
    {
        if (!$shortlink->keyword) {
            return back()->with('error', 'Permohonan belum punya short link.');
        }

        try {
            $res = $this->yourls->urlStats($shortlink->keyword);
        } catch (YourlsException $e) {
            return back()->with('error', 'Gagal mengambil statistik dari YOURLS: ' . $e->getMessage());
        }

        if (!isset($res['link'])) {
            return back()->with('error', 'YOURLS tidak mengembalikan data statistik (link mungkin sudah dihapus).');
        }

        $shortlink->update([
            'clicks'          => (int) ($res['link']['clicks'] ?? 0),
            'stats_synced_at' => now(),
        ]);

        return back()->with('success', "Statistik diperbarui: {$shortlink->clicks} klik.");
    }

    // POST /admin/digital/shortlink/{shortlink}/update-note
    public function updateNote(Request $r, ShortlinkRequest $shortlink)
    {
        $r->validate(['admin_note' => ['nullable', 'string', 'max:2000']]);
        $shortlink->update(['admin_note' => $r->admin_note]);
        $this->log($shortlink, 'note_updated', 'Catatan admin diperbarui.');

        return back()->with('success', 'Catatan admin diperbarui.');
    }

    private function log(ShortlinkRequest $shortlink, string $action, ?string $note = null): void
    {
        ShortlinkRequestLog::create([
            'shortlink_request_id' => $shortlink->id,
            'actor_id'             => Auth::id(),
            'action'               => $action,
            'note'                 => $note,
        ]);
    }
}
