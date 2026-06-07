<?php

namespace App\Filament\Resources\GambarRawatInapResource\Pages;

use App\Filament\Resources\GambarRawatInapResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGambarRawatInaps extends ListRecords
{
    protected static string $resource = GambarRawatInapResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
