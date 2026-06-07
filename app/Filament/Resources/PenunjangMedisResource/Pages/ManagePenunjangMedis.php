<?php

namespace App\Filament\Resources\PenunjangMedisResource\Pages;

use App\Filament\Resources\PenunjangMedisResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManagePenunjangMedis extends ManageRecords
{
    protected static string $resource = PenunjangMedisResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
            ->mutateFormDataUsing(function (array $data): array {

                if (!PenunjangMedisResource::isSuperAdmin()) {

                    $data['rumah_sakit_id'] = PenunjangMedisResource::rumahSakitId();

                }

                return $data;
            }),
        ];
    }
}
