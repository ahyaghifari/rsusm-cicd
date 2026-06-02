<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BannerResource\Pages;
use App\Models\Banner;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;

class BannerResource extends BaseRumahSakitResource
{
    protected static ?string $model = Banner::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationLabel = 'Banner';

    protected static ?string $pluralModelLabel = 'Banner';

    protected static ?string $modelLabel = 'Banner';

    protected static string | null $navigationGroup = 'Media Informasi';


    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                static::rsFormField(),

                Forms\Components\TextInput::make('nama')
                    ->label('Nama Banner')
                    ->required()
                    ->maxLength(255),

                Forms\Components\FileUpload::make('gambar')
                    ->label('Gambar')
                    ->image()
                    ->directory('banners')
                    ->visibility('public')
                    ->imageEditor()
                    ->maxSize(2048)
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
            ->reorderable('sort_order')
            ->defaultSort('sort_order', 'asc')
            ->columns([

                Tables\Columns\ImageColumn::make('gambar')
                    ->label('Gambar')
                    ->square()
                    ->size(60),

                Tables\Columns\TextColumn::make('nama')
                    ->label('Nama Banner')
                    ->searchable()
                    ->sortable(),

                static::rsTableColumn(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Urutan')
                    ->sortable(),

                Tables\Columns\ToggleColumn::make('aktif')
                    ->label('Aktif'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            ->filters([
                static::rsTableFilter(),

                Tables\Filters\TernaryFilter::make('aktif')
                    ->label('Status Aktif'),
            ])

            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])

            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ManageBanners::route('/'),
        ];
    }
}
