<?php

namespace App\Filament\Resources\LayananUnggulanResource\Pages;

use App\Filament\Resources\LayananUnggulanResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageLayananUnggulans extends ManageRecords
{
    protected static string $resource = LayananUnggulanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Membuka modal form saat tombol "Create" diklik
            Actions\CreateAction::make()
            ->mutateFormDataUsing(function (array $data): array {

                if (!LayananUnggulanResource::isSuperAdmin()) {

                    $data['rumah_sakit_id'] = LayananUnggulanResource::rumahSakitId();

                }

                return $data;
            }),
        ];
    }
}