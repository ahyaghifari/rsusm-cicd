<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;

abstract class BaseRumahSakitResource extends BaseResource
{
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (static::isSuperAdmin()) {
            return $query;
        }

        return $query->where('rumah_sakit_id', static::rumahSakitId());
    }

    // ---------------------------------------------------------------
    // Helper: field Select rumah_sakit_id untuk form
    // ---------------------------------------------------------------
    public static function rsFormField(): Forms\Components\Select
    {
        return Forms\Components\Select::make('rumah_sakit_id')
            ->relationship('rumahSakit', 'nama')
            ->required()
            ->label('Rumah Sakit')
            ->default(fn () => static::rumahSakitId())
            ->visible(fn () => static::isSuperAdmin());
    }

    // ---------------------------------------------------------------
    // Helper: kolom rumahSakit.nama untuk tabel
    // ---------------------------------------------------------------
    public static function rsTableColumn(): Tables\Columns\TextColumn
    {
        return Tables\Columns\TextColumn::make('rumahSakit.nama')
            ->label('Rumah Sakit')
            ->searchable()
            ->sortable()
            ->visible(fn () => static::isSuperAdmin());
    }

    // ---------------------------------------------------------------
    // Helper: filter rumah_sakit_id untuk tabel
    // ---------------------------------------------------------------
    public static function rsTableFilter(): Tables\Filters\SelectFilter
    {
        return Tables\Filters\SelectFilter::make('rumah_sakit_id')
            ->relationship('rumahSakit', 'nama')
            ->label('Rumah Sakit')
            ->visible(fn () => static::isSuperAdmin());
    }
}
