<?php

namespace App\Console\Commands;

use App\Models\DtsenAccessToken;
use App\Models\DtsenAccountRequest;
use App\Models\DtsenDataRequest;
use App\Models\DtsenDestructionReport;
use App\Models\DtsenIncidentReport;
use App\Models\DtsenRequestLog;
use App\Models\DtsenUtilizationReport;
use Illuminate\Console\Command;

/**
 * Pemeriksaan harian siklus hidup layanan DTSEN.
 *
 * Menegakkan tenggat yang diatur Juknis tanpa campur tangan operator:
 * - akun tidak dipakai 30 hari kalender dinonaktifkan (dengan peringatan lebih dulu)
 * - token/tautan unduh yang mendekati & melewati masa aktif
 * - pengingat laporan pemanfaatan (min. 1x per 6 bulan)
 * - pengingat penyampaian berita acara pemusnahan (maks. 14 hari kalender)
 * - eskalasi laporan insiden yang melewati batas 3x24 jam hari kerja
 *
 * Pemberitahuan dicatat sebagai entri audit trail sehingga tampil pada portal
 * (riwayat permohonan & dashboard), sesuai kanal notifikasi yang tersedia saat ini.
 */
class DtsenLifecycleCheck extends Command
{
    protected $signature = 'dtsen:lifecycle-check {--dry-run : Tampilkan tindakan tanpa menyimpan perubahan}';

    protected $description = 'Periksa masa aktif akun, token, dan tenggat pelaporan DTSEN';

    private bool $dryRun = false;

    public function handle(): int
    {
        $this->dryRun = (bool) $this->option('dry-run');

        if ($this->dryRun) {
            $this->warn('Mode dry-run: tidak ada perubahan yang disimpan.');
        }

        $this->checkAccounts();
        $this->checkTokens();
        $this->checkUtilizationReports();
        $this->checkDestructionReports();
        $this->checkIncidents();

        $this->info('Pemeriksaan siklus hidup DTSEN selesai.');

        return self::SUCCESS;
    }

    /** Peringatan & penonaktifan akun yang menganggur 30 hari kalender. */
    private function checkAccounts(): void
    {
        $accounts = DtsenAccountRequest::activeAccount()->get();
        $warned = 0;
        $deactivated = 0;

        foreach ($accounts as $account) {
            $daysLeft = $account->idleDaysLeft();
            if ($daysLeft === null) {
                continue;
            }

            if ($daysLeft <= 0) {
                if (! $this->dryRun) {
                    $account->is_active = false;
                    $account->deactivated_at = now();
                    $account->save();

                    DtsenRequestLog::record(
                        DtsenRequestLog::TYPE_AKUN,
                        $account->id,
                        'akun_dinonaktifkan_otomatis',
                        'Akun dinonaktifkan otomatis karena tidak digunakan selama '
                            . DtsenAccountRequest::IDLE_DAYS . ' hari kalender. Ajukan aktivasi ulang bila masih diperlukan.'
                    );
                }
                $deactivated++;
                continue;
            }

            $needsWarning = $daysLeft <= DtsenAccountRequest::WARN_BEFORE_DAYS
                && $account->deactivation_warned_at === null;

            if ($needsWarning) {
                if (! $this->dryRun) {
                    $account->deactivation_warned_at = now();
                    $account->saveQuietly();

                    DtsenRequestLog::record(
                        DtsenRequestLog::TYPE_AKUN,
                        $account->id,
                        'peringatan_nonaktif',
                        "Akun akan dinonaktifkan dalam {$daysLeft} hari bila tidak digunakan."
                    );
                }
                $warned++;
            }
        }

        $this->line("Akun: {$warned} diperingatkan, {$deactivated} dinonaktifkan.");
    }

    /** Peringatan & penandaan kedaluwarsa token/tautan unduh. */
    private function checkTokens(): void
    {
        $warned = 0;
        $expired = 0;

        $soon = DtsenAccessToken::with('dataRequest')
            ->whereNull('revoked_at')
            ->whereNull('expiry_warned_at')
            ->whereBetween('expires_at', [now(), now()->addDays(DtsenAccessToken::WARN_BEFORE_DAYS)])
            ->get();

        foreach ($soon as $token) {
            if (! $this->dryRun) {
                $token->expiry_warned_at = now();
                $token->save();

                DtsenRequestLog::record(
                    DtsenRequestLog::TYPE_PERMOHONAN,
                    $token->dtsen_data_request_id,
                    'token_segera_kedaluwarsa',
                    'Masa aktif token berakhir ' . $token->expires_at->format('d/m/Y')
                        . '. Ajukan perpanjangan bila data masih diperlukan.'
                );
            }
            $warned++;
        }

        $lapsed = DtsenDataRequest::where('status', DtsenDataRequest::STATUS_DATA_TERSEDIA)
            ->whereDoesntHave('tokens', function ($q) {
                $q->whereNull('revoked_at')
                    ->where(function ($sub) {
                        $sub->whereNull('expires_at')->orWhere('expires_at', '>', now());
                    });
            })
            ->get();

        foreach ($lapsed as $request) {
            if (! $this->dryRun) {
                $request->status = DtsenDataRequest::STATUS_KEDALUWARSA;
                $request->save();

                DtsenRequestLog::record(
                    DtsenRequestLog::TYPE_PERMOHONAN,
                    $request->id,
                    'akses_kedaluwarsa',
                    'Masa aktif token akses berakhir. Ajukan perpanjangan atau permintaan ulang data.'
                );
            }
            $expired++;
        }

        $this->line("Token: {$warned} peringatan kedaluwarsa, {$expired} permohonan ditandai kedaluwarsa.");
    }

