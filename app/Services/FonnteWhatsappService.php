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

    public function sendAdminNewRequestAlert(
        string $ticketNo,
        string $serviceTypeName,
        string $nama,
        string $nip
    ): bool {
        $adminPhone = (string) config("services.fonnte.admin_phones.{$this->channel}", '');

        if (empty($adminPhone)) {
            Log::warning("FonnteWhatsapp: Admin phone not configured for channel {$this->channel}");
            return false;
        }

        $message = "*[Permohonan Baru] {$serviceTypeName}*\n\n"
            . "No. Tiket: {$ticketNo}\n"
            . "Nama: {$nama}\n"
            . "NIP: {$nip}\n\n"
            . "Silakan segera tinjau dan proses permohonan ini.\n"
            . "Helpdesk DKISP Kaltara";

        return $this->sendMessage($adminPhone, $message);
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

    public function sendAccountVerifiedNotification(
        string $phoneNumber,
        string $nama,
        string $email
    ): bool {
        if (empty($phoneNumber)) {
            Log::warning('FonnteWhatsapp: sendAccountVerifiedNotification — phone kosong', compact('email'));
            return false;
        }

        $message = "*{$this->header}*\n\n"
            . "Yth. Bapak/Ibu *{$nama}*,\n\n"
            . "Akun Anda pada *Portal Layanan TIK DKISP Provinsi Kaltara* telah berhasil *diverifikasi* oleh admin.\n\n"
            . "Email: {$email}\n"
            . "Status: *Terverifikasi* ✓\n\n"
            . "Silakan login untuk mulai menyampaikan permohonan layanan digital:\n"
            . "https://layanan.diskominfo.kaltaraprov.go.id/login\n\n"
            . "Terima kasih.\n"
            . "Helpdesk DKISP Kaltara";

        return $this->sendMessage($phoneNumber, $message);
    }

    /**
     * Beri tahu pemohon vidcon bahwa permohonannya telah disetujui beserta
     * link meeting yang dapat digunakan.
     */
    public function sendVidconApprovedNotification(
        string $phoneNumber,
        string $ticketNo,
        string $judulKegiatan,
        string $linkMeeting,
        ?string $meetingId = null,
        ?string $meetingPassword = null,
        ?string $informasiTambahan = null
    ): bool {
        if (empty($phoneNumber)) {
            Log::warning("FonnteWhatsapp: phone empty for vidcon approve {$ticketNo}");
            return false;
        }

        $message = "*{$this->header}*\n\n"
            . "Yth. Bapak/Ibu,\n\n"
            . "Permohonan Video Conference Anda telah *disetujui*. Berikut informasi meeting Anda.\n\n"
            . "No. Tiket: {$ticketNo}\n"
            . "Kegiatan: {$judulKegiatan}\n"
            . "Status: *Selesai*\n\n"
            . "*Informasi Meeting:*\n"
            . "Link: {$linkMeeting}\n";

        if (!empty($meetingId)) {
            $message .= "Meeting ID: {$meetingId}\n";
        }
        if (!empty($meetingPassword)) {
            $message .= "Passcode: {$meetingPassword}\n";
        }
        if (!empty($informasiTambahan)) {
            $message .= "Informasi Tambahan: {$informasiTambahan}\n";
        }

        $message .= "\nTerima kasih.\n"
            . "Helpdesk DKISP Kaltara";

        return $this->sendMessage($phoneNumber, $message);
    }

    /**
     * Beri tahu pemohon vidcon bahwa informasi meeting (mis. link) telah direvisi
     * setelah permohonan disetujui/selesai.
     */
    public function sendVidconLinkRevisedNotification(
        string $phoneNumber,
        string $ticketNo,
        string $judulKegiatan,
        string $linkMeeting,
        ?string $meetingId = null,
        ?string $meetingPassword = null,
        ?string $informasiTambahan = null
    ): bool {
        if (empty($phoneNumber)) {
            Log::warning("FonnteWhatsapp: phone empty for vidcon revise {$ticketNo}");
            return false;
        }

        $message = "*{$this->header}*\n\n"
            . "Yth. Bapak/Ibu,\n\n"
            . "Terdapat *perubahan informasi meeting* untuk permohonan Video Conference Anda. Mohon gunakan informasi terbaru di bawah ini.\n\n"
            . "No. Tiket: {$ticketNo}\n"
            . "Kegiatan: {$judulKegiatan}\n\n"
            . "*Informasi Meeting Terbaru:*\n"
            . "Link: {$linkMeeting}\n";

        if (!empty($meetingId)) {
            $message .= "Meeting ID: {$meetingId}\n";
        }
        if (!empty($meetingPassword)) {
            $message .= "Passcode: {$meetingPassword}\n";
        }
        if (!empty($informasiTambahan)) {
            $message .= "Informasi Tambahan: {$informasiTambahan}\n";
        }

        $message .= "\n_Informasi sebelumnya tidak berlaku lagi. Mohon gunakan link terbaru di atas._\n\n"
            . "Terima kasih.\n"
            . "Helpdesk DKISP Kaltara";

        return $this->sendMessage($phoneNumber, $message);
    }

    /**
     * Beri tahu pemohon rekomendasi aplikasi bahwa usulannya perlu direvisi
     * dan meminta agar segera memperbarui usulan.
     */
    public function sendRevisiRekomendasiNotification(
        string $phoneNumber,
        string $ticketNumber,
        string $namaAplikasi,
        ?string $catatanRevisi = null,
        ?string $link = null
    ): bool {
        if (empty($phoneNumber)) {
            Log::warning("FonnteWhatsapp: phone empty for revisi rekomendasi {$ticketNumber}");
            return false;
        }

        $catatan = $catatanRevisi ?: '-';

        $message = "*{$this->header}*\n\n"
            . "Yth. Bapak/Ibu,\n\n"
            . "Usulan Rekomendasi Aplikasi Anda memerlukan *perbaikan (revisi)*. "
            . "Mohon segera memperbarui usulan Anda agar dapat diproses lebih lanjut.\n\n"
            . "No. Tiket: {$ticketNumber}\n"
            . "Nama Aplikasi: {$namaAplikasi}\n"
            . "Status: *Perlu Revisi*\n"
            . "Catatan Revisi: {$catatan}\n";

        if (!empty($link)) {
            $message .= "\nSilakan perbarui usulan Anda melalui tautan berikut:\n{$link}\n";
        }

        $message .= "\nTerima kasih.\n"
            . "Helpdesk DKISP Kaltara";

        return $this->sendMessage($phoneNumber, $message);
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
