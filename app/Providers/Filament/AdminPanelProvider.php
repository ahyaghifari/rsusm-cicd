<?php

namespace App\Providers\Filament;

use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\FontProviders\GoogleFontProvider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path(env('ADMIN_PATH', 'manage'))
            ->login()
            ->profile()
            ->brandLogo(fn () => view('filament.brand'))
            ->brandLogoHeight('5rem')
            ->brandName('RSU Syifa Medika')
            ->colors([
                'primary' => '#d606b0',
                'gray' => Color::Gray
            ])
            ->font('Plus Jakarta Sans', provider: GoogleFontProvider::class)
            ->navigationGroups([
                NavigationGroup::make()
                    ->label('Dokter'),
                NavigationGroup::make()
                    ->label('Poliklinik / Rawat Jalan'),
                NavigationGroup::make()
                    ->label('Media Informasi'),
                NavigationGroup::make()
                    ->label('Tentang Kami'),
                NavigationGroup::make()
                    ->label('Layanan'),
                NavigationGroup::make()
                    ->label('Rawat Inap'),
            ])
            ->plugins([
                FilamentShieldPlugin::make(),
            ])
            ->sidebarWidth('18rem')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
            ])
            ->renderHook(
                'panels::sidebar.nav.start',
                fn () => view('filament.components.rumahsakit-context')
            )
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): string => Blade::render('
                    <style>
                        .fi-sidebar-item.fi-active .fi-sidebar-item-button {
                            background-color: #ffe4fe !important; /* Ganti warna di sini */
                        }
                        .fi-section-content-ctn ..fi-section-content{
                            background-color: #ffe4fe !important; /* Ganti warna di sini */
                        }
                    </style>
                ',
                
                ),
                
            )
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
