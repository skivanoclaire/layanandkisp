<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RekomendasiAplikasiForm;
use Illuminate\Http\Request;

class RekomendasiAplikasiAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = RekomendasiAplikasiForm::with(['user', 'pemilikProsesBisnis', 'risikoItems'])
            ->latest();

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search by title or user
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('judul_aplikasi', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $forms = $query->paginate(15);

        return view('admin.rekomendasi.index', compact('forms'));
    }

    public function show($id)
    {
        $form = RekomendasiAplikasiForm::with(['user', 'pemilikProsesBisnis', 'risikoItems'])
            ->findOrFail($id);

        return view('admin.rekomendasi.show', compact('form'));
    }

    public function approve(Request $request, $id)
    {
        $form = RekomendasiAplikasiForm::findOrFail($id);

        $request->validate([
            'admin_feedback' => 'nullable|string',
        ]);

        $form->update([
            'status' => 'disetujui',
            'admin_feedback' => $request->admin_feedback,
            'processed_by' => auth()->id(),
            'processed_at' => now(),
        ]);

        return redirect()->route('admin.rekomendasi.show', $id)
            ->with('success', 'Usulan berhasil disetujui.');
    }

    public function reject(Request $request, $id)
    {
        $form = RekomendasiAplikasiForm::findOrFail($id);

        $request->validate([
            'admin_feedback' => 'required|string',
        ]);

        $form->update([
            'status' => 'ditolak',
            'admin_feedback' => $request->admin_feedback,
            'processed_by' => auth()->id(),
            'processed_at' => now(),
        ]);

        return redirect()->route('admin.rekomendasi.show', $id)
            ->with('success', 'Usulan telah ditolak.');
    }

    public function requestRevision(Request $request, $id)
    {
        $form = RekomendasiAplikasiForm::findOrFail($id);

        $request->validate([
            'revision_notes' => 'required|string',
        ]);

        $form->update([
            'status' => 'perlu_revisi',
            'revision_notes' => $request->revision_notes,
            'processed_by' => auth()->id(),
        ]);

        return redirect()->route('admin.rekomendasi.show', $id)
            ->with('success', 'Permintaan revisi telah dikirim ke pengguna.');
    }

    public function process($id)
    {
        $form = RekomendasiAplikasiForm::findOrFail($id);

        $form->update([
            'status' => 'diproses',
            'processed_by' => auth()->id(),
        ]);

        return redirect()->route('admin.rekomendasi.show', $id)
            ->with('success', 'Usulan sedang diproses.');
    }
}
