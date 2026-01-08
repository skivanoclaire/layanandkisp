<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RekomendasiAplikasiForm;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Dompdf\Dompdf;
use Dompdf\Options;

class RekomendasiAplikasiController extends Controller
{
    /**
     * Display a listing of all recommendation forms from all users
     */
    public function index(Request $request)
    {
        $query = RekomendasiAplikasiForm::with(['user', 'approvedBy', 'rejectedBy', 'revisionRequestedBy']);

        // Filter by status if provided
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                  ->orWhere('judul_aplikasi', 'like', "%{$search}%")
                  ->orWhere('letter_number', 'like', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $forms = $query->latest()->paginate(20)->appends($request->except('page'));

        return view('admin.rekomendasi.index', compact('forms'));
    }

    /**
     * Display the specified recommendation form for admin review
     */
    public function show($id)
    {
        $form = RekomendasiAplikasiForm::with(['user', 'risikoItems', 'approvedBy', 'rejectedBy', 'revisionRequestedBy'])->findOrFail($id);

        return view('admin.rekomendasi.show', compact('form'));
    }

    /**
     * Approve the recommendation form
     */
    public function approve(Request $request, $id)
    {
        $request->validate([
            'admin_feedback' => 'nullable|string|max:1000',
        ]);

        $form = RekomendasiAplikasiForm::findOrFail($id);

        $form->update([
            'status' => 'disetujui',
            'admin_feedback' => $request->admin_feedback,
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return redirect()
            ->route('admin.rekomendasi.show', $id)
            ->with('success', 'Usulan rekomendasi aplikasi berhasil disetujui.');
    }

    /**
     * Reject the recommendation form
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'admin_feedback' => 'required|string|max:1000',
        ]);

        $form = RekomendasiAplikasiForm::findOrFail($id);

        $form->update([
            'status' => 'ditolak',
            'admin_feedback' => $request->admin_feedback,
            'rejected_by' => Auth::id(),
            'rejected_at' => now(),
        ]);

        return redirect()
            ->route('admin.rekomendasi.show', $id)
            ->with('success', 'Usulan rekomendasi aplikasi ditolak dengan feedback.');
    }

    /**
     * Request revision from user
     */
    public function requestRevision(Request $request, $id)
    {
        $request->validate([
            'revision_notes' => 'required|string|max:2000',
        ]);

        $form = RekomendasiAplikasiForm::findOrFail($id);

        $form->update([
            'status' => 'perlu_revisi',
            'revision_notes' => $request->revision_notes,
            'revision_requested_by' => Auth::id(),
            'revision_requested_at' => now(),
        ]);

        return redirect()
            ->route('admin.rekomendasi.show', $id)
            ->with('success', 'Permintaan revisi berhasil dikirim ke user.');
    }

    /**
     * Manually change status (only between 'diajukan' and 'diproses')
     */
    public function changeStatus(Request $request, $id)
    {
        $request->validate([
            'new_status' => 'required|in:diajukan,diproses',
            'status_note' => 'nullable|string|max:500',
        ]);

        $form = RekomendasiAplikasiForm::findOrFail($id);

        // Only allow changing between 'diajukan' and 'diproses'
        if (!in_array($form->status, ['diajukan', 'diproses'])) {
            return redirect()
                ->route('admin.rekomendasi.show', $id)
                ->with('error', 'Status tidak dapat diubah. Gunakan tombol Approve/Reject/Minta Revisi untuk mengubah status lainnya.');
        }

        $oldStatus = $form->status;
        $newStatus = $request->new_status;

        // Prevent changing to same status
        if ($oldStatus === $newStatus) {
            return redirect()
                ->route('admin.rekomendasi.show', $id)
                ->with('error', 'Status tidak berubah. Pilih status yang berbeda.');
        }

        // Build feedback message
        $statusNote = $request->status_note
            ? "Catatan: " . $request->status_note
            : "Status diubah oleh admin dari '{$oldStatus}' ke '{$newStatus}'.";

        $form->update([
            'status' => $newStatus,
            'admin_feedback' => $statusNote,
        ]);

        return redirect()
            ->route('admin.rekomendasi.show', $id)
            ->with('success', "Status berhasil diubah dari '{$oldStatus}' ke '{$newStatus}'.");
    }

    /**
     * Generate PDF recommendation letter
     */
    public function generatePDF($id)
    {
        $form = RekomendasiAplikasiForm::with(['user', 'risikoItems', 'approvedBy', 'pemilikProsesBisnis'])->findOrFail($id);

        if ($form->status !== 'disetujui') {
            return back()->with('error', 'Hanya usulan yang disetujui yang dapat dicetak.');
        }

        // Generate letter number if not exists
        if (!$form->letter_number) {
            // Use approved_at date for letter number
            $approvalDate = $form->approved_at ?? now();
            $year = $approvalDate->format('Y');
            $month = $approvalDate->format('m');
            $monthRoman = ['', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
            $count = RekomendasiAplikasiForm::where('status', 'disetujui')
                ->whereYear('approved_at', $year)
                ->whereMonth('approved_at', $month)
                ->count();

            $letterNumber = sprintf('%04d/APTIKA/REKOMENDASI-APLIKASI/%s/%s', $count + 1, $monthRoman[(int)$month], $year);
            $form->update(['letter_number' => $letterNumber]);
        }

        // Generate verification code if not exists
        if (!$form->verification_code) {
            $verificationCode = 'VRF-' . strtoupper(Str::random(32));
            $form->update(['verification_code' => $verificationCode]);
        }

        // Generate QR Code
        $verificationUrl = url('/verify/' . $form->verification_code);
        $qrCode = base64_encode(QrCode::format('png')->size(200)->generate($verificationUrl));

        // Encode logo to base64
        $logoPath = public_path('logokaltarafix.png');
        $logoBase64 = '';
        if (file_exists($logoPath)) {
            $logoBase64 = base64_encode(file_get_contents($logoPath));
        }

        // Render the view to HTML
        $html = view('admin.rekomendasi.pdf', [
            'form' => $form,
            'letterNumber' => $form->letter_number,
            'date' => $form->approved_at->format('d F Y'),
            'qrCode' => $qrCode,
            'logoBase64' => $logoBase64,
            'nipKepalaDinas' => '197312311993021001',
            'signerName' => 'Dr. H. ISKANDAR S.IP, M.Si',
            'signerRank' => 'Pembina Utama Muda / IV c',
        ])->render();

        // Configure Dompdf
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Save PDF to storage
        $pdfPath = 'rekomendasi/' . $form->ticket_number . '.pdf';
        $fullPath = storage_path('app/public/' . $pdfPath);

        // Create directory if not exists with proper permissions
        $dir = dirname($fullPath);
        if (!file_exists($dir)) {
            if (!mkdir($dir, 0775, true) && !is_dir($dir)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $dir));
            }
        }

        // Save PDF with error handling
        $pdfOutput = $dompdf->output();
        if (file_put_contents($fullPath, $pdfOutput) === false) {
            throw new \RuntimeException('Failed to write PDF file. Check storage permissions.');
        }

        $form->update(['pdf_path' => $pdfPath]);

        // Download PDF
        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="Surat_Rekomendasi_' . $form->ticket_number . '.pdf"',
        ]);
    }
}
