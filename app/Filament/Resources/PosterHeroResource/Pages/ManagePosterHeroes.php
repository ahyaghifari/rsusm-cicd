<?php

namespace App\Filament\Resources\PosterHeroResource\Pages;

use App\Filament\Resources\PosterHeroResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManagePosterHeroes extends ManageRecords
{
    protected static string $resource = PosterHeroResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->mutateFormDataUsing(fn (array $data): array => PosterHeroResource::mutateFormDataBeforeCreate($data)),
        ];
    }
}
