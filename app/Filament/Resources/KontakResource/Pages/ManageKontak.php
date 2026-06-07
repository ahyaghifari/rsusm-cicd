<?php

namespace App\Filament\Resources\KontakResource\Pages;

use App\Filament\Resources\KontakResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageKontak extends ManageRecords
{
    protected static string $resource = KontakResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
            ->mutateFormDataUsing(function (array $data): array {

                if (!KontakResource::isSuperAdmin()) {

                    $data['rumah_sakit_id']
                        = KontakResource::rumahSakitId();

                }

                return $data;
            }),
        ];
    }
}
