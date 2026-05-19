<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RumahSakitResource\Pages;
use App\Models\RumahSakit;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class RumahSakitResource extends Resource
{
    protected static ?string $model = RumahSakit::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nama')
                    ->required()
                    ->maxLength(100),
                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->maxLength(100),
                Forms\Components\TextInput::make('lokasi')
                    ->required()
                    ->maxLength(100),
                Forms\Components\Textarea::make('alamat')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('no_emergency')
                    ->maxLength(20),
                Forms\Components\TextInput::make('no_hotline')
                    ->maxLength(20),
                Forms\Components\FileUpload::make('gambar')
                    ->image()
                    ->directory('rumah-sakit/gambar'),
                Forms\Components\FileUpload::make('logo')
                    ->image()
                    ->directory('rumah-sakit/logo'),
                Forms\Components\Toggle::make('aktif')
                    ->required()
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable(),
                Tables\Columns\TextColumn::make('lokasi')
                    ->searchable(),
                Tables\Columns\TextColumn::make('no_emergency')
                    ->searchable(),
                Tables\Columns\TextColumn::make('no_hotline')
                    ->searchable(),
                Tables\Columns\ImageColumn::make('gambar'),
                Tables\Columns\ImageColumn::make('logo'),
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
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRumahSakits::route('/'),
            'create' => Pages\CreateRumahSakit::route('/create'),
            'edit' => Pages\EditRumahSakit::route('/{record}/edit'),
        ];
    }
}
