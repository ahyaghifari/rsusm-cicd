<?php

namespace App\Filament\Resources\GedungResource\Pages;

use App\Filament\Resources\GedungResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageGedungs extends ManageRecords
{
    protected static string $resource = GedungResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
