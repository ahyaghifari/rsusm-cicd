<?php

namespace App\Providers;

use Artesaos\SEOTools\Facades\OpenGraph;
use Artesaos\SEOTools\Facades\SEOMeta;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
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
