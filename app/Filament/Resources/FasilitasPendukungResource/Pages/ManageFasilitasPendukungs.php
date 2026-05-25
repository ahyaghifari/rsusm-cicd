<?php

namespace App\Filament\Resources\FasilitasPendukungResource\Pages;

use App\Filament\Resources\FasilitasPendukungResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageFasilitasPendukung extends ManageRecords
{
    protected static string $resource = FasilitasPendukungResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}