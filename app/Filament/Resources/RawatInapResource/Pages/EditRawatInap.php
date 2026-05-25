<?php

namespace App\Filament\Resources\RawatInapResource\Pages;

use App\Filament\Resources\RawatInapResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRawatInap extends EditRecord
{
    protected static string $resource = RawatInapResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {

        if (!RawatInapResource::isSuperAdmin()) {

            $data['rumah_sakit_id'] = RawatInapResource::rumahSakitId();

        }

        return $data;
    }
}
