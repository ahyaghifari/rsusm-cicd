<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GambarRawatInapResource\Pages;
use App\Filament\Resources\GambarRawatInapResource\RelationManagers;
use App\Models\GambarRawatInap;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class GambarRawatInapResource extends Resource
{
    protected static ?string $model = GambarRawatInap::class;

    protected static ?string $navigationLabel = 'Gambar';
    protected static ?int $navigationSort = 3;
    protected static string | null $navigationGroup = 'Rawat Inap';
    protected static ?string $navigationIcon = 'fas-image';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detail Gambar')
                    ->schema([
                        Forms\Components\Select::make('rawat_inap_id')
                            ->relationship('rawatInap', 'nama')
                            ->searchable()
                            ->preload()
                            ->required(),
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
                        Forms\Components\TextInput::make('sort_order')
                            ->required()
                            ->numeric()
                            ->default(0),
                        Forms\Components\Toggle::make('aktif')
                            ->required()
                            ->default(true),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('gambar')
                    ->disk('public')
                    ->circular(),
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
                    ->relationship('rawatInap', 'nama')
                    ->label('Kamar Rawat Inap')
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGambarRawatInaps::route('/'),
            'create' => Pages\CreateGambarRawatInap::route('/create'),
            'edit' => Pages\EditGambarRawatInap::route('/{record}/edit'),
        ];
    }
}
