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
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
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
                    ->label('Media Informasi')
                    ->collapsed()
                    ,
                NavigationGroup::make()
                    ->label('Dokter'),
                NavigationGroup::make()
                    ->label('Poliklinik / Rawat Jalan'),
                NavigationGroup::make()
                    ->label('Layanan')
                    ->collapsed()
                    ,
                NavigationGroup::make()
                    ->label('Rawat Inap')
                    ->collapsed()
                    ,
                NavigationGroup::make()
                    ->label('Lainnya')
                    ->collapsed()
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
