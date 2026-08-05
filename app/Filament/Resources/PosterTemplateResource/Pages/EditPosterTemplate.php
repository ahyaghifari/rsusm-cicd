<?php

namespace App\Filament\Resources\PosterTemplateResource\Pages;

use App\Filament\Resources\PosterTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPosterTemplate extends EditRecord
{
    protected static string $resource = PosterTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('zone_editor')
                ->label('Edit Zone')
                ->icon('heroicon-o-paint-brush')
                ->color('primary')
                ->url(function () {
                    $layout   = $this->record->layout();
                    $routeKey = match (true) {
                        $layout instanceof \App\Filament\PosterLayouts\Layouts\PerubahanJadwalLayout => 'zone-editor-perubahan-jadwal',
                        $layout instanceof \App\Filament\PosterLayouts\Layouts\ListPolosLayout        => 'zone-editor-list-polos',
                        default                                                                       => 'zone-editor',
                    };
                    return PosterTemplateResource::getUrl($routeKey, ['record' => $this->record]);
                }),
            Actions\DeleteAction::make(),
        ];
    }
}
