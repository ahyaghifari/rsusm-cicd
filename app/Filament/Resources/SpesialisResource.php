<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SpesialisResource\Pages;
use App\Filament\Resources\SpesialisResource\RelationManagers;
use App\Models\Spesialis;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SpesialisResource extends BaseRumahSakitResource
{
    protected static ?string $model = Spesialis::class;

    protected static ?int $navigationSort = 3;
    protected static string | null $navigationGroup = 'Dokter';
    protected static ?string $navigationIcon = 'fas-stethoscope';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('rumah_sakit_id')
                    ->relationship('rumahSakit', 'nama')
                    ->required(fn () => static::isSuperAdmin())
                    ->visible(fn () => static::isSuperAdmin())
                    ->default(1),
                Forms\Components\TextInput::make('nama')
                    ->required()
                    ->maxLength(100)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) => $operation === 'create' ? $set('slug', \Illuminate\Support\Str::slug($state)) : null),
                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->maxLength(100)
                    ->unique(Spesialis::class, ignoreRecord: true),
                Forms\Components\FileUpload::make('logo')
                    ->image()
                    ->directory('spesialis/logo')
                    ->disk('public'),
                Forms\Components\Toggle::make('aktif')
                    ->required()
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('rumahSakit.nama')
                    ->visible(fn () => static::isSuperAdmin())
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nama')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\ImageColumn::make('logo')
                    ->disk('public'),
                Tables\Columns\IconColumn::make('aktif')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->mutateFormDataUsing(function (array $data): array {

                        if (! static::isSuperAdmin()) {

                            $data['rumah_sakit_id']
                                = static::rumahSakitId();

                        }

                        return $data;
                    }),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageSpesialis::route('/'),
        ];
    }
}
