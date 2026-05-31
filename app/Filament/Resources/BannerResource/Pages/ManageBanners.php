<?php

namespace App\Filament\Resources\BannerResource\Pages;

use App\Filament\Resources\BannerResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

/**
 * ManageBanners — satu halaman untuk CRUD (Simple Resource).
 *
 * Mode Simple menggunakan modal, sehingga Create / Edit / Delete
 * semuanya terjadi di halaman yang sama tanpa navigasi tambahan.
 */
class ManageBanners extends ManageRecords
{
    protected static string $resource = BannerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
        ];
    }
}