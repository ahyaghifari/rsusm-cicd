<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PosterHeroResource\Pages;
use App\Models\PosterHero;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;

class PosterHeroResource extends BaseRumahSakitResource
{
    protected static ?string $model = PosterHero::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationLabel = 'Foto Header';

    protected static ?string $modelLabel = 'Foto Header';

    protected static ?string $pluralModelLabel = 'Foto Header';

    protected static ?string $navigationGroup = 'Poster Jadwal';

    protected static ?int $navigationSort = 4;

    protected static bool $shouldRegisterNavigation = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                static::rsFormField(),

                Forms\Components\TextInput::make('nama')
                    ->label('Nama')
                    ->required()
                    ->maxLength(100)
                    ->placeholder('Contoh: EXILIS Aurora'),

                Forms\Components\FileUpload::make('foto')
                    ->label('Foto Header')
                    ->image()
                    ->required()
                    ->directory('poster-heroes')
                    ->disk('public')
                    ->imageEditor()
                    ->maxSize(5120)
                    ->acceptedFileTypes(['image/jpeg', 'image/png'])
                    ->helperText('Foto yang akan menjadi background layer bawah poster.')
                    ->columnSpanFull(),

                Forms\Components\Textarea::make('keterangan')
                    ->label('Keterangan')
                    ->placeholder('Contoh: Tindakan EXILIS Aurora EC')
                    ->rows(2)
                    ->columnSpanFull(),

                Forms\Components\Toggle::make('aktif')
                    ->label('Aktif')
                    ->default(true)
                    ->onColor('success')
                    ->offColor('danger'),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('nama', 'asc')
            ->columns([
                Tables\Columns\ImageColumn::make('foto')
                    ->label('Foto')
                    ->disk('public')
                    ->square()
                    ->size(60),

                Tables\Columns\TextColumn::make('nama')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->limit(40),

                static::rsTableColumn(),

                Tables\Columns\ToggleColumn::make('aktif')
                    ->label('Aktif'),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Terakhir Diubah')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                static::rsTableFilter(),

                Tables\Filters\TernaryFilter::make('aktif')
                    ->label('Status Aktif'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->mutateFormDataUsing(fn (array $data): array => static::mutateFormDataBeforeSave($data)),
                Tables\Actions\DeleteAction::make(),
            ]);
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManagePosterHeroes::route('/'),
        ];
    }
}
