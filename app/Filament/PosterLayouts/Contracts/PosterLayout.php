<?php

namespace App\Filament\PosterLayouts\Contracts;

interface PosterLayout
{
    /** Nama layout yang ditampilkan di UI. */
    public function label(): string;

    /** Default config JSON untuk template baru. */
    public function defaultConfig(): array;

    /** FQCN Filament Page untuk zone editor layout ini. */
    public function zoneEditorPageClass(): string;

    /** Blade view name untuk generate/preview poster. */
    public function templateView(): string;

    /**
     * Field konfigurasi grid yang ditampilkan di panel "Sesuaikan Cepat".
     * Setiap entry: ['key' => string, 'label' => string, 'quick_setting' => true]
     */
    public function quickConfigFields(): array;
}
