<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KontakResource\Pages;
use App\Models\Kontak;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class KontakResource extends BaseRumahSakitResource
{
    protected static ?string $model = Kontak::class;

    protected static ?int $navigationSort = 2;
    protected static ?string $navigationIcon = 'heroicon-o-phone';

    protected static ?string $navigationLabel = 'Kontak';

    protected static ?string $modelLabel = 'Kontak';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('rumah_sakit_id')
                    ->relationship('rumahSakit', 'nama')
                    ->required(fn () => static::isSuperAdmin())
                    ->visible(fn () => static::isSuperAdmin())
                    ->preload(),

                Forms\Components\Select::make('kategori')
                    ->options([
                        'OPERASIONAL' => 'Operasional',
                        'SOSIAL MEDIA' => 'Sosial Media',
                    ])
                    ->required(),

                Forms\Components\TextInput::make('label')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('value')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Textarea::make('link')
                    ->rows(2)
                    ->nullable(),

                Forms\Components\FileUpload::make('gambar')
                    ->image()
                    ->directory('kontak/gambar')
                    ->maxSize(2048)
                    ->nullable(),

                Forms\Components\Textarea::make('logo')
                    ->rows(10)
                    ->nullable(),

                Forms\Components\Toggle::make('aktif')
                    ->default(true)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('rumahSakit.nama')
                    ->label('Rumah Sakit')
                    ->sortable()
                    ->searchable()
                    ->visible(fn () => static::isSuperAdmin())
                    ,

                Tables\Columns\TextColumn::make('kategori')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'OPERASIONAL' => 'success',
                        'SOSIAL MEDIA' => 'info',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('label')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('value')
                    ->searchable(),

                Tables\Columns\ImageColumn::make('gambar')
                    ->label('Gambar'),

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
                    ->label('Filter Rumah Sakit')->visible(fn () => static::isSuperAdmin()),

                Tables\Filters\SelectFilter::make('kategori')
                    ->options([
                        'OPERASIONAL' => 'Operasional',
                        'SOSIAL MEDIA' => 'Sosial Media',
                    ])
                    ->label('Filter Kategori'),
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
            'index' => Pages\ManageKontak::route('/'),
        ];
    }
}