    /** Pengingat laporan pemanfaatan yang jatuh tempo (min. 1x per 6 bulan). */
    private function checkUtilizationReports(): void
    {
        $requests = DtsenDataRequest::with('utilizationReports')
            ->whereIn('status', [
                DtsenDataRequest::STATUS_DATA_TERSEDIA,
                DtsenDataRequest::STATUS_SELESAI,
                DtsenDataRequest::STATUS_KEDALUWARSA,
            ])
            ->whereNotNull('akses_at')
            ->get();

        $due = 0;

        foreach ($requests as $request) {
            $nextDue = DtsenUtilizationReport::nextDueFor($request);
            if (! $nextDue || $nextDue->isFuture()) {
                continue;
            }

            // Cukup satu pengingat per hari agar riwayat tidak dibanjiri entri serupa.
            $alreadyReminded = DtsenRequestLog::where('request_type', DtsenRequestLog::TYPE_PERMOHONAN)
                ->where('request_id', $request->id)
                ->where('action', 'pengingat_pelaporan')
                ->whereDate('created_at', now()->toDateString())
                ->exists();

            if ($alreadyReminded) {
                continue;
            }

            if (! $this->dryRun) {
                DtsenRequestLog::record(
                    DtsenRequestLog::TYPE_PERMOHONAN,
                    $request->id,
                    'pengingat_pelaporan',
                    'Laporan pemanfaatan DTSEN sudah jatuh tempo sejak ' . $nextDue->format('d/m/Y') . '.'
                );
            }
            $due++;
        }

        $this->line("Pelaporan pemanfaatan: {$due} permohonan jatuh tempo.");
    }

    /** Pengingat penyampaian salinan berita acara pemusnahan (maks. 14 hari). */
    private function checkDestructionReports(): void
    {
        $reports = DtsenDestructionReport::where('status', DtsenDestructionReport::STATUS_DRAFT)
            ->whereNotNull('batas_penyampaian')
            ->whereDate('batas_penyampaian', '<=', now()->addDays(3)->toDateString())
            ->get();

        $reminded = 0;

        foreach ($reports as $report) {
            $alreadyReminded = DtsenRequestLog::where('request_type', DtsenRequestLog::TYPE_PEMUSNAHAN)
                ->where('request_id', $report->id)
                ->where('action', 'pengingat_penyampaian')
                ->whereDate('created_at', now()->toDateString())
                ->exists();

            if ($alreadyReminded) {
                continue;
            }

            if (! $this->dryRun) {
                DtsenRequestLog::record(
                    DtsenRequestLog::TYPE_PEMUSNAHAN,
                    $report->id,
                    'pengingat_penyampaian',
                    'Salinan berita acara pemusnahan wajib disampaikan ke DKISP paling lambat '
                        . $report->batas_penyampaian->format('d/m/Y') . '.'
                );
            }
            $reminded++;
        }

        $this->line("Berita acara pemusnahan: {$reminded} pengingat penyampaian.");
    }

    /** Eskalasi laporan insiden yang melewati batas 3x24 jam hari kerja. */
    private function checkIncidents(): void
    {
        $incidents = DtsenIncidentReport::where('status', '!=', DtsenIncidentReport::STATUS_SELESAI)
            ->whereNull('escalated_at')
            ->whereNotNull('batas_pelaporan')
            ->where('batas_pelaporan', '<', now())
            ->get();

        $escalated = 0;

        foreach ($incidents as $incident) {
            if (! $this->dryRun) {
                $incident->escalated_at = now();
                $incident->terlambat = true;
                $incident->save();

                DtsenRequestLog::record(
                    DtsenRequestLog::TYPE_INSIDEN,
                    $incident->id,
                    'eskalasi',
                    'Batas penanganan 3x24 jam hari kerja terlampaui; dieskalasi ke Petugas Pelindung DTSEN.'
                );
            }
            $escalated++;
        }

        $this->line("Insiden: {$escalated} dieskalasi.");
    }
}
