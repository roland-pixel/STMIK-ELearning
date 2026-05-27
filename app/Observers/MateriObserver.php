<?php

namespace App\Observers;

use App\Models\Materi;
use Illuminate\Support\Facades\Cache;

class MateriObserver
{
    private function clearKelasCache(Materi $materi): void
    {
        Cache::forget("kelas:global_detail:{$materi->kelas_id}");
    }

    public function saved(Materi $materi): void
    {
        $this->clearKelasCache($materi);
    }

    public function deleted(Materi $materi): void
    {
        $this->clearKelasCache($materi);
    }
}
