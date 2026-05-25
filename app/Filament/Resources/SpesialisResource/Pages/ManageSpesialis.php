<?php

namespace App\Filament\Resources\SpesialisResource\Pages;

use App\Filament\Resources\SpesialisResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageSpesialis extends ManageRecords
{
    protected static string $resource = SpesialisResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
            ->mutateFormDataUsing(function (array $data): array {

                if (!SpesialisResource::isSuperAdmin()) {

                    $data['rumah_sakit_id']
                        = SpesialisResource::rumahSakitId();

                }

                return $data;
            }),
        ];
    }
}
