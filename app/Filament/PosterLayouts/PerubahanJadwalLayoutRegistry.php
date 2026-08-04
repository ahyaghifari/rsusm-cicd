<?php

namespace App\Filament\PosterLayouts;

use App\Filament\PosterLayouts\Contracts\PosterLayout;
use App\Filament\PosterLayouts\Layouts\PerubahanJadwalLayout;

class PerubahanJadwalLayoutRegistry
{
    /**
     * RS ID → layout class untuk poster Perubahan Jadwal. Sama seperti
     * LayoutRegistry (poster jadwal harian), tambah entri di sini kalau
     * ada cabang yang butuh tampilan perubahan jadwal berbeda.
     */
    private const MAP = [
        //
    ];

    public static function for(int $rumahSakitId): PosterLayout
    {
        $class = self::MAP[$rumahSakitId] ?? PerubahanJadwalLayout::class;
        return new $class();
    }
}
