<?php

namespace App\Observers;

use App\Models\Semester;
use App\Services\ActivityLogger;

class SemesterObserver
{
    public function created(Semester $semester): void
    {
        ActivityLogger::log('created', $semester, null, $semester->toArray());
    }

    public function updated(Semester $semester): void
    {
        $before = $semester->getOriginal();
        $after  = $semester->getAttributes();

        ActivityLogger::log('updated', $semester, $before, $after);
    }

    public function deleted(Semester $semester): void
    {
        ActivityLogger::log('deleted', $semester, $semester->toArray(), null);
    }
}
