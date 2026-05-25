<?php

namespace App\Filament\Resources\PenunjangMedisResource\Pages;

use App\Filament\Resources\PenunjangMedisResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManagePenunjangMedis extends ManageRecords
{
    protected static string $resource = PenunjangMedisResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
