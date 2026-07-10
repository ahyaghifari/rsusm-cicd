<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PoliKlinikResource\Pages;
use App\Models\PoliKlinik;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class PoliKlinikResource extends BaseResource
{
    protected static ?string $model = PoliKlinik::class;

    protected static ?int $navigationSort = 1;
    protected static string | null $navigationGroup = 'Poliklinik / Rawat Jalan';
    protected static ?string $navigationIcon = 'heroicon-o-heart';

    protected static ?string $slug = 'poliklinik';

    protected static ?string $label = 'Poliklinik';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->withTrashed();

        if (static::isSuperAdmin()) {
            return $query;
        }

        return $query->where('rumah_sakit_id', static::rumahSakitId());
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nama')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state))),

                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->disabled()
                    ->dehydrated()
                    ->maxLength(255)
                    ->unique(
                        table: 'poliklinik',
                        column: 'slug',
                        ignoreRecord: true,
                        modifyRuleUsing: function (\Illuminate\Validation\Rules\Unique $rule, Forms\Get $get) {
                            $rsId = $get('rumah_sakit_id') ?? static::rumahSakitId();
                            return $rsId ? $rule->where('rumah_sakit_id', $rsId) : $rule;
                        }
                    ),

                Forms\Components\Select::make('rumah_sakit_id')
                    ->label('Rumah Sakit')
                    ->options(\App\Models\RumahSakit::pluck('nama', 'id'))
                    ->required()
                    ->live()
                    ->visible(fn () => static::isSuperAdmin())
                    ->default(fn () => static::isSuperAdmin() ? null : static::rumahSakitId()),

                Forms\Components\FileUpload::make('gambar')
                    ->image()
                    ->directory('poliklinik')
                    ->nullable(),

                Forms\Components\Textarea::make('deskripsi')
                    ->required()
                    ->columnSpanFull(),

                Forms\Components\Toggle::make('aktif')
                    ->required()
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->defaultSort('sort_order', 'asc')
            ->columns([
                Tables\Columns\ImageColumn::make('gambar'),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Urutan')
                    ->sortable(),
                Tables\Columns\TextColumn::make('nama')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable(),
                Tables\Columns\TextColumn::make('rumahSakit.nama')
                    ->label('Rumah Sakit')
                    ->sortable()
                    ->visible(fn () => static::isSuperAdmin()),
                Tables\Columns\IconColumn::make('aktif')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('rumah_sakit_id')
                    ->relationship('rumahSakit', 'nama')
                    ->label('Rumah Sakit')
                    ->visible(fn () => static::isSuperAdmin()),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManagePoliKliniks::route('/'),
        ];
    }
}
