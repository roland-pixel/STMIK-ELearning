<?php

namespace App\Observers;

use App\Models\Bimbingan;
use App\Services\ActivityLogger;

class BimbinganObserver
{
    public function created(Bimbingan $bimbingan): void
    {
        ActivityLogger::log('created', $bimbingan, null, $bimbingan->toArray());
    }

    public function updated(Bimbingan $bimbingan): void
    {
        $before = $bimbingan->getOriginal();
        $after  = $bimbingan->getAttributes();

        ActivityLogger::log('updated', $bimbingan, $before, $after);
    }

    public function deleted(Bimbingan $bimbingan): void
    {
        ActivityLogger::log('deleted', $bimbingan, $bimbingan->toArray(), null);
    }
}
