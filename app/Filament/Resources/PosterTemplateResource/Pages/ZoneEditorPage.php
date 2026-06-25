<?php

namespace App\Filament\Resources\PosterTemplateResource\Pages;

use App\Filament\Resources\PosterTemplateResource;
use App\Models\PosterTemplate;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Storage;

class ZoneEditorPage extends Page
{
    use InteractsWithRecord;

    protected static string $resource = PosterTemplateResource::class;

    /** Full-page view — no Filament panel wrapper, no sidebar. */
    protected static string $view = 'filament.resources.poster-template-resource.pages.zone-editor-page';

    /** Override layout — blank pass-through since the view is a complete HTML page. */
    protected static string $layout = 'components.layouts.blank';

    // ── Config bound via Alpine $wire.entangle ────────────────────────────
    public array $config = [];

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
        $this->config = $this->record->config ?? PosterTemplate::defaultConfig();
    }

    // ── Layout Props ──────────────────────────────────────────────────────────

    /** URL untuk tombol "Kembali" di top bar. */
    public function getBackUrlProperty(): string
    {
        return PosterTemplateResource::getUrl('index');
    }

    /** Nama template yang ditampilkan di top bar. */
    public function getRecordNameProperty(): string
    {
        return $this->record->nama;
    }

    // ── Save ──────────────────────────────────────────────────────────────────

    public function save(): void
    {
        $this->record->update(['config' => $this->config]);

        Notification::make()
            ->title('Zone berhasil disimpan')
            ->success()
            ->send();
    }

    // ── Helpers untuk view ────────────────────────────────────────────────────

    /** URL template PNG untuk preview canvas. */
    public function getTemplatePngUrlProperty(): ?string
    {
        return $this->record->template_png
            ? Storage::url($this->record->template_png)
            : null;
    }

}
