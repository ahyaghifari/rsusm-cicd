<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

/** Kolom `kode`: acak 8 karakter (A-Z0-9), unik, diisi otomatis saat create. */
trait HasKode
{
    protected static function bootHasKode(): void
    {
        static::creating(fn ($m) => $m->kode ??= static::generateKode());
    }

    /** Dijamin belum dipakai (termasuk baris soft-deleted). */
    public static function generateKode(): string
    {
        do {
            $kode = strtoupper(Str::random(8));
        } while (static::withTrashed()->where('kode', $kode)->exists());

        return $kode;
    }
}
