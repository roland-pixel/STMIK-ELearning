<?php

namespace App\Observers;

use App\Models\Kelas;
use App\Services\ActivityLogger;

class KelasObserver
{
    public function created(Kelas $kelas): void
    {
        ActivityLogger::log('created', $kelas, null, $kelas->toArray());
    }

    public function updated(Kelas $kelas): void
    {
        $before = $kelas->getOriginal();
        $after  = $kelas->getAttributes();

        ActivityLogger::log('updated', $kelas, $before, $after);
    }

    public function deleted(Kelas $kelas): void
    {
        ActivityLogger::log('deleted', $kelas, $kelas->toArray(), null);
    }
}
