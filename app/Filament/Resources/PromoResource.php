<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PromoResource\Pages\ManagePromo;
use App\Models\Promo;

use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;

use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;

use Filament\Tables\Filters\SelectFilter;

class PromoResource extends BaseRumahSakitResource
{
    protected static ?string $model = Promo::class;

    protected static ?string $navigationIcon = 'heroicon-o-megaphone';

    protected static ?string $navigationLabel = 'Promo';
    protected static ?int $navigationSort = 3;
    protected static string | null $navigationGroup = 'Media Informasi';



    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                static::rsFormField(),

                TextInput::make('judul')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, callable $set, $record) {
                        if (! $record) {
                            $set('slug', \Illuminate\Support\Str::slug($state));
                        }
                    }),

                TextInput::make('slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->helperText('Otomatis dari judul. Bisa diubah manual.'),

                Toggle::make('popup')
                    ->label('Popup Promo')
                    ->helperText('Jika aktif, promo popup lainnya otomatis dinonaktifkan.')
                    ->default(false),

                Toggle::make('aktif')
                    ->default(true),

                RichEditor::make('deskripsi')
                    ->columnSpanFull(),

                FileUpload::make('gambar')
                    ->image()
                    ->disk('public')
                    ->directory('promo')
                    ->imageEditor()
                    ->nullable(),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                ImageColumn::make('gambar')
                    ->disk('public')
                    ->square(),

                TextColumn::make('judul')
                    ->searchable()
                    ->sortable(),

                static::rsTableColumn(),

                IconColumn::make('popup')
                    ->boolean()
                    ->sortable(),

                IconColumn::make('aktif')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

            ])
            ->filters([

                SelectFilter::make('aktif')
                    ->options([
                        1 => 'Aktif',
                        0 => 'Tidak Aktif',
                    ]),

                SelectFilter::make('popup')
                    ->options([
                        1 => 'Popup',
                        0 => 'Bukan Popup',
                    ]),

            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function mutateFormDataBeforeCreate(array $data): array
    {
        if (! static::isSuperAdmin()) {

            $data['rumah_sakit_id'] = static::rumahSakitId();

        }
        
        if ($data['popup'] ?? false) {

            Promo::where('popup', true)
                ->update([
                    'popup' => false,
                ]);
        }

        return $data;
    }

    public static function mutateFormDataBeforeSave(array $data, Promo $record): array
    {
        if (! static::isSuperAdmin()) {

            $data['rumah_sakit_id'] = static::rumahSakitId();

        }

        if ($data['popup'] ?? false) {

            Promo::where('id', '!=', $record->id)
                ->where('popup', true)
                ->update([
                    'popup' => false,
                ]);
        }

        return $data;
    }

    public static function getPages(): array
    {
        return [
            'index' => ManagePromo::route('/'),
        ];
    }
}