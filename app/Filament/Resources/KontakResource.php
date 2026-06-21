<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KontakResource\Pages;
use App\Models\Kontak;
use Filament\Forms;
use Filament\Forms\Form;

use Filament\Tables;
use Filament\Tables\Table;

class KontakResource extends BaseRumahSakitResource
{
    protected static ?string $model = Kontak::class;

    protected static ?int $navigationSort = 4;
    protected static ?string $navigationIcon = 'heroicon-o-phone';

    protected static ?string $navigationLabel = 'Kontak';

    protected static ?string $modelLabel = 'Kontak';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                static::rsFormField(),

                Forms\Components\Select::make('kategori')
                    ->options([
                        'PENDAFTARAN' => 'Pendaftaran (tampil di halaman jadwal)',
                        'RAWAT INAP' => 'Rawat Inap (tampil di halaman ketersediaan rawat inap)',
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

                // Forms\Components\Textarea::make('logo')
                //     ->rows(10)
                //     ->nullable(),

                Forms\Components\Toggle::make('aktif')
                    ->default(true)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->defaultSort('sort_order', 'asc')
            ->columns([
                static::rsTableColumn(),

                Tables\Columns\TextColumn::make('kategori')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'PENDAFTARAN' => 'warning',
                        'RAWAT INAP' => 'primary',
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
                static::rsTableFilter(),

                Tables\Filters\SelectFilter::make('kategori')
                    ->options([
                        'PENDAFTARAN' => 'Pendaftaran',
                        'RAWAT INAP' => 'Rawat Inap',
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
            'index' => Pages\ManageKontak::route('/'),
        ];
    }
}
