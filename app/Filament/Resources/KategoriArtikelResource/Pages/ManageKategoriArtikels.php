<?php

namespace App\Filament\Resources\KategoriArtikelResource\Pages;

use App\Filament\Resources\KategoriArtikelResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageKategoriArtikels extends ManageRecords
{
    protected static string $resource = KategoriArtikelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->mutateFormDataUsing(fn (array $data): array => KategoriArtikelResource::mutateFormDataBeforeCreate($data)),
        ];
    }
}
