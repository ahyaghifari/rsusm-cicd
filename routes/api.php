<?php

use App\Http\Controllers\Api\DokterController;
use App\Http\Controllers\Api\JadwalHarianController;
use App\Http\Controllers\Api\KetersediaanBedController;
use App\Http\Controllers\Api\PosterController;
use App\Http\Controllers\Api\PromoController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:mobile-api')->group(function () {
    Route::get('/promo', [PromoController::class, 'index']);
});

Route::middleware(['throttle:mobile-api', 'dokter.basic'])->group(function () {
    Route::get('/dokter', [DokterController::class, 'index']);
});

Route::middleware('throttle:public-api')->group(function () {
    Route::get('/{rs}/jadwal-harian', [JadwalHarianController::class, 'index']);
    Route::get('/{rs}/jadwal-harian-executive', [JadwalHarianController::class, 'executive']);
    Route::get('/{rs}/jadwal-harian-dokter', [JadwalHarianController::class, 'perDokter']);    Route::get('/{rs}/jadwal-bulanan',[JadwalHarianController::class, 'bulanan']);
    Route::get('/{rs}/jadwal-bulanan-executive', [JadwalHarianController::class, 'bulananExecutive']);
    Route::get('/{rs}/ketersediaan-bed', [KetersediaanBedController::class, 'index']);
    Route::get('/{rs}/poster-jadwal-harian',[PosterController::class, 'jadwalHarian']);
    Route::get('/{rs}/poster-jadwal-harian-executive', [PosterController::class, 'jadwalHarianExecutive']);
});