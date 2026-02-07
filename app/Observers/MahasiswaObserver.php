<?php

namespace App\Observers;

use App\Models\Mahasiswa;
use App\Services\ActivityLogger;

class MahasiswaObserver
{
    public function created(Mahasiswa $mahasiswa): void
    {
        ActivityLogger::log('created', $mahasiswa, null, $mahasiswa->toArray());
    }

    public function updated(Mahasiswa $mahasiswa): void
    {
        $before = $mahasiswa->getOriginal();
        $after  = $mahasiswa->getAttributes();

        ActivityLogger::log('updated', $mahasiswa, $before, $after);
    }

    public function deleted(Mahasiswa $mahasiswa): void
    {
        ActivityLogger::log('deleted', $mahasiswa, $mahasiswa->toArray(), null);
    }
}
