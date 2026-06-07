<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FasilitasPendukungResource\Pages;
use App\Models\FasilitasPendukung;
use Filament\Forms;
use Filament\Forms\Form;

use Filament\Tables;
use Filament\Tables\Table;

class FasilitasPendukungResource extends BaseRumahSakitResource
{
    protected static ?string $model = FasilitasPendukung::class;

    protected static ?int $navigationSort = 3;
    protected static string | null $navigationGroup = 'Layanan';
    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    
    protected static ?string $navigationLabel = 'Fasilitas Pendukung';
    
    protected static ?string $modelLabel = 'Fasilitas Pendukung';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                static::rsFormField(),
                
                // Text input maksimal 255 karakter
                Forms\Components\TextInput::make('nama')
                    ->required()
                    ->maxLength(255),
                
                // Media upload image agar user bisa mengunggah file gambar
                Forms\Components\FileUpload::make('gambar')
                    ->image()
                    ->directory('fasilitas-pendukung')
                    ->maxSize(2048),
                
                // Toggle aktif dengan nilai bawaan true
                Forms\Components\Toggle::make('aktif')
                    ->default(true)
                    ->required(),

                // Textarea untuk deskripsi yang mengambil ruang penuh baris
                Forms\Components\Textarea::make('deskripsi')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->defaultSort('sort_order', 'asc')
            ->columns([
                static::rsTableColumn(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Urutan')
                    ->sortable(),

                Tables\Columns\TextColumn::make('nama')
                    ->searchable()
                    ->sortable(),
                
                // Menampilkan thumbnail gambar
                Tables\Columns\ImageColumn::make('gambar'),
                
                // Kolom status aktif berupa ikon boolean
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
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                ->mutateFormDataUsing(function (array $data): array {

                        if (! static::isSuperAdmin()) {

                            $data['rumah_sakit_id'] = static::rumahSakitId();

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
            'index' => Pages\ManageFasilitasPendukung::route('/'),
        ];
    }
}