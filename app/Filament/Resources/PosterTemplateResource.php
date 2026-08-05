<?php

namespace App\Filament\Resources;

use App\Enums\Hari;
use App\Enums\JenisPosterTemplate;
use App\Filament\Resources\PosterTemplateResource\Pages;
use App\Models\PosterTemplate;
use App\Models\RumahSakit;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Table;

class PosterTemplateResource extends BaseRumahSakitResource
{
    protected static ?string $model = PosterTemplate::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationLabel = 'Template Poster';

    protected static ?string $modelLabel = 'Template Poster';

    protected static ?string $pluralModelLabel = 'Template Poster';

    protected static ?string $navigationGroup = 'Poster Jadwal';

    protected static ?int $navigationSort = 3;
    // protected static bool $shouldRegisterNavigation = false;

    // ── Form ──────────────────────────────────────────────────────────────────

    public static function form(Form $form): Form
    {
        return $form->schema([

            // ── Informasi Dasar ──────────────────────────────────────────────
            Forms\Components\Section::make('Informasi Template')
                ->schema([
                    static::rsFormField()->live(),

                    Forms\Components\Select::make('jenis')
                        ->label('Jenis Poster')
                        ->options(JenisPosterTemplate::class)
                        ->default(JenisPosterTemplate::JADWAL_HARIAN)
                        ->required()
                        ->live(),

                    Forms\Components\TextInput::make('nama')
                        ->label('Nama Template')
                        ->required()
                        ->maxLength(100)
                        ->placeholder('Contoh: Template Reguler 2025'),

                    Forms\Components\Select::make('hari')
                        ->label('Hari')
                        ->options(Hari::class)
                        ->placeholder('— Default / Semua Hari —')
                        ->helperText('Kosongkan untuk jadi template fallback kalau hari tsb belum punya template khusus.')
                        ->visible(fn (Forms\Get $get) => $get('jenis') === JenisPosterTemplate::JADWAL_HARIAN->value),

                    Forms\Components\Toggle::make('is_executive')
                        ->label('Klinik Eksekutif')
                        ->helperText('Aktifkan kalau template ini khusus buat jadwal klinik eksekutif.')
                        ->default(false)
                        ->visible(function (Forms\Get $get) {
                            if ($get('jenis') !== JenisPosterTemplate::JADWAL_HARIAN->value) return false;
                            $rsId = $get('rumah_sakit_id') ?? static::rumahSakitId();
                            return $rsId && (bool) RumahSakit::where('id', $rsId)->value('executive_clinic');
                        }),
                ])
                ->columns(2),

            // ── Upload Asset ─────────────────────────────────────────────────
            Forms\Components\Section::make('Upload Asset')
                ->schema([
                    Forms\Components\Group::make([
                    Forms\Components\FileUpload::make('template_png')
                        ->label('Template PNG (Background)')
                        ->image()
                        ->required()
                        ->directory('rawat-inap/thumbnail')
                        ->disk('public')
                        ->maxSize(5120)
                        ->acceptedFileTypes(['image/png'])
                        ->helperText('Wajib PNG agar transparansi terjaga. Desain di Canva lalu export PNG. Maks 5MB.')
                        ->live(),
                    ]),

                    Forms\Components\Group::make([
                    Forms\Components\FileUpload::make('logo_header')
                        ->label('Logo Header')
                        ->image()
                        ->directory('poster-templates/logo')
                        ->maxSize(1024)
                        ->acceptedFileTypes(['image/png', 'image/jpeg'])
                        ->helperText('Logo RS yang ditempatkan di layer atas poster.')
                        ->visible(fn (Forms\Get $get) => $get('jenis') !== JenisPosterTemplate::PERUBAHAN_JADWAL->value),
                    ]),

                    Forms\Components\Group::make([
                    Forms\Components\FileUpload::make('shape_poli')
                        ->label('Shape Nama Poli (opsional)')
                        ->image()
                        ->directory('poster-templates/shape')
                        ->maxSize(1024)
                        ->acceptedFileTypes(['image/png'])
                        ->helperText('PNG transparan untuk background header nama poli. Kosongkan untuk pakai warna solid/gradasi.')
                        ->visible(fn (Forms\Get $get) => $get('jenis') !== JenisPosterTemplate::PERUBAHAN_JADWAL->value),
                    ])
                ])
                ->columns(2),

        ]);
    }

    // ── Table ─────────────────────────────────────────────────────────────────

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('template_png')
                    ->label('Preview')
                    ->disk('public')
                    ->height(60)
                    ->width(34),   // rasio potrait 9:16

                Tables\Columns\TextColumn::make('nama')
                    ->label('Nama Template')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('jenis')
                    ->label('Jenis')
                    ->badge(),

                static::rsTableColumn(),

                Tables\Columns\TextColumn::make('hari')
                    ->label('Hari')
                    ->badge()
                    ->placeholder('Default'),

                Tables\Columns\IconColumn::make('is_executive')
                    ->label('Eksekutif')
                    ->boolean(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Terakhir Diubah')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                static::rsTableFilter(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ReplicateAction::make()
                    ->label('Duplikat')
                    ->beforeReplicaSaved(function (PosterTemplate $replica): void {
                        $replica->nama = $replica->nama . ' (Copy)';
                    }),
                Tables\Actions\Action::make('terapkan_config')
                    ->label('Terapkan Config')
                    ->icon('heroicon-o-arrow-path')
                    ->color('gray')
                    ->form(fn (PosterTemplate $record) => [
                        Forms\Components\CheckboxList::make('target_ids')
                            ->label('Terapkan config zone dari template ini ke:')
                            ->options(
                                PosterTemplate::where('rumah_sakit_id', $record->rumah_sakit_id)
                                    ->where('jenis', $record->jenis)
                                    ->where('id', '!=', $record->id)
                                    ->pluck('nama', 'id')
                            )
                            ->required()
                            ->bulkToggleable()
                            ->columns(1),
                    ])
                    ->visible(fn (PosterTemplate $record) => PosterTemplate::where('rumah_sakit_id', $record->rumah_sakit_id)
                        ->where('jenis', $record->jenis)
                        ->where('id', '!=', $record->id)
                        ->exists())
                    ->requiresConfirmation()
                    ->modalHeading('Terapkan Konfigurasi Zone ke Template Lain')
                    ->modalDescription(fn (PosterTemplate $record) => "Config zone (posisi, warna, font) dari \"{$record->nama}\" akan menimpa config template yang dipilih. PNG background & logo template tujuan tidak berubah. Tindakan ini tidak bisa dibatalkan.")
                    ->action(function (PosterTemplate $record, array $data) {
                        $targets = PosterTemplate::whereIn('id', $data['target_ids'])->get();
                        $targets->each(fn (PosterTemplate $t) => $t->update(['config' => $record->config]));

                        Notification::make()
                            ->title("Config diterapkan ke {$targets->count()} template")
                            ->success()
                            ->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'                  => Pages\ListPosterTemplates::route('/'),
            'create'                 => Pages\CreatePosterTemplate::route('/create'),
            'edit'                   => Pages\EditPosterTemplate::route('/{record}/edit'),
            'zone-editor'                 => Pages\ZoneEditorPage::route('/{record}/zone-editor'),
            'zone-editor-list-polos'      => Pages\ZoneEditorPageListPolos::route('/{record}/zone-editor-list-polos'),
            'zone-editor-perubahan-jadwal'=> Pages\ZoneEditorPagePerubahanJadwal::route('/{record}/zone-editor-perubahan-jadwal'),
        ];
    }
}
