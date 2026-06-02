<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RumahSakitResource\Pages;
use App\Models\RumahSakit;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RumahSakitResource extends BaseResource
{
    protected static ?string $model = RumahSakit::class;

    protected static ?int $navigationSort = 1;
    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    protected static ?string $navigationLabel = 'Rumah Sakit';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (static::isSuperAdmin()) {
            return $query;
        }

        return $query->where('id', static::rumahSakitId());
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nama')
                    ->required()
                    ->maxLength(100),
                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->maxLength(100),
                Forms\Components\TextInput::make('lokasi')
                    ->required()
                    ->maxLength(100),
                Forms\Components\Textarea::make('alamat')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('no_emergency')
                    ->maxLength(20),
                Forms\Components\TextInput::make('no_hotline')
                    ->maxLength(20),
                Forms\Components\FileUpload::make('gambar')
                    ->image()
                    ->directory('rumah-sakit/gambar'),
                Forms\Components\FileUpload::make('logo')
                    ->image()
                    ->directory('rumah-sakit/logo'),
                Forms\Components\Textarea::make('link_pendaftaran_online'),
                Forms\Components\Textarea::make('lokasi_google_map'),
                Forms\Components\Toggle::make('aktif')
                    ->required()
                    ->default(true),
                Forms\Components\Section::make('Tentang RS')
                    ->description('Konten untuk section "Kenapa Memilih Kami" di halaman beranda.')
                    ->collapsible()
                    ->schema([
                        Forms\Components\FileUpload::make('gambar_tentang')
                            ->label('Foto / Gambar RS')
                            ->image()
                            ->directory('rumah-sakit/tentang')
                            ->nullable()
                            ->columnSpanFull(),
                        Forms\Components\RichEditor::make('tentang_kami')
                            ->label('Deskripsi "Kenapa Memilih Kami"')
                            ->nullable()
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('lokasi')
                    ->searchable(),
                Tables\Columns\TextColumn::make('no_emergency')
                    ->searchable(),
                Tables\Columns\TextColumn::make('no_hotline')
                    ->searchable(),
                Tables\Columns\ImageColumn::make('gambar')->disk('public'),
                Tables\Columns\ImageColumn::make('logo')->disk('public'),
                Tables\Columns\IconColumn::make('aktif')
                    ->boolean(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
            ->actions([
                // Superadmin: edit & delete penuh
                Tables\Actions\EditAction::make()
                    ->visible(fn () => static::isSuperAdmin()),

                Tables\Actions\DeleteAction::make()
                    ->visible(fn () => static::isSuperAdmin()),

                // Admin: hanya bisa ubah field operasional via modal
                Tables\Actions\Action::make('pengaturan')
                    ->label('Pengaturan')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->color('gray')
                    ->visible(fn () => !static::isSuperAdmin())
                    ->fillForm(fn (RumahSakit $record): array => [
                        'no_emergency'  => $record->no_emergency,
                        'no_hotline'    => $record->no_hotline,
                        'gambar'        => $record->gambar,
                        'logo'          => $record->logo,
                        'gambar_tentang' => $record->gambar_tentang,
                        'tentang_kami'  => $record->tentang_kami,
                    ])
                    ->form([
                        Forms\Components\TextInput::make('no_emergency')
                            ->label('No. Emergency')
                            ->maxLength(20),
                        Forms\Components\TextInput::make('no_hotline')
                            ->label('No. Hotline')
                            ->maxLength(20),
                        Forms\Components\FileUpload::make('gambar')
                            ->label('Gambar Utama RS')
                            ->image()
                            ->disk('public')
                            ->directory('rumah-sakit/gambar'),
                        Forms\Components\FileUpload::make('logo')
                            ->label('Logo RS')
                            ->image()
                            ->disk('public')
                            ->directory('rumah-sakit/logo'),
                        Forms\Components\FileUpload::make('gambar_tentang')
                            ->label('Foto / Gambar Tentang RS')
                            ->image()
                            ->disk('public')
                            ->directory('rumah-sakit/tentang'),
                        Forms\Components\RichEditor::make('tentang_kami')
                            ->label('Deskripsi "Kenapa Memilih Kami"')
                            ->nullable(),
                    ])
                    ->action(function (RumahSakit $record, array $data): void {
                        $record->update([
                            'no_emergency'   => $data['no_emergency'],
                            'no_hotline'     => $data['no_hotline'],
                            'gambar'         => $data['gambar'],
                            'logo'           => $data['logo'],
                            'gambar_tentang' => $data['gambar_tentang'],
                            'tentang_kami'   => $data['tentang_kami'],
                        ]);
                    })
                    ->modalHeading('Pengaturan Rumah Sakit')
                    ->modalSubmitActionLabel('Simpan')
                    ->slideOver(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => static::isSuperAdmin()),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListRumahSakits::route('/'),
            'create' => Pages\CreateRumahSakit::route('/create'),
            'edit'   => Pages\EditRumahSakit::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return static::user()->hasAnyRole(['super_admin', 'admin']);
    }

    public static function canCreate(): bool
    {
        return static::isSuperAdmin();
    }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return static::isSuperAdmin();
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return static::isSuperAdmin();
    }

    public static function canDeleteAny(): bool
    {
        return static::isSuperAdmin();
    }
}
