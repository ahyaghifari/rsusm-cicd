<?php

use App\Http\Controllers\Api\JadwalHarianController;
use App\Http\Controllers\Api\JadwalPraktekController;
use App\Http\Controllers\Api\PosterController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:public-api')->group(function () {
    Route::get('/{rs}/jadwal-harian', [JadwalHarianController::class, 'index']);
    Route::get('/{rs}/jadwal-harian-executive', [JadwalHarianController::class, 'executive']);
    Route::get('/{rs}/poster-jadwal-harian', [PosterController::class, 'jadwalHarian']);
});
