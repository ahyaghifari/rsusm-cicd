<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LayananUnggulanResource\Pages;
use App\Models\LayananUnggulan;
use Filament\Forms;
use Filament\Forms\Form;

use Filament\Tables;
use Filament\Tables\Table;

class LayananUnggulanResource extends BaseRumahSakitResource
{
    protected static ?string $model = LayananUnggulan::class;

    protected static ?int $navigationSort = 2;
    protected static string | null $navigationGroup = 'Layanan';
    protected static ?string $navigationIcon = 'heroicon-o-star';
    
    protected static ?string $navigationLabel = 'Layanan Unggulan';
    
    protected static ?string $modelLabel = 'Layanan Unggulan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                static::rsFormField(),

                Forms\Components\TextInput::make('nama')
                    ->required()
                    ->maxLength(255)
                    ->label('Nama Layanan'),

                // Media Upload Image untuk Gambar
                Forms\Components\FileUpload::make('gambar')
                    ->image() // Validasi file harus berupa gambar
                    ->directory('layanan-unggulan') // Folder penyimpanan di storage/app/public
                    ->required()
                    ->label('Gambar Layanan'),

                Forms\Components\RichEditor::make('deskripsi')
                    ->required()
                    ->label('Deskripsi'),

                Forms\Components\Toggle::make('aktif')
                    ->default(true)
                    ->required()
                    ->label('Status Aktif'),
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
                    ->sortable()
                    ->label('Nama Layanan'),

                // Menampilkan preview gambar berukuran kecil di tabel
                Tables\Columns\ImageColumn::make('gambar')
                    ->circular()
                    ->label('Gambar'),

                Tables\Columns\IconColumn::make('aktif')
                    ->boolean()
                    ->sortable()
                    ->label('Aktif'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Anda bisa menambahkan filter status aktif jika diperlukan di sini
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
            // Karena menggunakan flag --simple, routing diarahkan ke ManageLayananUnggulan
            'index' => Pages\ManageLayananUnggulans::route('/'),
        ];
    }
}