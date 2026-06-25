<?php

namespace App\Http\Controllers\Admin\Splp;

use App\Http\Controllers\Controller;
use App\Models\SplpServiceLog;
use Illuminate\Http\Request;

class SplpAuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = SplpServiceLog::with(['service', 'consumer', 'actor'])
            ->orderByDesc('created_at');

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }
        if ($request->filled('splp_service_id')) {
            $query->where('splp_service_id', $request->splp_service_id);
        }
        if ($request->filled('dari_tanggal')) {
            $query->whereDate('created_at', '>=', $request->dari_tanggal);
        }
        if ($request->filled('sampai_tanggal')) {
            $query->whereDate('created_at', '<=', $request->sampai_tanggal);
        }

        $logs = $query->paginate(50)->withQueryString();

        return view('admin.splp.audit-log.index', compact('logs'));
    }
}
