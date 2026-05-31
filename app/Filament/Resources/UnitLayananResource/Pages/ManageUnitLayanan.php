<?php

namespace App\Filament\Resources\UnitLayananResource\Pages;

use App\Filament\Resources\UnitLayananResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageUnitLayanan extends ManageRecords
{
    protected static string $resource = UnitLayananResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}