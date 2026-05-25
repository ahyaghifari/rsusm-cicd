<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PortalController;
use App\Http\Middleware\RumahSakitMiddleware;

Route::get('/', [PortalController::class, 'index'])->name('home');
Route::get('/cari-spesialis', [PortalController::class, 'spesialis'])->name('cari_spesialis');

Route::prefix('{rumahsakit}')
    ->middleware(RumahSakitMiddleware::class)
    ->group(function () {

        Route::get('/', App\Livewire\RumahSakit\Index::class)->name('rumahsakit.home');


        Route::get('dokter-kami', App\Livewire\Dokter\Find::class)->name('rumahsakit.dokter_kami');
        Route::get('dokter-kami/{dokter}', App\Livewire\Dokter\Show::class)->name('rumahsakit.dokter_show');
        Route::get('jadwal-praktek', App\Livewire\JadwalPraktek\Index::class)->name('rumahsakit.jadwal_praktek');
        Route::get('rawat-jalan', App\Livewire\Pages\RawatJalan::class)->name('rumahsakit.rawat_jalan');
        Route::get('rawat-inap', App\Livewire\Pages\RawatInap::class)->name('rumahsakit.rawat_inap');
        Route::get('unggulan', App\Livewire\Pages\LayananUnggulan::class)->name('rumahsakit.unggulan');
        Route::get('fasilitas-pendukung', App\Livewire\Pages\FasilitasPendukung::class)->name('rumahsakit.fasilitas_pendukung');
        Route::get('penunjang-medis', App\Livewire\Pages\PenunjangMedis::class)->name('rumahsakit.penunjang_medis');
        Route::get('partner-kami', App\Livewire\Pages\PartnerKami::class)->name('rumahsakit.partner_kami');
        Route::get('hubungi-kami', App\Livewire\Pages\HubungiKami::class)->name('rumahsakit.hubungi_kami');
    });