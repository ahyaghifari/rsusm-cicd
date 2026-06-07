<?php

namespace App\Filament\Resources\RawatInapResource\Pages;

use App\Filament\Resources\RawatInapResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateRawatInap extends CreateRecord
{
    protected static string $resource = RawatInapResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {

        if (!RawatInapResource::isSuperAdmin()) {

            $data['rumah_sakit_id'] = RawatInapResource::rumahSakitId();

        }

        return $data;
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

