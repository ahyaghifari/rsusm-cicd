<?php

namespace App\Filament\Resources\PoliKlinikResource\Pages;

use App\Filament\Resources\PoliKlinikResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManagePoliKliniks extends ManageRecords
{
    protected static string $resource = PoliKlinikResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}