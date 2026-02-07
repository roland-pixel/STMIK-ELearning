<?php

namespace App\Observers;

use App\Models\MataKuliah;
use App\Services\ActivityLogger;

class MataKuliahObserver
{
     public function created(MataKuliah $mataKuliah): void
    {
        ActivityLogger::log('created', $mataKuliah, null, $mataKuliah->toArray());
    }

    public function updated(MataKuliah $mataKuliah): void
    {
        $before = $mataKuliah->getOriginal();
        $after  = $mataKuliah->getAttributes();

        ActivityLogger::log('updated', $mataKuliah, $before, $after);
    }

    public function deleted(MataKuliah $mataKuliah): void
    {
        ActivityLogger::log('deleted', $mataKuliah, $mataKuliah->toArray(), null);
    }
}
