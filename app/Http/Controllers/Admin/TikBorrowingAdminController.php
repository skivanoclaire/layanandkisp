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
}
