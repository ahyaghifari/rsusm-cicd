<?php

namespace App\Filament\Resources\PosterTemplateResource\Pages;

use App\Filament\Resources\PosterTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePosterTemplate extends CreateRecord
{
    protected static string $resource = PosterTemplateResource::class;

    protected function getRedirectUrl(): string
    {
        return PosterTemplateResource::getUrl('zone-editor', ['record' => $this->record]);
    }

    protected function afterCreate(): void
    {
        // Beri default config saat pertama kali dibuat
        if (empty($this->record->config)) {
            $this->record->update(['config' => \App\Models\PosterTemplate::defaultConfig()]);
        }
    }
}
