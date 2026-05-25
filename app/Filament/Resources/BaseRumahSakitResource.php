<?php

namespace App\Filament\Resources;

use App\Models\User;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;

// Class ini akan dipakai oleh semua resource
abstract class BaseRumahSakitResource extends BaseResource
{
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        // jika superadmin maka ambil seluruh data tanpa lihat rumah sakit mana
        if (static::isSuperAdmin()) {
            return $query;
        }

        // jika admin rumah sakit maka ambil setiap query dengan rumah sakit id yang sama dengan user
        return $query->where(
            'rumah_sakit_id',
            static::rumahSakitId()
        );
    }
}