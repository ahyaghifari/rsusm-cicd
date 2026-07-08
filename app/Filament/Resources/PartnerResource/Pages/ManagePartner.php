<?php

namespace App\Filament\Resources\PartnerResource\Pages;

use App\Filament\Resources\PartnerResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManagePartner extends ManageRecords
{
    protected static string $resource = PartnerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->mutateFormDataUsing(function (array $data): array {
                    if (! PartnerResource::isSuperAdmin()) {
                        $data['rumah_sakit_id'] = PartnerResource::rumahSakitId();
                    }

                    return $data;
                }),
        ];
    }
}
