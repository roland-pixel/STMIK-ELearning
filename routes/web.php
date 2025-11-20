<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UploadController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/uploads', [UploadController::class, 'index'])->name('admin.uploads.index');
    Route::post('/uploads', [UploadController::class, 'store'])->name('admin.uploads.store');
    Route::delete('/uploads/{upload}', [UploadController::class, 'destroy'])->name('admin.uploads.destroy');
});
