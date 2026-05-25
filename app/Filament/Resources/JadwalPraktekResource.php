<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JadwalPraktekResource\Pages;
use App\Models\JadwalPraktek;

class JadwalPraktekResource extends BaseResource
{
    protected static ?string $model = JadwalPraktek::class;

    protected static ?int $navigationSort = 2;
    protected static string | null $navigationGroup = 'Dokter';
    protected static ?string $navigationIcon = 'fas-calendar';

    public static function getPages(): array
    {
        return [
            'index' => Pages\JadwalPraktekDokter::route('/'),
        ];
    }
}
