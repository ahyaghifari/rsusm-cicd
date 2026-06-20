<?php

namespace App\Filament\Resources\KelasRawatInapResource\Pages;

use App\Filament\Resources\KelasRawatInapResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageKelasRawatInap extends ManageRecords
{
    protected static string $resource = KelasRawatInapResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->mutateFormDataUsing(fn (array $data): array => KelasRawatInapResource::mutateFormDataBeforeCreate($data)),
        ];
    }
}
