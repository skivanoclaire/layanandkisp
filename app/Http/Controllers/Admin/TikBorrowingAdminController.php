<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TikBorrowing;
use App\Models\User;
use Illuminate\Http\Request;

class TikBorrowingAdminController extends Controller
{
    public function index(Request $r)
    {
        $q = TikBorrowing::with(['operator','items.asset']);

        if ($r->filled('operator')) $q->where('operator_id', $r->operator);
        if ($r->filled('status'))   $q->where('status', $r->status);
        if ($r->filled('date_from')) $q->whereDate('started_at','>=',$r->date_from);
        if ($r->filled('date_to'))   $q->whereDate('started_at','<=',$r->date_to);

        $items = $q->orderByDesc('started_at')->paginate(25)->withQueryString();
        $operators = User::where('role','operator-vidcon')->orderBy('name')->get(['id','name']);

        return view('admin.tik.borrowings.index', compact('items','operators'));
    }

    public function show(TikBorrowing $borrowing)
    {
        $borrowing->load(['operator','items.asset.category','photos']);
        return view('admin.tik.borrowings.show', compact('borrowing'));
    }

    public function forceClose(Request $request, TikBorrowing $borrowing)
    {
        // Validasi: hanya bisa force close jika status ongoing
        if ($borrowing->status !== 'ongoing') {
            return redirect()->back()->with('error', 'Peminjaman ini sudah selesai atau tidak dalam status sedang dipinjam.');
        }

        // Validasi input alasan
        $request->validate([
            'closed_reason' => 'required|string|min:10|max:1000',
        ], [
            'closed_reason.required' => 'Alasan penutupan paksa harus diisi.',
            'closed_reason.min' => 'Alasan minimal 10 karakter.',
            'closed_reason.max' => 'Alasan maksimal 1000 karakter.',
        ]);

        // Update borrowing
        $borrowing->update([
            'status' => 'returned',
            'finished_at' => now(),
            'closed_by' => auth()->id(),
            'closed_reason' => $request->closed_reason,
            'closed_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Peminjaman berhasil ditutup secara paksa. Stok aset telah dikembalikan ke inventaris.');
    }
}
