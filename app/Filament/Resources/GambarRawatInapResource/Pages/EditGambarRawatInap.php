<?php

namespace App\Filament\Resources\GambarRawatInapResource\Pages;

use App\Filament\Resources\GambarRawatInapResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGambarRawatInap extends EditRecord
{
    protected static string $resource = GambarRawatInapResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

