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
        Route::get('jadwal-praktek', App\Livewire\Pages\JadwalPraktek::class)->name('rumahsakit.jadwal_praktek');
        Route::get('jadwal-poliklinik', App\Livewire\Pages\JadwalPoliklinik::class)->name('rumahsakit.jadwal_poliklinik');
        Route::get('rawat-jalan', App\Livewire\Pages\RawatJalan::class)->name('rumahsakit.rawat_jalan');
        Route::get('rawat-jalan/{poliklinik}', App\Livewire\Pages\PoliKlinikDetail::class)->name('rumahsakit.rawat_jalan_show');
        Route::get('rawat-inap', App\Livewire\Pages\RawatInap::class)->name('rumahsakit.rawat_inap');
        Route::get('unggulan', App\Livewire\Pages\LayananUnggulan::class)->name('rumahsakit.unggulan');
        Route::get('fasilitas-pendukung', App\Livewire\Pages\FasilitasPendukung::class)->name('rumahsakit.fasilitas_pendukung');
        Route::get('penunjang-medis', App\Livewire\Pages\PenunjangMedis::class)->name('rumahsakit.penunjang_medis');
        Route::get('partner-kami', App\Livewire\Pages\PartnerKami::class)->name('rumahsakit.partner_kami');
        Route::get('hubungi-kami', App\Livewire\Pages\HubungiKami::class)->name('rumahsakit.hubungi_kami');
        Route::get('promo', App\Livewire\Pages\Promo::class)->name('rumahsakit.promo');
        Route::get('promo/{promo:slug}', App\Livewire\Pages\PromoDetail::class)->name('rumahsakit.promo_detail');
        Route::get('info/{slug}', App\Livewire\Pages\HalamanStatis::class)->name('rumahsakit.halaman_statis');
        Route::get('magazine', App\Livewire\Pages\Magazines::class)->name('rumahsakit.magazine');
        Route::get('faq', App\Livewire\Pages\Faq::class)->name('rumahsakit.faq');
    });