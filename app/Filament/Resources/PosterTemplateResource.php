<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PosterTemplateResource\Pages;
use App\Models\PosterTemplate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;

class PosterTemplateResource extends BaseRumahSakitResource
{
    protected static ?string $model = PosterTemplate::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationLabel = 'Template Poster';

    protected static ?string $modelLabel = 'Template Poster';

    protected static ?string $pluralModelLabel = 'Template Poster';

    protected static ?string $navigationGroup = 'Poliklinik / Rawat Jalan';

    protected static ?int $navigationSort = 3;
    // protected static bool $shouldRegisterNavigation = false;

    // ── Form ──────────────────────────────────────────────────────────────────

    public static function form(Form $form): Form
    {
        return $form->schema([

            // ── Informasi Dasar ──────────────────────────────────────────────
            Forms\Components\Section::make('Informasi Template')
                ->schema([
                    static::rsFormField(),

                    Forms\Components\TextInput::make('nama')
                        ->label('Nama Template')
                        ->required()
                        ->maxLength(100)
                        ->placeholder('Contoh: Template Reguler 2025'),

                    Forms\Components\Toggle::make('is_default')
                        ->label('Jadikan Template Default')
                        ->helperText('Template default otomatis terpilih saat generate poster.'),
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
                        ->helperText('Logo RS yang ditempatkan di layer atas poster.'),
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

                static::rsTableColumn(),

                Tables\Columns\IconColumn::make('is_default')
                    ->label('Default')
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
                Tables\Actions\Action::make('zone_editor')
                    ->label('Edit Zone')
                    ->icon('heroicon-o-paint-brush')
                    ->color('primary')
                    ->url(fn (PosterTemplate $record) => static::getUrl('zone-editor', ['record' => $record])),
                Tables\Actions\ReplicateAction::make()
                    ->label('Duplikat')
                    ->beforeReplicaSaved(function (PosterTemplate $replica): void {
                        $replica->nama = $replica->nama . ' (Copy)';
                        $replica->is_default = false;
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
            'index'       => Pages\ListPosterTemplates::route('/'),
            'create'      => Pages\CreatePosterTemplate::route('/create'),
            'edit'        => Pages\EditPosterTemplate::route('/{record}/edit'),
            'zone-editor' => Pages\ZoneEditorPage::route('/{record}/zone-editor'),
        ];
    }
}
