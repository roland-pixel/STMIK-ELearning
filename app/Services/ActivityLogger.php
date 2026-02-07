<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityLogger
{
    public static function log(string $action, Model $model, ?array $before = null, ?array $after = null): void
    {
        ActivityLog::create([
            'user_id'      => Auth::id(),
            'action'       => $action,
            'subject_type' => get_class($model),
            'subject_id'   => $model->getKey(),
            'before'       => $before,
            'after'        => $after,
            'ip_address'   => Request::ip(),
            'user_agent'   => Request::userAgent(),
        ]);
    }
}
