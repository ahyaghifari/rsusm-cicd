<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UnitLayananResource\Pages;
use App\Models\UnitLayanan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class UnitLayananResource extends BaseResource
{
    protected static ?string $model = UnitLayanan::class;

    protected static ?int $navigationSort = 2;
    protected static string | null $navigationGroup = 'Poliklinik / Rawat Jalan';
    protected static ?string $navigationIcon = 'fas-building-un';
    
    protected static ?string $navigationLabel = 'Unit Layanan';
    
    protected static ?string $modelLabel = 'Unit Layanan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Pilihan Rumah Sakit tujuan relasi
                Forms\Components\Select::make('rumah_sakit_id')
                    ->relationship('rumahSakit', 'nama')
                    ->required()
                    ->searchable()
                    ->preload(),
                
                // Input Nama Unit Layanan
                Forms\Components\TextInput::make('nama')
                    ->required()
                    ->maxLength(255),
                
                // Media upload image untuk mengupload file gambar
                Forms\Components\FileUpload::make('gambar')
                    ->image()
                    ->directory('unit-layanan')
                    ->maxSize(2048)
                    ->nullable(),
                
                // Status Aktif data
                Forms\Components\Toggle::make('aktif')
                    ->default(true)
                    ->required(),

                // Deskripsi Unit Layanan
                Forms\Components\Textarea::make('deskripsi')
                    ->nullable()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Menampilkan nama Rumah Sakit melalui relasi
                Tables\Columns\TextColumn::make('rumahSakit.nama')
                    ->label('Rumah Sakit')
                    ->sortable()
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('nama')
                    ->searchable()
                    ->sortable(),
                
                // Menampilkan thumbnail media gambar
                Tables\Columns\ImageColumn::make('gambar'),
                
                // Menampilkan status aktif berupa ikon boolean
                Tables\Columns\IconColumn::make('aktif')
                    ->boolean()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('rumah_sakit_id')
                    ->relationship('rumahSakit', 'nama')
                    ->label('Filter Rumah Sakit'),
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
            'index' => Pages\ManageUnitLayanan::route('/'),
        ];
    }

    public static function canAccess(): bool
    {
        return static::isSuperAdmin();
    }
}