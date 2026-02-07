<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\BimbinganController;
use App\Http\Controllers\Admin\DosenController;
use App\Http\Controllers\Admin\JurusanController;
use App\Http\Controllers\Admin\KelasController as AdminKelasController;
use App\Http\Controllers\Admin\MahasiswaController;
use App\Http\Controllers\Admin\MataKuliahController;
use App\Http\Controllers\Admin\ProfileController as AdminProfileController;
use App\Http\Controllers\Admin\SemesterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Dosen\DashboardController as DosenDashboardController;
use App\Http\Controllers\Dosen\KelasDetailController;
use App\Http\Controllers\Dosen\KelasController as DosenKelasController;
use App\Http\Controllers\Dosen\MateriController;
use App\Http\Controllers\Dosen\PenilaianBimbinganController;
use App\Http\Controllers\Dosen\PenilaianOnlineController;
use App\Http\Controllers\Dosen\ProfileController as DosenProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Auth
|--------------------------------------------------------------------------
*/

Route::get('/',  [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Dashboard basic (kalau masih mau pakai view langsung)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::view('/admin/dashboard', 'admin.dashboard.index')->name('admin.dashboard');
    Route::view('/mahasiswa/dashboard', 'mahasiswa.dashboard.index')->name('mahasiswa.dashboard');
});

/*
|--------------------------------------------------------------------------
| Admin
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        // Resources (CRUD)
        Route::resource('jurusans', JurusanController::class)
            ->except(['show']);

        Route::resource('semesters', SemesterController::class)
            ->except(['show']);

        // pakai path "mata-kuliahs" tapi name tetap "mata_kuliahs.*"
        Route::resource('mata-kuliahs', MataKuliahController::class)
            ->parameters(['mata-kuliahs' => 'mata_kuliah'])
            ->names('mata_kuliahs')
            ->except(['show']);

        Route::resource('dosens', DosenController::class)
            ->except(['show']);

        Route::resource('mahasiswas', MahasiswaController::class)
            ->except(['show']);

        Route::resource('kelases', AdminKelasController::class)
            ->except(['show']);

        Route::resource('bimbingans', BimbinganController::class)
            ->except(['show']);

        // Profile
        Route::get('profile', [AdminProfileController::class, 'edit'])->name('profile.edit');
        Route::put('profile', [AdminProfileController::class, 'update'])->name('profile.update');

        // Activity Logs (cukup admin)
        Route::get('activity-logs', [ActivityLogController::class, 'index'])->name('activity_logs.index');
    });

/*
|--------------------------------------------------------------------------
| Dosen
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:dosen'])
    ->prefix('dosen')
    ->name('dosen.')
    ->group(function () {

        Route::get('dashboard', [DosenDashboardController::class, 'index'])
            ->name('dashboard');

        /** ================= Profile ================= */
        Route::prefix('profile')->name('profile.')->group(function () {
            Route::get('/', [DosenProfileController::class, 'edit'])->name('edit');
            Route::post('/', [DosenProfileController::class, 'update'])->name('update');
            Route::post('/password', [DosenProfileController::class, 'updatePassword'])->name('password');
            Route::delete('/avatar', [DosenProfileController::class, 'destroyAvatar'])->name('avatar.destroy');
        });

        /** ================= Penilaian Bimbingan ================= */
        Route::prefix('penilaian-bimbingan')->name('penilaian_bimbingan.')->group(function () {
            Route::get('/', [PenilaianBimbinganController::class, 'index'])->name('index');
            Route::get('/{bimbingan}/edit', [PenilaianBimbinganController::class, 'edit'])->name('edit');
            Route::put('/{bimbingan}', [PenilaianBimbinganController::class, 'update'])->name('update');
            Route::delete('/{bimbingan}', [PenilaianBimbinganController::class, 'destroy'])->name('destroy');
        });

        /** ================= Kelas ================= */
        Route::prefix('kelas/{kelas:uuid}')->name('kelas.')->group(function () {

            Route::get('/', [KelasDetailController::class, 'show'])->name('show');
            Route::patch('/settings', [DosenKelasController::class, 'updateSettings'])
                ->name('settings.update');

            /** ===== Materi ===== */
            Route::resource('materi', MateriController::class)
                ->only(['index', 'create', 'store', 'edit', 'update', 'destroy'])
                ->names('materi');

            /** ===== Penilaian Online ===== */
            Route::prefix('penilaian/online')->name('penilaian.online.')->group(function () {
                Route::get('/', [PenilaianOnlineController::class, 'index'])->name('index');
                Route::get('/create', [PenilaianOnlineController::class, 'create'])->name('create');
                Route::post('/', [PenilaianOnlineController::class, 'store'])->name('store');

                Route::get('/{penilaian}/edit', [PenilaianOnlineController::class, 'edit'])->name('edit');
                Route::put('/{penilaian}', [PenilaianOnlineController::class, 'update'])->name('update');
                Route::delete('/{penilaian}', [PenilaianOnlineController::class, 'destroy'])->name('destroy');
            });
        });
    });
