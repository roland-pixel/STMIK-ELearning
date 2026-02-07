<?php

namespace App\Observers;

use App\Models\Jurusan;
use App\Services\ActivityLogger;

class JurusanObserver
{
    public function created(Jurusan $jurusan): void
    {
        ActivityLogger::log('created', $jurusan, null, $jurusan->toArray());
    }

    public function updated(Jurusan $jurusan): void
    {
        $before = $jurusan->getOriginal();
        $after  = $jurusan->getAttributes();

        ActivityLogger::log('updated', $jurusan, $before, $after);
    }

    public function deleted(Jurusan $jurusan): void
    {
        ActivityLogger::log('deleted', $jurusan, $jurusan->toArray(), null);
    }
}
