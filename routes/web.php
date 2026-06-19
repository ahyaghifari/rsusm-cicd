<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PortalController;
use App\Http\Controllers\SitemapController;
use App\Http\Middleware\RumahSakitMiddleware;

// ── Poster Preview (one-time HTML, admin only) ───────────────────────────────
Route::get('/poster-preview/{key}', function (string $key) {
    abort_if(! preg_match('/^[0-9a-f\-]{36}$/', $key), 404);
    $path = storage_path("app/poster-preview/{$key}.html");
    abort_if(! file_exists($path), 404);
    $html = file_get_contents($path);
    @unlink($path);
    return response($html)->header('Content-Type', 'text/html; charset=utf-8');
})->name('poster.preview')->middleware('auth');

Route::get('/', [PortalController::class, 'index'])->middleware('throttle:portal')->name('home');
Route::get('/cari-spesialis', [PortalController::class, 'spesialis'])->middleware('throttle:public-api')->name('cari_spesialis');

// ── Sitemap (otomatis dari database, di-cache 6 jam) ─────────────────────────
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap.index');
Route::get('/{rumahsakit}/sitemap.xml', [SitemapController::class, 'show'])
    ->middleware('throttle:portal')
    ->name('rumahsakit.sitemap');

Route::prefix('{rumahsakit}')
    ->middleware([RumahSakitMiddleware::class, 'throttle:portal'])
    ->group(function () {

        Route::get('/', App\Livewire\RumahSakit\Index::class)->name('rumahsakit.home');

        Route::get('dokter-kami', App\Livewire\Dokter\Find::class)->name('rumahsakit.dokter_kami');
        Route::get('dokter-kami/{dokter}', App\Livewire\Dokter\Show::class)->name('rumahsakit.dokter_show');
        Route::get('tanya-dokter', App\Livewire\Pages\TanyaDokter::class)->name('rumahsakit.tanya_dokter');
        Route::get('konsultasi/{sesi:token}', App\Livewire\Pages\KonsultasiChat::class)->name('rumahsakit.konsultasi');
        Route::get('jadwal-praktek', App\Livewire\Pages\JadwalPraktek::class)->name('rumahsakit.jadwal_praktek');
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
        Route::get('artikel', App\Livewire\Pages\Artikel::class)->name('rumahsakit.artikel');
        Route::get('artikel/{artikel:slug}', App\Livewire\Pages\ArtikelDetail::class)->name('rumahsakit.artikel_detail');
    });