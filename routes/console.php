<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Jalankan pencatatan kutipan mingguan bawaan Laravel
Schedule::command('quote:weekly')->weekly();

/**
 * Scheduler Sistem Akademik
 * Mengecek kuis online yang mangkir setiap jam (hourly)
 */
// Schedule::command('nilai:singkron-mangkir')->hourly();
Schedule::command('nilai:singkron-mangkir')->everyTwoMinutes();