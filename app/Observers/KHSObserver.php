<?php

namespace App\Observers;

use Illuminate\Support\Facades\Cache;

class KHSObserver
{
    private function clearKHSCache($model)
    {
        // Pastikan minimal ada properti mahasiswa_id sebelum memproses
        if (isset($model->mahasiswa_id)) {

            // 1. Hapus cache KHS HANYA jika model memiliki semester_id (seperti RekapNilai)
            if (isset($model->semester_id)) {
                Cache::forget("khs_data_{$model->mahasiswa_id}_{$model->semester_id}");
            }

            // 2. Hapus cache Transkrip (Pasti punya mahasiswa_id, baik RekapNilai maupun Bimbingan)
            Cache::forget("transkrip_mahasiswa_{$model->mahasiswa_id}");

            $redis = \Illuminate\Support\Facades\Redis::connection('cache');

            // Jalankan perintah keys seperti biasa
            $keys = $redis->keys('*cumlaude_list_*');

            foreach ($keys as $key) {
                // Cari posisi kata 'cumlaude_list_' agar pemotongan prefix aman
                $pos = strpos($key, 'cumlaude_list_');
                if ($pos !== false) {
                    $cleanKey = substr($key, $pos);
                    \Illuminate\Support\Facades\Cache::forget($cleanKey);
                }
            }

            if (isset($model->kelas_id)) {
                Cache::forget("detail_kelas_{$model->kelas_id}");
            }
        }
    }

    public function created($model)
    {
        $this->clearKHSCache($model);
    }
    public function updated($model)
    {
        $this->clearKHSCache($model);
    }
    public function deleted($model)
    {
        $this->clearKHSCache($model);
    }
}
