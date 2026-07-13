<?php

use App\Http\Controllers\Api\JadwalHarianController;
use App\Http\Controllers\Api\JadwalPraktekController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:public-api')->group(function () {
    Route::get('/{rs}/jadwal-harian', [JadwalHarianController::class, 'index']);
    Route::get('/{rs}/jadwal-harian-executive', [JadwalHarianController::class, 'executive']);
});
