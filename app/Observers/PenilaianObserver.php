<?php

namespace App\Observers;

use App\Models\Penilaian;
use Illuminate\Support\Facades\Cache;

class PenilaianObserver
{
    private function clearKelasCache(Penilaian $penilaian): void
    {
        Cache::forget("kelas:global_detail:{$penilaian->kelas_id}");
    }

    public function saved(Penilaian $penilaian): void
    {
        $this->clearKelasCache($penilaian);
    }

    public function deleted(Penilaian $penilaian): void
    {
        $this->clearKelasCache($penilaian);
    }
}
