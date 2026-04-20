<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FonnteWhatsappService
{
    protected string $token;
    protected string $apiUrl;
    protected string $channel;
    protected string $header;

    public function __construct(string $channel = 'sandi')
    {
        $this->channel = $channel;
        $this->token = (string) config("services.fonnte.tokens.{$channel}", '');
        $this->apiUrl = config('services.fonnte.url', 'https://api.fonnte.com/send');
        $this->header = match ($channel) {
            'aptika' => 'Helpdesk Bidang Aptika - DKISP Kaltara',
            default  => 'Helpdesk Bidang Persandian - DKISP Kaltara',
        };
    }

    public function sendMessage(string $phoneNumber, string $message): bool
    {
        if (empty($this->token)) {
            Log::warning("FonnteWhatsapp: Token not configured for channel {$this->channel}");
            return false;
        }

        try {
            $response = Http::withOptions(['verify' => false])
                ->withHeaders([
                    'Authorization' => $this->token,
                ])
                ->asForm()
                ->post($this->apiUrl, [
                    'target' => $phoneNumber,
                    'message' => $message,
                    'countryCode' => '62',
                ]);

            if ($response->successful()) {
                $body = $response->json();
                if (isset($body['status']) && $body['status'] === true) {
                    Log::info("FonnteWhatsapp: Message sent to {$phoneNumber}");
                    return true;
                }
                Log::warning('FonnteWhatsapp: API returned failure', ['response' => $body]);
                return false;
            }

            Log::error('FonnteWhatsapp: HTTP error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return false;
        } catch (\Exception $e) {
            Log::error('FonnteWhatsapp: Exception - ' . $e->getMessage());
            return false;
        }
    }

    public function sendStatusNotification(
        string $phoneNumber,
        string $identifier,
        string $serviceTypeName,
        string $newStatus,
        ?string $keteranganAdmin,
        string $identifierLabel = 'No. Tiket'
    ): bool {
        if (empty($phoneNumber)) {
            Log::warning("FonnteWhatsapp: phone empty for {$identifier}");
            return false;
        }

        $statusLabel = $this->getStatusLabel($newStatus);
        $catatan = $keteranganAdmin ?: '-';

        $message = "*{$this->header}*\n\n"
            . "Yth. Bapak/Ibu,\n\n"
            . "Permohonan {$serviceTypeName} Anda telah diperbarui.\n\n"
            . "{$identifierLabel}: {$identifier}\n"
            . "Layanan: {$serviceTypeName}\n"
            . "Status: *{$statusLabel}*\n"
            . "Catatan: {$catatan}\n\n"
            . "Terima kasih.\n"
            . "Helpdesk DKISP Kaltara";

        return $this->sendMessage($phoneNumber, $message);
    }

    public function sendSubmitNotification(
        string $phoneNumber,
        string $identifier,
        string $serviceTypeName,
        string $identifierLabel = 'No. Tiket'
    ): bool {
        if (empty($phoneNumber)) {
            Log::warning("FonnteWhatsapp: phone empty for {$identifier}");
            return false;
        }

        $message = "*{$this->header}*\n\n"
            . "Yth. Bapak/Ibu,\n\n"
            . "Permohonan {$serviceTypeName} Anda telah kami terima.\n\n"
            . "{$identifierLabel}: {$identifier}\n"
            . "Layanan: {$serviceTypeName}\n"
            . "Status: *Menunggu*\n\n"
            . "Kami akan memproses permohonan Anda dan mengirim kabar saat status berubah.\n\n"
            . "Terima kasih.\n"
            . "Helpdesk DKISP Kaltara";

        return $this->sendMessage($phoneNumber, $message);
    }

    public function sendTteStatusNotification(
        Model $ticket,
        string $serviceTypeName,
        string $newStatus,
        ?string $keteranganAdmin
    ): bool {
        return $this->sendStatusNotification(
            $ticket->no_hp ?? '',
            $ticket->ticket_no ?? '',
            $serviceTypeName,
            $newStatus,
            $keteranganAdmin,
            'No. Tiket'
        );
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
}
