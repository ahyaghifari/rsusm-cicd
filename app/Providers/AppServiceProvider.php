<?php

namespace App\Providers;

use Artesaos\SEOTools\Facades\OpenGraph;
use Artesaos\SEOTools\Facades\SEOMeta;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // rate limiter
        RateLimiter::for('public-api', function (Request $request) {
            return Limit::perMinute(20)->by($request->ip());
        });

        RateLimiter::for('portal', function (Request $request) {
            return Limit::perMinute(100)->by($request->ip());
        });

        Role::deleting(function (Role $role) {
            if ($role->name === config('filament-shield.super_admin.name', 'super_admin')) {
                abort(403, 'Role super_admin tidak dapat dihapus.');
            }
        });

        View::composer('layouts.rumah_sakit', function ($view) {
            $rs = current_rumahsakit();
            $view->with('currentRumahSakit', $rs);

            SEOMeta::setTitleDefault($rs->nama);
            SEOMeta::setTitleSeparator(' — ');
            OpenGraph::setSiteName($rs->nama);
            OpenGraph::setUrl(request()->url());
            OpenGraph::setType('website');
        });
    }
}
