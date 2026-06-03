<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GambarRawatInapResource\Pages;
use App\Models\GambarRawatInap;
use App\Models\RawatInap;
use App\Models\RumahSakit;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class GambarRawatInapResource extends BaseResource
{
    protected static ?string $model = GambarRawatInap::class;

    protected static ?string $navigationLabel = 'Gambar';
    protected static ?int $navigationSort = 3;
    protected static string | null $navigationGroup = 'Rawat Inap';
    protected static ?string $navigationIcon = 'fas-image';

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
                Forms\Components\Section::make('Detail Gambar')
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
                            ->afterStateUpdated(fn (Forms\Set $set) => $set('rawat_inap_id', null))
                            ->columnSpanFull(),

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

                        Forms\Components\FileUpload::make('gambar')
                            ->image()
                            ->directory('gambar-rawat-inap')
                            ->disk('public')
                            ->required(),

                        Forms\Components\Textarea::make('deskripsi')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Pengaturan')
                    ->schema([
                        Forms\Components\Toggle::make('aktif')
                            ->required()
                            ->default(true),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('gambar')
                    ->disk('public')
                    ->circular(),
                Tables\Columns\TextColumn::make('rawatInap.rumahSakit.nama')
                    ->label('Rumah Sakit')
                    ->sortable()
                    ->searchable()
                    ->visible(fn () => static::isSuperAdmin()),
                Tables\Columns\TextColumn::make('rawatInap.nama')
                    ->label('Kamar Rawat Inap')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('deskripsi')
                    ->limit(50)
                    ->placeholder('-')
                    ->searchable(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('aktif')
                    ->boolean()
                    ->sortable(),
            ])
            ->defaultSort('sort_order', 'asc')
            ->reorderable('sort_order')
            ->filters([
                Tables\Filters\SelectFilter::make('rawat_inap_id')
                    ->label('Kamar Rawat Inap')
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
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListGambarRawatInaps::route('/'),
            'create' => Pages\CreateGambarRawatInap::route('/create'),
            'edit'   => Pages\EditGambarRawatInap::route('/{record}/edit'),
        ];
    }
}
