<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Generate JadwalHarian dari JadwalPraktek setiap hari pukul 03:00
// Skip jika JadwalHarian untuk (tanggal, poliklinik_id) sudah ada
Schedule::command('jadwal:generate-harian')
    ->dailyAt('03:00')
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/jadwal-harian-cron.log'));
