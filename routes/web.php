<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('{rumahsakit}')
    // ->middleware('hospital')
    ->group(function () {

        Route::get('/', App\Livewire\RumahSakit\Index::class)
            ->name('rumahsakit.home');
        
        
        Route::get('dokter-kami', App\Livewire\Dokter\Find::class)->name('rumahsakit.dokter_kami');

    });