<?php

namespace App\Filament\Resources;

use App\Enums\StatusLayanan;
use App\Filament\Resources\JadwalLayananHarianResource\Pages;
use App\Models\Dokter;
use App\Models\JadwalLayanan;
use App\Models\JadwalLayananHarian;
use App\Models\PoliKlinik;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class JadwalLayananHarianResource extends BaseResource
{
    protected static ?string $model = JadwalLayananHarian::class;

    protected static ?int $navigationSort = 2;
    protected static string|null $navigationGroup = 'Poliklinik / Rawat Jalan';
    protected static ?string $navigationIcon = 'fas-calendar-day';
    protected static ?string $navigationLabel = 'Jadwal Harian';
    protected static ?string $modelLabel = 'Jadwal Layanan Harian';
    protected static ?string $pluralModelLabel = 'Jadwal Layanan Harian';

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
                            ->afterStateUpdated(function (Forms\Set $set) {
                                $set('poliklinik_id', null);
                                $set('jadwal_layanan_id', null);
                            }),

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
                            ->live()
                            ->disabled(fn (Forms\Get $get) => static::isSuperAdmin() && ! $get('rumah_sakit_id'))
                            ->afterStateUpdated(fn (Forms\Set $set) => $set('jadwal_layanan_id', null)),
                    ])->columns(2),

                Forms\Components\Section::make('Detail Jadwal')
                    ->schema([
                        Forms\Components\DatePicker::make('tanggal')
                            ->required()
                            ->native(false)
                            ->displayFormat('d F Y'),

                        Forms\Components\Select::make('jadwal_layanan_id')
                            ->label('Berdasarkan Jadwal Master (opsional)')
                            ->options(function (Forms\Get $get) {
                                $poliId = $get('poliklinik_id');
                                if (! $poliId) return [];

                                return JadwalLayanan::where('poliklinik_id', $poliId)
                                    ->get()
                                    ->mapWithKeys(fn ($j) => [
                                        $j->id => $j->hari->getLabel() . ' | ' . ($j->nama_dokter ?? '-') . ' (' . optional($j->jam_mulai)->format('H:i') . '–' . optional($j->jam_selesai)->format('H:i') . ')',
                                    ]);
                            })
                            ->nullable()
                            ->searchable()
                            ->disabled(fn (Forms\Get $get) => ! $get('poliklinik_id')),

                        Forms\Components\Select::make('status_layanan')
                            ->options(StatusLayanan::class)
                            ->required()
                            ->native(false)
                            ->default(StatusLayanan::BUKA),

                        Forms\Components\TimePicker::make('jam_mulai')
                            ->label('Jam Mulai')
                            ->seconds(false)
                            ->nullable(),

                        Forms\Components\TimePicker::make('jam_selesai')
                            ->label('Jam Selesai')
                            ->seconds(false)
                            ->nullable(),
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
            ->defaultSort('tanggal', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('tanggal')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('poliklinik.nama')
                    ->label('Poliklinik')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('poliklinik.unitLayanan.rumahSakit.nama')
                    ->label('Rumah Sakit')
                    ->searchable()
                    ->sortable()
                    ->visible(fn () => static::isSuperAdmin()),

                Tables\Columns\TextColumn::make('nama_dokter')
                    ->label('Dokter')
                    ->searchable()
                    ->default('-'),

                Tables\Columns\TextColumn::make('jam_mulai')
                    ->label('Jam Mulai')
                    ->time('H:i'),

                Tables\Columns\TextColumn::make('jam_selesai')
                    ->label('Jam Selesai')
                    ->time('H:i'),

                Tables\Columns\TextColumn::make('status_layanan')
                    ->label('Status')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('tanggal')
                    ->form([
                        Forms\Components\DatePicker::make('dari')->label('Dari Tanggal')->native(false),
                        Forms\Components\DatePicker::make('sampai')->label('Sampai Tanggal')->native(false),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when($data['dari'], fn ($q, $v) => $q->whereDate('tanggal', '>=', $v))
                            ->when($data['sampai'], fn ($q, $v) => $q->whereDate('tanggal', '<=', $v));
                    }),

                Tables\Filters\SelectFilter::make('status_layanan')
                    ->options(StatusLayanan::class)
                    ->label('Filter Status'),

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
            // Halaman index diganti ke halaman custom spreadsheet harian
            'index' => Pages\JadwalLayananHarianPage::route('/'),
            'excel' => Pages\JadwalLayananHarianExcel::route('/excel'),
        ];
    }
}
