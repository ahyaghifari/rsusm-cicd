<?php

namespace App\Filament\Resources\JadwalLayananHarianResource\Pages;

use App\Filament\Resources\JadwalLayananHarianResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageJadwalLayananHarians extends ManageRecords
{
    protected static string $resource = JadwalLayananHarianResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
