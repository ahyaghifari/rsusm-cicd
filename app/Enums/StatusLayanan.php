<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum StatusLayanan: string implements HasLabel, HasColor
{
    case BUKA  = 'BUKA';
    case LIBUR = 'LIBUR';

    public function getLabel(): ?string
    {
        return match($this) {
            self::BUKA  => 'Buka',
            self::LIBUR => 'Libur',
        };
    }

    public function getColor(): string|array|null
    {
        return match($this) {
            self::BUKA  => 'success',
            self::LIBUR => 'danger',
        };
    }
}
