<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $q      = $request->get('q');
        $action = $request->get('action');
        $type   = $request->get('type');

        $logs = ActivityLog::query()
            ->with('user')
            ->when($q, function ($query) use ($q) {
                $query->where(function ($qq) use ($q) {
                    $qq->where('subject_type', 'like', "%{$q}%")
                        ->orWhere('subject_id', 'like', "%{$q}%")
                        ->orWhereHas('user', function ($u) use ($q) {
                            $u->where('nama_lengkap', 'like', "%{$q}%")
                                ->orWhere('email', 'like', "%{$q}%");
                        });
                });
            })
            ->when($action, fn($query) => $query->where('action', $action))
            ->when($type, fn($query) => $query->where('subject_type', $type))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        // untuk dropdown filter type yang ada di database
        $types = ActivityLog::query()
            ->select('subject_type')
            ->distinct()
            ->orderBy('subject_type')
            ->pluck('subject_type');

        return view('admin.activity_logs.index', compact('logs', 'types', 'q', 'action', 'type'));
    }
}
