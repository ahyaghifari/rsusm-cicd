<?php

namespace App\Filament\Resources\MagazineResource\Pages;

use App\Filament\Resources\MagazineResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageMagazines extends ManageRecords
{
    protected static string $resource = MagazineResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->mutateFormDataUsing(fn (array $data): array => MagazineResource::mutateFormDataBeforeCreate($data)),
        ];
    }
}
