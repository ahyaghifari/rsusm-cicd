<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FasilitasRawatInapResource\Pages;
use App\Filament\Resources\FasilitasRawatInapResource\RelationManagers;
use App\Models\FasilitasRawatInap;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FasilitasRawatInapResource extends Resource
{
    protected static ?string $model = FasilitasRawatInap::class;

    protected static ?string $navigationLabel = 'Fasilitas';
    protected static ?int $navigationSort = 2;
    protected static string | null $navigationGroup = 'Rawat Inap';
    protected static ?string $navigationIcon = 'fas-house-medical';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('rawat_inap_id')
                    ->relationship('rawatInap', 'nama')
                    ->searchable()
                    ->preload()
                    ->required(),
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
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('rawat_inap_id')
                    ->relationship('rawatInap', 'nama')
                    ->label('Rawat Inap')
                    ->searchable()
                    ->preload(),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageFasilitasRawatInaps::route('/'),
        ];
    }
}
