<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RekomendasiAplikasiForm;

class VerificationController extends Controller
{
    /**
     * Verify document by verification code (Public route)
     */
    public function verify($code)
    {
        $document = RekomendasiAplikasiForm::where('verification_code', $code)
            ->with(['user', 'approvedBy'])
            ->first();

        if (!$document) {
            return view('public.verify-document', [
                'found' => false,
                'message' => 'Kode verifikasi tidak valid atau dokumen tidak ditemukan.'
            ]);
        }

        if ($document->status !== 'disetujui') {
            return view('public.verify-document', [
                'found' => false,
                'message' => 'Dokumen ini belum disetujui atau tidak valid.'
            ]);
        }

        return view('public.verify-document', [
            'found' => true,
            'document' => $document
        ]);
    }
}
