<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DokterResource\Pages;
use App\Filament\Resources\DokterResource\RelationManagers;
use App\Models\Dokter;
use App\Models\RumahSakit;
use App\Models\Spesialis;
use App\Services\AntrianApiClient;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DokterResource extends BaseRumahSakitResource
{
    protected static ?string $model = Dokter::class;

    protected static ?int $navigationSort = 1;
    protected static string | null $navigationGroup = 'Dokter';
    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Dokter')
                    ->schema([
                        Forms\Components\TextInput::make('nama')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, Forms\Set $set) => $set('slug', \Illuminate\Support\Str::slug($state))),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(Dokter::class, 'slug', ignoreRecord: true, modifyRuleUsing: function ($rule, Forms\Get $get) {
                                return $rule->where('rumah_sakit_id', static::isSuperAdmin() ? $get('rumah_sakit_id') : static::rumahSakitId());
                            }),
                        Forms\Components\FileUpload::make('foto')
                            ->image()
                            ->directory('dokter/foto')
                            ->disk('public'),
                        Forms\Components\Textarea::make('deskripsi')
                            ->rows(3)
                            ->maxLength(1000),
                        Forms\Components\Textarea::make('kuota_pasien')
                            ->label('Info Kuota / Ketersediaan Pasien')
                            ->rows(2)
                            ->nullable()
                            ->helperText('Tampil di profil dokter (publik) sebagai info kuota rawat jalan. Kosongkan jika tidak ingin ditampilkan.'),
                        Forms\Components\Toggle::make('aktif')
                            ->required()
                            ->default(true),
                    ])->columnSpan(1),

                Forms\Components\Section::make('Relasi & Detail Medis')
                    ->schema([
                        Forms\Components\Select::make('rumah_sakit_id')
                            ->relationship('rumahSakit', 'nama')
                            ->required(fn () => static::isSuperAdmin())
                            ->visible(fn() => static::isSuperAdmin())
                            ->live(condition: static::isSuperAdmin())
                            ->afterStateUpdated(fn (Forms\Set $set) => $set('spesialis_id', null)),
                        Forms\Components\Select::make('spesialis_id')
                            ->label('Spesialis')
                            ->options(function (Forms\Get $get){
                                $rumahSakitId = static::isSuperAdmin()
                                    ? $get('rumah_sakit_id')
                                    : static::rumahSakitId();

                                if (! $rumahSakitId) {
                                    return [];
                                }

                                return Spesialis::query()
                                    ->where('rumah_sakit_id', $rumahSakitId)
                                    ->pluck('nama', 'id');
                            })
                            ->helperText('Kosongkan jika dokter ini adalah dokter umum (tanpa spesialisasi tertentu) — akan ditampilkan sebagai "Dokter Umum".')
                            ->disabled(fn (Forms\Get $get) => static::isSuperAdmin() && !$get('rumah_sakit_id'))
                            ->searchable()
                            ->preload(),
                        Forms\Components\RichEditor::make('pendidikan')
                            ->placeholder('Masukkan riwayat pendidikan dokter...'),
                        Forms\Components\RichEditor::make('pelatihan')
                            ->placeholder('Masukkan riwayat pelatihan dokter...'),
                    ])->columnSpan(1),

                Forms\Components\Section::make('API Antrian')
                    ->description('Hubungkan dokter ini ke sistem live antrian poliklinik eksternal. Base URL API-nya mengikuti "Link Antrian" milik rumah sakit (kelola di menu Rumah Sakit).')
                    ->collapsible()
                    ->schema([
                        Forms\Components\TextInput::make('nomor_poli_antrian')
                            ->label('Nomor Poli Antrian')
                            ->numeric()
                            ->nullable()
                            ->live()
                            ->helperText('Identifier dokter ini di sistem antrian eksternal. Klik "Tes" untuk memastikan nomornya benar sebelum disimpan.')
                            ->suffixAction(
                                Forms\Components\Actions\Action::make('testAntrian')
                                    ->label('Tes')
                                    ->icon('heroicon-m-signal')
                                    ->action(function (Forms\Get $get) {
                                        $nomor = $get('nomor_poli_antrian');

                                        if (! $nomor) {
                                            Notification::make()
                                                ->title('Isi nomor poli antrian dulu')
                                                ->warning()
                                                ->send();
                                            return;
                                        }

                                        $rumahSakitId = static::isSuperAdmin()
                                            ? $get('rumah_sakit_id')
                                            : static::rumahSakitId();

                                        $baseUrl = $rumahSakitId
                                            ? RumahSakit::find($rumahSakitId)?->link_antrian
                                            : null;

                                        if (! $baseUrl) {
                                            Notification::make()
                                                ->title('Link Antrian rumah sakit belum diisi')
                                                ->body('Isi "Link Antrian" di menu Rumah Sakit terlebih dahulu sebelum menguji nomor ini.')
                                                ->warning()
                                                ->send();
                                            return;
                                        }

                                        $data = app(AntrianApiClient::class)->fetch($baseUrl, $nomor);

                                        if (! $data) {
                                            Notification::make()
                                                ->title('Gagal mengambil data antrian')
                                                ->body('Periksa nomor poli, atau pastikan Link Antrian rumah sakit benar.')
                                                ->danger()
                                                ->send();
                                            return;
                                        }

                                        Notification::make()
                                            ->title('Respons API Antrian')
                                            ->body(
                                                "ID: " . ($data['id'] ?? '-') . "\n" .
                                                "Nama Poli: " . ($data['nama_poli'] ?? '-') . "\n" .
                                                "Nama Dokter: " . ($data['nama_dokter'] ?? '-') . "\n" .
                                                "Status: " . ($data['status'] ?? '-')
                                            )
                                            ->success()
                                            ->persistent()
                                            ->send();
                                    })
                            ),
                    ])
                    ->columnSpanFull(),

                // Forms\Components\Section::make('Konsultasi Chat')
                //     ->description('Pengaturan untuk fitur Tanya Dokter (chat real-time bersesi).')
                //     ->schema([
                //         Forms\Components\Toggle::make('dapat_konsultasi')
                //             ->label('Termasuk Dokter Telemedicine')
                //             ->helperText('Tentukan apakah dokter ini ikut serta dalam layanan Tanya Dokter. Diatur oleh admin — biasanya tidak sering berubah.')
                //             ->live()
                //             ->default(false),
                //         // Forms\Components\Toggle::make('tersedia_konsultasi')
                //         //     ->label('Status: Tersedia untuk Sesi Chat Sekarang')
                //         //     ->helperText('Status real-time — aktifkan saat dokter sedang online & siap menerima sesi chat dari pasien. Bisa berubah-ubah setiap hari.')
                //         //     ->disabled(fn (Forms\Get $get) => ! $get('dapat_konsultasi'))
                //         //     ->default(false),
                //         Forms\Components\TextInput::make('durasi_sesi_menit')
                //             ->label('Durasi Sesi (menit)')
                //             ->numeric()
                //             ->default(30)
                //             ->required(),
                //         Forms\Components\Select::make('user_id')
                //             ->label('Akun Login Dokter (opsional)')
                //             ->relationship('user', 'name')
                //             ->searchable()
                //             ->preload()
                //             ->helperText('Hubungkan ke akun User jika dokter ini akan login & membalas chat sendiri. Kosongkan jika hanya admin/CS yang akan membalas atas nama dokter.'),
                //     ])
                //     ->columnSpanFull(),
            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('foto')
                    ->disk('public')
                    ->circular(),
                Tables\Columns\TextColumn::make('nama')
                    ->searchable()
                    ->sortable(),
                static::rsTableColumn(),
                Tables\Columns\TextColumn::make('spesialis.nama')
                    ->label('Spesialis')
                    ->placeholder('Dokter Umum')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('aktif')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                static::rsTableFilter(),
                Tables\Filters\SelectFilter::make('spesialis_id')
                    ->relationship('spesialis', 'nama')
                    ->label('Spesialis'),
            ])
            ->filters([
                static::rsTableFilter(),
                Tables\Filters\SelectFilter::make('spesialis_id')
                    ->relationship('spesialis', 'nama')
                    ->label('Spesialis'),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
            ]);
            // ->bulkActions([
            //     Tables\Actions\BulkActionGroup::make([
            //         Tables\Actions\DeleteBulkAction::make(),
            //         Tables\Actions\RestoreBulkAction::make(),
            //         Tables\Actions\ForceDeleteBulkAction::make(),
            //     ]),
            // ]);
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
            'index' => Pages\ListDokters::route('/'),
            'create' => Pages\CreateDokter::route('/create'),
            'edit' => Pages\EditDokter::route('/{record}/edit'),
        ];
    }
}
