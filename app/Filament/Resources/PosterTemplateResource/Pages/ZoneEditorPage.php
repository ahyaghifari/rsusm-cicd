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

    // ── Font List — edit di sini untuk menambah/menghapus pilihan font ────────

    /**
     * Daftar Google Fonts yang tersedia di semua dropdown font pada Zone Editor.
     * Tambahkan nama font di sini, maka akan otomatis muncul di semua select font
     * dan ikut di-load via Google Fonts link di halaman editor.
     *
     * @var array<string>
     */
    public static array $availableFonts = [
        'Montserrat',
        'Poppins',
        'Roboto',
        'Open Sans',
        'Lato',
        'Nunito',
        'Raleway',
        'Inter',
        'Oswald',
        'Playfair Display',
    ];

    /**
     * Daftar pilihan font weight di semua dropdown weight pada Zone Editor.
     * Format: ['value' => 'Label'] — value dipakai sebagai CSS font-weight.
     *
     * @var array<string, string>
     */
    public static array $availableFontWeights = [
        '300' => 'Light (300)',
        '400' => 'Normal (400)',
        '500' => 'Medium (500)',
        '600' => 'Semibold (600)',
        '700' => 'Bold (700)',
        '800' => 'Extra Bold (800)',
        '900' => 'Black (900)',
    ];

    // ── Config bound via Alpine $wire.entangle ────────────────────────────────
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

    /** URL shape poli (null jika tidak diupload). */
    public function getShapePoliUrlProperty(): ?string
    {
        return $this->record->shape_poli
            ? Storage::url($this->record->shape_poli)
            : null;
    }

    /**
     * Bangun string Google Fonts URL query dari $availableFonts.
     * Dipakai oleh blade untuk load font via <link>.
     */
    public function getGoogleFontsUrlProperty(): string
    {
        $families = collect(static::$availableFonts)
            ->map(fn ($f) => str_replace(' ', '+', $f) . ':wght@400;600;700')
            ->join('&family=');

        return "https://fonts.googleapis.com/css2?family={$families}&display=swap";
    }

}
