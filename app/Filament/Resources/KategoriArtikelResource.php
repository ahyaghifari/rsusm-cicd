<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KategoriArtikelResource\Pages;
use App\Models\KategoriArtikel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class KategoriArtikelResource extends BaseRumahSakitResource
{
    protected static ?string $model = KategoriArtikel::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationLabel = 'Kategori Artikel';

    protected static ?string $modelLabel = 'Kategori Artikel';

    protected static ?string $navigationGroup = 'Media Informasi';

    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                static::rsFormField()->live(),

                Forms\Components\TextInput::make('nama')
                    ->required()
                    ->maxLength(100)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, Forms\Set $set, $record) {
                        if (! $record) {
                            $set('slug', \Illuminate\Support\Str::slug($state));
                        }
                    }),

                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->maxLength(100)
                    ->unique(KategoriArtikel::class, 'slug', ignoreRecord: true, modifyRuleUsing: function ($rule, Forms\Get $get) {
                        return $rule->where('rumah_sakit_id', static::isSuperAdmin() ? $get('rumah_sakit_id') : static::rumahSakitId());
                    }),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('artikel_count')
                    ->label('Jumlah Artikel')
                    ->state(fn (KategoriArtikel $record): int => $record->artikel()->count()),

                static::rsTableColumn(),
            ])
            ->filters([
                static::rsTableFilter(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->mutateFormDataUsing(fn (array $data, KategoriArtikel $record): array => static::mutateFormDataBeforeSave($data, $record)),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function mutateFormDataBeforeCreate(array $data): array
    {
        if (! static::isSuperAdmin()) {
            $data['rumah_sakit_id'] = static::rumahSakitId();
        }
        return $data;
    }

    public static function mutateFormDataBeforeSave(array $data, KategoriArtikel $record): array
    {
        if (! static::isSuperAdmin()) {
            $data['rumah_sakit_id'] = static::rumahSakitId();
        }
        return $data;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageKategoriArtikels::route('/'),
        ];
    }
}
