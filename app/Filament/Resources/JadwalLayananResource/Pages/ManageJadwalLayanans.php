<?php

namespace App\Filament\Resources\JadwalLayananResource\Pages;

use App\Filament\Resources\JadwalLayananResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageJadwalLayanans extends ManageRecords
{
    protected static string $resource = JadwalLayananResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
