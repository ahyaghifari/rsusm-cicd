<?php

namespace App\Filament\PosterLayouts;

use App\Filament\PosterLayouts\Contracts\PosterLayout;
use App\Filament\PosterLayouts\Layouts\GridShapeLayout;
use App\Filament\PosterLayouts\Layouts\ListPolosLayout;

class LayoutRegistry
{
    /** RS ID → layout class. Tambah entri baru di sini saat ada cabang baru. */
    private const MAP = [
        1 => GridShapeLayout::class,  // RSU Syifa Medika Banjarbaru
        2 => ListPolosLayout::class,  // RSU Syifa Medika Barabai
    ];

    public static function for(int $rumahSakitId): PosterLayout
    {
        $class = self::MAP[$rumahSakitId] ?? GridShapeLayout::class;
        return new $class();
    }
}
