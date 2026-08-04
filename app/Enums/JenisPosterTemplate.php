<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum JenisPosterTemplate: string implements HasLabel, HasColor
{
    case JADWAL_HARIAN    = 'JADWAL_HARIAN';
    case PERUBAHAN_JADWAL = 'PERUBAHAN_JADWAL';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::JADWAL_HARIAN    => 'Jadwal Harian',
            self::PERUBAHAN_JADWAL => 'Perubahan Jadwal',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::JADWAL_HARIAN    => 'primary',
            self::PERUBAHAN_JADWAL => 'warning',
        };
    }
}
