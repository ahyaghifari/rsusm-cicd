<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum StatusKetersediaanKamar: int implements HasLabel, HasColor
{
    case KOSONG     = 1;
    case RESERVASI  = 2;
    case TERISI     = 3;
    case PERBAIKAN  = 6;

    public function getLabel(): ?string
    {
        return match ($this) {
            self::KOSONG    => 'Kosong',
            self::RESERVASI => 'Reservasi',
            self::TERISI    => 'Terisi',
            self::PERBAIKAN => 'Sedang Perbaikan',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::KOSONG    => 'success',
            self::RESERVASI => 'warning',
            self::TERISI    => 'danger',
            self::PERBAIKAN => 'gray',
        };
    }

    /**
     * Label untuk kode status yang tidak dikenali (mis. 4, 5 — belum dipakai sistem Ranap
     * per saat dokumen ini ditulis). Dipakai sebagai fallback di UI, bukan exception.
     */
    public static function labelFor(int $code): string
    {
        return self::tryFrom($code)?->getLabel() ?? 'Status Tidak Dikenal';
    }
}
