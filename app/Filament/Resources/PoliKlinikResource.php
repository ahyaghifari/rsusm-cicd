<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PoliKlinikResource\Pages;
use App\Models\PoliKlinik;
use App\Models\UnitLayanan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class PoliKlinikResource extends Resource
{
    protected static ?string $model = PoliKlinik::class;

    protected static ?int $navigationSort = 1;
    protected static string | null $navigationGroup = 'Poliklinik / Rawat Jalan';
    protected static ?string $navigationIcon = 'fas-house-chimney-medical';
    
    protected static ?string $slug = 'poliklinik';
    
    protected static ?string $label = 'Poliklinik';

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
                    ->unique(PoliKlinik::class, 'slug', ignoreRecord: true),

                // Step 1: Pilih Rumah Sakit terlebih dahulu (Tidak disimpan ke database)
                Forms\Components\Select::make('rumah_sakit_id')
                    ->label('Rumah Sakit')
                    ->relationship('unitLayanan.rumahSakit', 'nama') // Membantu memuat data awal saat Edit
                    ->options(\App\Models\RumahSakit::pluck('nama', 'id'))
                    ->required()
                    ->live()
                    ->dehydrated(false) // Memastikan field ini tidak ikut disimpan ke tabel poliklinik
                    ->afterStateUpdated(fn (Set $set) => $set('unit_layanan_id', null)), // Reset unit jika RS berubah

                // Step 2: Pilih Unit Layanan yang difilter berdasarkan Rumah Sakit yang dipilih
                Forms\Components\Select::make('unit_layanan_id')
                    ->label('Unit Layanan')
                    ->required()
                    ->options(function (Forms\Get $get) {
                        $rumahSakitId = $get('rumah_sakit_id');
                        
                        // Jika Rumah Sakit belum dipilih, tampilkan kosong atau semua data jika saat halaman edit dimuat
                        if (! $rumahSakitId) {
                            return [];
                        }

                        // Mengambil Unit Layanan yang berafiliasi dengan rumah_sakit_id tersebut
                        return UnitLayanan::where('rumah_sakit_id', $rumahSakitId)->pluck('nama', 'id');
                    })
                    ->disabled(fn (Forms\Get $get) => !$get('rumah_sakit_id')),

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
            ->columns([
                Tables\Columns\ImageColumn::make('gambar'),
                Tables\Columns\TextColumn::make('nama')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable(),
                Tables\Columns\TextColumn::make('unitLayanan.nama')
                    ->label('Unit Layanan')
                    ->sortable(),
                Tables\Columns\IconColumn::make('aktif')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->mutateRecordDataUsing(function (array $data): array {
                        // Mengisi nilai awal 'rumah_sakit_id' pada modal Edit berdasarkan unit_layanan yang tersimpan
                        $unitLayanan = UnitLayanan::find($data['unit_layanan_id']);
                        if ($unitLayanan) {
                            $data['rumah_sakit_id'] = $unitLayanan->rumah_sakit_id;
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
            'index' => Pages\ManagePoliKliniks::route('/'),
        ];
    }
}