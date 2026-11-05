<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CutiDokterResource\Pages;
use App\Models\CutiDokter;

class CutiDokterResource extends BaseRumahSakitResource
{
    protected static ?string $model = CutiDokter::class;

    protected static ?int $navigationSort = 4;
    protected static string|null $navigationGroup = 'Poliklinik / Rawat Jalan';
    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    protected static ?string $navigationLabel = 'Cuti / Libur Dokter';
    protected static ?string $modelLabel = 'Cuti Dokter';
    protected static ?string $pluralModelLabel = 'Cuti / Libur Dokter';

    public static function getPages(): array
    {
        return [
            'index' => Pages\CutiDokterPage::route('/'),
        ];
    }
}
