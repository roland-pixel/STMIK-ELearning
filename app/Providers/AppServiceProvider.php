<?php

namespace App\Providers;

use App\Models\AnggotaKelas;
use App\Models\Dosen;
use App\Models\Kelas;
use App\Models\Jurusan;
use App\Models\Semester;
use App\Models\Bimbingan;
use App\Models\Mahasiswa;
use App\Models\MataKuliah;
use App\Models\Materi;
use App\Models\Penilaian;
use App\Observers\AnggotaKelasObserver;
use App\Observers\DosenObserver;
use App\Observers\KelasObserver;
use App\Observers\JurusanObserver;
use App\Observers\SemesterObserver;
use App\Observers\BimbinganObserver;
use App\Observers\MahasiswaObserver;
use App\Observers\MataKuliahObserver;
use App\Observers\MateriObserver;
use App\Observers\PenilaianObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Kelas::observe(KelasObserver::class);
        MataKuliah::observe(MataKuliahObserver::class);
        Semester::observe(SemesterObserver::class);
        Jurusan::observe(JurusanObserver::class);
        Mahasiswa::observe(MahasiswaObserver::class);
        Dosen::observe(DosenObserver::class);
        Bimbingan::observe(BimbinganObserver::class);

        Materi::observe(MateriObserver::class);
        Penilaian::observe(PenilaianObserver::class);
        AnggotaKelas::observe(AnggotaKelasObserver::class);
    }
}
