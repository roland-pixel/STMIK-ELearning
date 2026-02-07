<?php

namespace App\Observers;

use App\Models\Dosen;
use App\Services\ActivityLogger;

class DosenObserver
{
     public function created(Dosen $dosen): void
    {
        ActivityLogger::log('created', $dosen, null, $dosen->toArray());
    }

    public function updated(Dosen $dosen): void
    {
        $before = $dosen->getOriginal();
        $after  = $dosen->getAttributes();

        ActivityLogger::log('updated', $dosen, $before, $after);
    }

    public function deleted(Dosen $dosen): void
    {
        ActivityLogger::log('deleted', $dosen, $dosen->toArray(), null);
    }
}
