<?php

namespace App\Observers;

use App\Models\AnggotaKelas;
use Illuminate\Support\Facades\Cache;

class AnggotaKelasObserver
{
    private function clearKelasCache(AnggotaKelas $anggotaKelas): void
    {
        Cache::forget("kelas:global_detail:{$anggotaKelas->kelas_id}");
    }

    public function saved(AnggotaKelas $anggotaKelas): void
    {
        $this->clearKelasCache($anggotaKelas);
    }

    public function deleted(AnggotaKelas $anggotaKelas): void
    {
        $this->clearKelasCache($anggotaKelas);
    }
}
