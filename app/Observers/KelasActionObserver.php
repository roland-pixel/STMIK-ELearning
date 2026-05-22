<?php

namespace App\Observers;

use Illuminate\Support\Facades\Cache;

class KelasActionObserver
{
    private function clearKelasCache($model)
    {
        // Jika model memiliki atribut kelas_id, hapus cache detail kelas tersebut
        if (isset($model->kelas_id)) {
            Cache::forget("kelas_detail_{$model->kelas_id}");
        }
    }

    public function created($model): void
    {
        $this->clearKelasCache($model);
    }

    public function updated($model): void
    {
        $this->clearKelasCache($model);
    }

    public function deleted($model): void
    {
        $this->clearKelasCache($model);
    }
}
