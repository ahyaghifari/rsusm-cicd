<?php

namespace App\Filament\Resources\HalamanResource\Pages;

use App\Filament\Resources\HalamanResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateHalaman extends CreateRecord
{
    protected static string $resource = HalamanResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (!HalamanResource::isSuperAdmin()) {
            $data['rumah_sakit_id'] = HalamanResource::rumahSakitId();
        }

        return $data;
    }
}
