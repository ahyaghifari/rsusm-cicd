<?php

namespace App\Filament\Resources\LinkLayananResource\Pages;

use App\Filament\Resources\LinkLayananResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageLinkLayanan extends ManageRecords
{
    protected static string $resource = LinkLayananResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
