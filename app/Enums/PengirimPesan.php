<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum PengirimPesan: string implements HasLabel
{
    case PASIEN = 'PASIEN';
    case DOKTER = 'DOKTER';

    public function getLabel(): ?string
    {
        return match($this) {
            self::PASIEN => 'Pasien',
            self::DOKTER => 'Dokter',
        };
    }
}
