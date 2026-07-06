<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Kirim notifikasi ke grup Telegram (mis. grup Bidang Sandi).
 *
 * Konfigurasi dibaca dari config/services.php => 'telegram'
 * (TELEGRAM_ENABLED, TELEGRAM_BOT_TOKEN, TELEGRAM_GROUP_CHAT_ID).
 */
class TelegramService
{
    protected bool $enabled;
    protected string $botToken;
    protected string $chatId;
    protected string $apiUrl;

    public function __construct(?string $chatId = null)
    {
        $this->enabled  = (bool) config('services.telegram.enabled', false);
        $this->botToken = (string) config('services.telegram.bot_token', '');
        $this->chatId   = (string) ($chatId ?? config('services.telegram.group_chat_id', ''));
        $this->apiUrl   = rtrim((string) config('services.telegram.api_url', 'https://api.telegram.org'), '/');
    }

    /**
     * Kirim pesan teks ke grup/chat Telegram.
     */
    public function sendMessage(string $message, ?string $chatId = null): bool
    {
        $target = $chatId ?: $this->chatId;

        if (!$this->enabled) {
            Log::info('Telegram: dinonaktifkan (TELEGRAM_ENABLED=false), pesan dilewati');
            return false;
        }

        if (empty($this->botToken) || empty($target)) {
            Log::warning('Telegram: bot token / chat id belum dikonfigurasi');
            return false;
        }

        try {
            $response = Http::withOptions(['verify' => false])
                ->asForm()
                ->post("{$this->apiUrl}/bot{$this->botToken}/sendMessage", [
                    'chat_id'                  => $target,
                    'text'                     => $message,
                    'parse_mode'               => 'HTML',
                    'disable_web_page_preview' => true,
                ]);

            if ($response->successful() && ($response->json('ok') === true)) {
                Log::info("Telegram: pesan terkirim ke chat {$target}");
                return true;
            }

            Log::error('Telegram: API mengembalikan kegagalan', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            return false;
        } catch (\Exception $e) {
            Log::error('Telegram: Exception - ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Alert ke grup Sandi: ada permohonan TTE baru masuk.
     */
    public function sendTteNewRequestAlert(
        string $ticketNo,
        string $serviceTypeName,
        string $nama,
        ?string $nip = null
    ): bool {
        $nipLine = $nip ? "NIP: <b>" . e($nip) . "</b>\n" : '';

        $message = "🆕 <b>[Permohonan Baru] {$serviceTypeName}</b>\n\n"
            . "No. Tiket: <b>" . e($ticketNo) . "</b>\n"
            . "Nama: <b>" . e($nama) . "</b>\n"
            . $nipLine
            . "Status: <b>Menunggu</b>\n\n"
            . "Mohon segera ditinjau dan diproses.\n"
            . "— Layanan TTE DKISP Kaltara";

        return $this->sendMessage($message);
    }

    /**
     * Notifikasi ke grup Sandi: status permohonan TTE berubah.
     */
    public function sendTteStatusUpdate(
        Model $ticket,
        string $serviceTypeName,
        string $newStatus,
        ?string $keteranganAdmin = null
    ): bool {
        $emoji   = $this->getStatusEmoji($newStatus);
        $label   = $this->getStatusLabel($newStatus);
        $catatan = $keteranganAdmin ?: '-';

        $message = "{$emoji} <b>[Update Status] {$serviceTypeName}</b>\n\n"
            . "No. Tiket: <b>" . e($ticket->ticket_no ?? '-') . "</b>\n"
            . "Nama: <b>" . e($ticket->nama ?? '-') . "</b>\n"
            . "Status: <b>{$label}</b>\n"
            . "Catatan: " . e($catatan) . "\n\n"
            . "— Layanan TTE DKISP Kaltara";

        return $this->sendMessage($message);
    }

    protected function getStatusLabel(string $status): string
    {
        return match ($status) {
            'menunggu', 'pending'  => 'Menunggu',
            'proses', 'diproses'   => 'Sedang Diproses',
            'selesai', 'processed' => 'Selesai',
            'ditolak', 'rejected'  => 'Ditolak',
            default                => ucfirst($status),
        };
    }

    protected function getStatusEmoji(string $status): string
    {
        return match ($status) {
            'menunggu', 'pending'  => '⏳',
            'proses', 'diproses'   => '🔄',
            'selesai', 'processed' => '✅',
            'ditolak', 'rejected'  => '❌',
            default                => 'ℹ️',
        };
    }
}
