<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RawatInapResource\Pages;
use App\Filament\Resources\RawatInapResource\RelationManagers;
use App\Models\Gedung;
use App\Models\KelasRawatInap;
use App\Models\RawatInap;
use App\Models\RumahSakit;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RawatInapResource extends BaseRumahSakitResource
{
    protected static ?string $model = RawatInap::class;

    protected static string | null $navigationGroup = 'Rawat Inap';

    protected static ?string $navigationIcon = 'heroicon-o-identification';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Dasar')
                    ->schema([
                        Forms\Components\Select::make('rumah_sakit_id')
                            ->relationship('rumahSakit', 'nama')
                            ->preload()
                            ->live()
                            ->visible(fn () => static::isSuperAdmin())
                            ->required(fn () => static::isSuperAdmin())
                            ->default(fn () => static::isSuperAdmin() ? null : static::rumahSakitId()),
                        Forms\Components\Select::make('gedung_id')
                            ->label('Gedung')
                            ->options(function (Forms\Get $get) {


                                $rumahSakitId = static::isSuperAdmin()
                                    ? $get('rumah_sakit_id')
                                    : static::rumahSakitId();

                                if (! $rumahSakitId) {
                                    return [];
                                }

                                return Gedung::query()
                                    ->where('rumah_sakit_id', $rumahSakitId)
                                    ->pluck('nama', 'id');
                            })
                            ->disabled(fn (Forms\Get $get) => static::isSuperAdmin() && !$get('rumah_sakit_id'))
                            ->required(function (Forms\Get $get){
                                $rumahSakitId = static::isSuperAdmin()
                                    ? $get('rumah_sakit_id')
                                    : static::rumahSakitId();

                                if (! $rumahSakitId) {
                                    return false;
                                }

                                return Gedung::where(
                                    'rumah_sakit_id',
                                    $rumahSakitId
                                )->exists();
                            }),
                        Forms\Components\TextInput::make('nama')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('kelas_rawat_inap_id')
                            ->label('Kelas')
                            ->options(function (Forms\Get $get) {
                                $rumahSakitId = static::isSuperAdmin()
                                    ? $get('rumah_sakit_id')
                                    : static::rumahSakitId();

                                if (! $rumahSakitId) {
                                    return [];
                                }

                                return KelasRawatInap::where('rumah_sakit_id', $rumahSakitId)
                                    ->pluck('nama', 'id');
                            })
                            ->required()
                            ->searchable()
                            ->disabled(fn (Forms\Get $get) => static::isSuperAdmin() && ! $get('rumah_sakit_id'))
                            ->helperText('Kelola daftar kelas di menu "Kelas Rawat Inap".'),
                    ])->columns(2),

                Forms\Components\Section::make('Kapasitas & Tarif')
                    ->schema([
                        Forms\Components\TextInput::make('harga')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->placeholder('0.00'),
                        Forms\Components\TextInput::make('kapasitas')
                            ->required()
                            ->numeric()
                            ->placeholder('Jumlah Bed Pasien'),
                    ])->columns(2),

                Forms\Components\Section::make('Fasilitas & Tampilan')
                    ->schema([
                        Forms\Components\FileUpload::make('thumbnail')
                            ->image()
                            ->directory('rawat-inap/thumbnail')
                            ->disk('public')
                            ->columnSpanFull(),

                        Forms\Components\FileUpload::make('foto_360')
                            ->label('Foto 360°')
                            ->image()
                            ->imageEditorAspectRatios(['2:1'])
                            ->maxSize(10240)
                            ->directory('rawat-inap/foto-360')
                            ->disk('public')
                            ->helperText('Foto panorama equirectangular (rasio 2:1) dari kamera 360°. Kosongkan kalau kamar ini belum difoto ulang — tombol "Preview 360°" otomatis tidak tampil di halaman publik.')
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Pengaturan Tambahan')
                    ->schema([
                        Forms\Components\Toggle::make('aktif')
                            ->required()
                            ->default(true),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('thumbnail')
                    ->disk('public')
                    ->circular(),
                Tables\Columns\TextColumn::make('nama')
                    ->searchable()
                    ->sortable(),
                static::rsTableColumn(),
                Tables\Columns\TextColumn::make('gedung.nama')
                    ->label('Gedung')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('kelasRawatInap.nama')
                    ->label('Kelas')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('harga')
                    ->money('IDR', locale: 'id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('kapasitas')
                    ->numeric()
                    ->sortable()
                    ->suffix(' Bed'),
                Tables\Columns\IconColumn::make('aktif')
                    ->boolean()
                    ->sortable(),
                // Tables\Columns\TextColumn::make('sort_order')
                //     ->numeric()
                //     ->sortable(),
            ])
            ->defaultSort('sort_order', 'asc')
            ->reorderable('sort_order')
            ->filters([
                static::rsTableFilter()->searchable()->preload(),
                Tables\Filters\SelectFilter::make('gedung_id')
                    ->relationship('gedung', 'nama')
                    ->label('Gedung')
                    ->searchable()
                    ->preload(),
                Tables\Filters\TernaryFilter::make('aktif')
                    ->label('Status Aktif'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
            // ->bulkActions([
            //     Tables\Actions\BulkActionGroup::make([
            //         Tables\Actions\DeleteBulkAction::make(),
            //     ]),
            // ]);
    }

    public static function mutateFormDataBeforeCreate(array $data): array
    {
        if (! static::isSuperAdmin()) {
            $data['rumah_sakit_id'] = static::rumahSakitId();
        }

        return $data;
    }

    public static function mutateFormDataBeforeSave(array $data): array
    {
        if (! static::isSuperAdmin()) {
            $data['rumah_sakit_id'] = static::rumahSakitId();
        }

        return $data;
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\GambarRelationManager::class,
            RelationManagers\FasilitasRawatInapRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRawatInaps::route('/'),
            'create' => Pages\CreateRawatInap::route('/create'),
            'edit' => Pages\EditRawatInap::route('/{record}/edit'),
        ];
    }
}
