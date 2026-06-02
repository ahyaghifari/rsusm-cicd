<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum Hari: string implements HasLabel
{
    case SENIN   = 'SENIN';
    case SELASA  = 'SELASA';
    case RABU    = 'RABU';
    case KAMIS   = 'KAMIS';
    case JUMAT   = 'JUMAT';
    case SABTU   = 'SABTU';
    case MINGGU  = 'MINGGU';

    public function getLabel(): ?string
    {
        return match($this) {
            self::SENIN  => 'Senin',
            self::SELASA => 'Selasa',
            self::RABU   => 'Rabu',
            self::KAMIS  => 'Kamis',
            self::JUMAT  => 'Jumat',
            self::SABTU  => 'Sabtu',
            self::MINGGU => 'Minggu',
        };
    }
}
