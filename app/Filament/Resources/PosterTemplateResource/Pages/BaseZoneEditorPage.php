<?php

namespace App\Filament\Resources\PosterTemplateResource\Pages;

use App\Filament\Resources\PosterTemplateResource;
use App\Models\PosterTemplate;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Storage;

abstract class BaseZoneEditorPage extends Page
{
    use InteractsWithRecord;

    protected static string $resource = PosterTemplateResource::class;
    protected static string $layout = 'components.layouts.blank';

    public static array $availableFonts = [
        'Montserrat', 'Poppins', 'Roboto', 'Open Sans', 'Lato',
        'Nunito', 'Raleway', 'Inter', 'Oswald', 'Playfair Display',
    ];

    public static array $availableFontWeights = [
        '300' => 'Light (300)',
        '400' => 'Normal (400)',
        '500' => 'Medium (500)',
        '600' => 'Semibold (600)',
        '700' => 'Bold (700)',
        '800' => 'Extra Bold (800)',
        '900' => 'Black (900)',
    ];

    public array $config = [];

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);

        /** @var \App\Models\User $user */
        $user = filament()->auth()->user();
        abort_unless(
            $user->isSuperAdmin() || (int) $user->rumah_sakit_id === (int) $this->record->rumah_sakit_id,
            403
        );

        $this->config = $this->record->config
            ?? PosterTemplate::defaultConfig((int) $this->record->rumah_sakit_id);
    }

    public function save(): void
    {
        $this->record->update(['config' => $this->config]);

        Notification::make()->title('Zone berhasil disimpan')->success()->send();
    }

    public function getBackUrlProperty(): string
    {
        return PosterTemplateResource::getUrl('index');
    }

    public function getRecordNameProperty(): string
    {
        return $this->record->nama;
    }

    public function getTemplatePngUrlProperty(): ?string
    {
        return $this->record->template_png
            ? Storage::url($this->record->template_png)
            : null;
    }

    public function getShapePoliUrlProperty(): ?string
    {
        return $this->record->shape_poli
            ? Storage::url($this->record->shape_poli)
            : null;
    }

    public function getGoogleFontsUrlProperty(): string
    {
        $families = collect(static::$availableFonts)
            ->map(fn ($f) => str_replace(' ', '+', $f) . ':wght@400;600;700')
            ->join('&family=');

        return "https://fonts.googleapis.com/css2?family={$families}&display=swap";
    }
}
