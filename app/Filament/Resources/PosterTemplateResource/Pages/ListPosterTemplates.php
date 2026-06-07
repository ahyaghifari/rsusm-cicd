<?php

namespace App\Filament\Resources\PosterTemplateResource\Pages;

use App\Filament\Resources\PosterTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;


class ListPosterTemplates extends ListRecords
{
    protected static string $resource = PosterTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
