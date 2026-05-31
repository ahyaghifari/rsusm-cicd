<?php

namespace App\Filament\Resources\FasilitasRawatInapResource\Pages;

use App\Filament\Resources\FasilitasRawatInapResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageFasilitasRawatInaps extends ManageRecords
{
    protected static string $resource = FasilitasRawatInapResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
