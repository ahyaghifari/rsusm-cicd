<?php

namespace App\Filament\Resources\RumahSakitResource\Pages;

use App\Filament\Resources\RumahSakitResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateRumahSakit extends CreateRecord
{
    protected static string $resource = RumahSakitResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
