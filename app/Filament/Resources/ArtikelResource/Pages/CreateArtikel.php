<?php

namespace App\Filament\Resources\ArtikelResource\Pages;

use App\Filament\Resources\ArtikelResource;
use Filament\Resources\Pages\CreateRecord;

class CreateArtikel extends CreateRecord
{
    protected static string $resource = ArtikelResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {

        if (!ArtikelResource::isSuperAdmin()) {

            $data['rumah_sakit_id'] = ArtikelResource::rumahSakitId();

        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
