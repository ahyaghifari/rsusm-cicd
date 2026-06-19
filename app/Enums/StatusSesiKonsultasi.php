<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum StatusSesiKonsultasi: string implements HasLabel, HasColor
{
    case MENUNGGU    = 'MENUNGGU';
    case BERLANGSUNG = 'BERLANGSUNG';
    case SELESAI     = 'SELESAI';
    case KEDALUWARSA = 'KEDALUWARSA';

    public function getLabel(): ?string
    {
        return match($this) {
            self::MENUNGGU    => 'Menunggu Dokter',
            self::BERLANGSUNG => 'Sedang Berlangsung',
            self::SELESAI     => 'Selesai',
            self::KEDALUWARSA => 'Kedaluwarsa',
        };
    }

    public function getColor(): string|array|null
    {
        return match($this) {
            self::MENUNGGU    => 'warning',
            self::BERLANGSUNG => 'success',
            self::SELESAI     => 'gray',
            self::KEDALUWARSA => 'danger',
        };
    }
}
