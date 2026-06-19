<?php

namespace App\Providers\Filament;

use App\Filament\Dokter\Pages\KonsultasiDashboard;
use App\Filament\Dokter\Pages\RiwayatKonsultasi;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class DokterPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('dokter')
            ->path('dokter')
            ->login()
            // ->brandName('Panel Dokter — RSU Syifa Medika')
            ->brandLogo(fn () => view('filament.brand'))
            ->brandLogoHeight('5rem')
            ->brandName('Dokter - RSU Syifa Medika')
            ->colors([
                'primary' => '#006c4b',
                'gray' => Color::Slate
            ])
            ->sidebarWidth('15rem')
            ->pages([
                KonsultasiDashboard::class,
                RiwayatKonsultasi::class,
            ])
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                /**
                 * Panel Filament memuat bundle aset sendiri (lihat @filamentStyles/@filamentScripts)
                 * dan TIDAK otomatis menyertakan resources/js/app.js milik aplikasi —
                 * padahal window.Echo (dipakai listener #[On('echo:...')] / #[On('echo-private:...')]
                 * di KonsultasiDashboard) hanya diinisialisasi di sana (lihat resources/js/echo.js).
                 * Tanpa baris ini, listener broadcasting di panel dokter diam saja tanpa error
                 * ("works on reload, not live") karena window.Echo tidak pernah ada.
                 */
                fn (): string => Blade::render("@vite(['resources/css/app.css', 'resources/js/app.js'])")
            )
            ->profile()
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
