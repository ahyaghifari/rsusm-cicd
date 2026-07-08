<?php

namespace App\Filament\Resources\ArtikelResource\Pages;

use App\Filament\Resources\ArtikelResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditArtikel extends EditRecord
{
    protected static string $resource = ArtikelResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
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
