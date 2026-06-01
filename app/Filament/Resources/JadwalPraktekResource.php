<?php

namespace App\Filament\Resources;

use App\Enums\Hari;
use App\Filament\Resources\JadwalPraktekResource\Pages;
use App\Models\Dokter;
use App\Models\JadwalPraktek;
use App\Models\PoliKlinik;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class JadwalPraktekResource extends BaseResource
{
    protected static ?string $model = JadwalPraktek::class;

    protected static ?int $navigationSort = 2;
    protected static string|null $navigationGroup = 'Poliklinik / Rawat Jalan';
    protected static ?string $navigationIcon = 'fas-calendar-week';
    protected static ?string $navigationLabel = 'Jadwal Praktek';
    protected static ?string $modelLabel = 'Jadwal Praktek';
    protected static ?string $pluralModelLabel = 'Jadwal Praktek';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (static::isSuperAdmin()) {
            return $query;
        }

        return $query->whereHas('poliklinik.unitLayanan', function (Builder $q) {
            $q->where('rumah_sakit_id', static::rumahSakitId());
        });
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Lokasi Layanan')
                    ->schema([
                        Forms\Components\Select::make('rumah_sakit_id')
                            ->label('Rumah Sakit')
                            ->options(\App\Models\RumahSakit::pluck('nama', 'id'))
                            ->required(fn () => static::isSuperAdmin())
                            ->visible(fn () => static::isSuperAdmin())
                            ->live()
                            ->dehydrated(false)
                            ->afterStateUpdated(fn (Forms\Set $set) => $set('poliklinik_id', null)),

                        Forms\Components\Select::make('poliklinik_id')
                            ->label('Poliklinik')
                            ->options(function (Forms\Get $get) {
                                $rsId = static::isSuperAdmin()
                                    ? $get('rumah_sakit_id')
                                    : static::rumahSakitId();

                                if (! $rsId) return [];

                                return PoliKlinik::whereHas('unitLayanan', fn ($q) => $q->where('rumah_sakit_id', $rsId))
                                    ->where('aktif', true)
                                    ->pluck('nama', 'id');
                            })
                            ->required()
                            ->searchable()
                            ->disabled(fn (Forms\Get $get) => static::isSuperAdmin() && ! $get('rumah_sakit_id')),
                    ])->columns(2),

                Forms\Components\Section::make('Detail Jadwal')
                    ->schema([
                        Forms\Components\Select::make('hari')
                            ->options(Hari::class)
                            ->required()
                            ->native(false),

                        Forms\Components\TimePicker::make('waktu_mulai')
                            ->label('Jam Mulai')
                            ->seconds(false)
                            ->nullable(),

                        Forms\Components\TimePicker::make('waktu_selesai')
                            ->label('Jam Selesai')
                            ->seconds(false)
                            ->nullable(),

                        Forms\Components\Toggle::make('sesuai_perjanjian')
                            ->label('Sesuai Perjanjian')
                            ->default(false),
                    ])->columns(2),

                Forms\Components\Section::make('Dokter')
                    ->schema([
                        Forms\Components\Select::make('dokter_id')
                            ->label('Dokter (opsional)')
                            ->options(function (Forms\Get $get) {
                                $rsId = static::isSuperAdmin()
                                    ? $get('rumah_sakit_id')
                                    : static::rumahSakitId();

                                if (! $rsId) return [];

                                return Dokter::where('rumah_sakit_id', $rsId)
                                    ->where('aktif', true)
                                    ->pluck('nama', 'id');
                            })
                            ->nullable()
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(function (Forms\Set $set, ?int $state) {
                                if ($state) {
                                    $set('nama_dokter', Dokter::find($state)?->nama);
                                }
                            }),

                        Forms\Components\TextInput::make('nama_dokter')
                            ->label('Nama Dokter')
                            ->maxLength(255)
                            ->nullable(),

                        Forms\Components\Textarea::make('catatan')
                            ->nullable()
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('hari')
            ->columns([
                Tables\Columns\TextColumn::make('poliklinik.nama')
                    ->label('Poliklinik')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('poliklinik.unitLayanan.rumahSakit.nama')
                    ->label('Rumah Sakit')
                    ->searchable()
                    ->sortable()
                    ->visible(fn () => static::isSuperAdmin()),

                Tables\Columns\TextColumn::make('hari')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('nama_dokter')
                    ->label('Dokter')
                    ->searchable()
                    ->default('-'),

                Tables\Columns\TextColumn::make('waktu_mulai')
                    ->label('Jam Mulai')
                    ->time('H:i'),

                Tables\Columns\TextColumn::make('waktu_selesai')
                    ->label('Jam Selesai')
                    ->time('H:i'),

                Tables\Columns\IconColumn::make('sesuai_perjanjian')
                    ->label('Perjanjian')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('hari')
                    ->options(Hari::class)
                    ->label('Filter Hari'),

                Tables\Filters\SelectFilter::make('poliklinik_id')
                    ->relationship('poliklinik', 'nama')
                    ->label('Filter Poliklinik')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->mutateRecordDataUsing(function (array $data): array {
                        $poli = PoliKlinik::with('unitLayanan')->find($data['poliklinik_id']);
                        if ($poli) {
                            $data['rumah_sakit_id'] = $poli->unitLayanan->rumah_sakit_id;
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
            'index' => Pages\JadwalPraktekPage::route('/'),
            'excel' => Pages\JadwalPraktekExcel::route('/excel'),
        ];
    }
}
