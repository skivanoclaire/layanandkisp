<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with('user')->orderByDesc('created_at');

        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($w) use ($q) {
                $w->where('email', 'like', "%{$q}%")
                  ->orWhere('ip_address', 'like', "%{$q}%")
                  ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$q}%"));
            });
        }

        $logs = $query->paginate(25)->appends($request->except('page'));
        $eventLabels = AuditLog::eventLabels();

        return view('admin.audit-logs.index', compact('logs', 'eventLabels'));
    }

    public function show(AuditLog $auditLog)
    {
        $auditLog->load('user');
        $eventLabels = AuditLog::eventLabels();
        return view('admin.audit-logs.show', compact('auditLog', 'eventLabels'));
    }
}
