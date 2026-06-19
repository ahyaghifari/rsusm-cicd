<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Generate JadwalHarian dari JadwalPraktek setiap hari pukul 00:05
// Skip jika JadwalHarian untuk (tanggal, poliklinik_id) sudah ada
Schedule::command('jadwal:generate-harian')
    ->dailyAt('00:05')
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/jadwal-harian-cron.log'));

// Sync ketersediaan kamar rawat inap dari sistem Ranap setiap 30 detik
Schedule::command('rawat-inap:sync-ketersediaan')
    ->everyThirtySeconds()
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/rawat-inap-ketersediaan-cron.log'));
