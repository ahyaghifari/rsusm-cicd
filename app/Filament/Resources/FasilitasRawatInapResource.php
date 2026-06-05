<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FasilitasRawatInapResource\Pages;
use App\Models\FasilitasRawatInap;
use App\Models\RawatInap;
use App\Models\RumahSakit;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class FasilitasRawatInapResource extends BaseResource
{
    protected static ?string $model = FasilitasRawatInap::class;

    protected static ?string $navigationLabel = 'Fasilitas';
    protected static ?int $navigationSort = 2;
    protected static string | null $navigationGroup = 'Rawat Inap';
    protected static ?string $navigationIcon = 'fas-house-medical';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (static::isSuperAdmin()) {
            return $query;
        }

        return $query->whereHas('rawatInap', fn ($q) => $q->where('rumah_sakit_id', static::rumahSakitId()));
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Superadmin: pilih RS dulu, lalu rawat inap
                Forms\Components\Select::make('rumah_sakit_id')
                    ->label('Rumah Sakit')
                    ->options(RumahSakit::pluck('nama', 'id'))
                    ->required()
                    ->live()
                    ->visible(fn () => static::isSuperAdmin())
                    ->afterStateHydrated(function ($component, $record) {
                        if ($record && static::isSuperAdmin()) {
                            $component->state($record->rawatInap?->rumah_sakit_id);
                        }
                    })
                    ->afterStateUpdated(fn (Forms\Set $set) => $set('rawat_inap_id', null)),

                Forms\Components\Select::make('rawat_inap_id')
                    ->label('Rawat Inap')
                    ->options(function (Forms\Get $get) {
                        $rsId = static::isSuperAdmin()
                            ? $get('rumah_sakit_id')
                            : static::rumahSakitId();

                        if (! $rsId) return [];

                        return RawatInap::where('rumah_sakit_id', $rsId)
                            ->orderBy('nama')
                            ->pluck('nama', 'id');
                    })
                    ->searchable()
                    ->required()
                    ->disabled(fn (Forms\Get $get) => static::isSuperAdmin() && ! $get('rumah_sakit_id')),

                Forms\Components\TextInput::make('nama')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Toggle::make('aktif')
                    ->required()
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('rawatInap.rumahSakit.nama')
                    ->label('Rumah Sakit')
                    ->sortable()
                    ->searchable()
                    ->visible(fn () => static::isSuperAdmin()),
                Tables\Columns\TextColumn::make('rawatInap.nama')
                    ->label('Rawat Inap')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nama')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('aktif')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('rawat_inap_id')
                    ->label('Rawat Inap')
                    ->options(fn () => RawatInap::when(
                            ! static::isSuperAdmin(),
                            fn ($q) => $q->where('rumah_sakit_id', static::rumahSakitId())
                        )
                        ->orderBy('nama')
                        ->pluck('nama', 'id')
                    )
                    ->searchable(),
                Tables\Filters\TernaryFilter::make('aktif')
                    ->label('Status Aktif'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
            // ->bulkActions([
            //     Tables\Actions\BulkActionGroup::make([
            //         Tables\Actions\DeleteBulkAction::make(),
            //     ]),
            // ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageFasilitasRawatInaps::route('/'),
        ];
    }

}
