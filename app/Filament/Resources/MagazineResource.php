<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MagazineResource\Pages;
use App\Models\Magazine;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;

class MagazineResource extends BaseRumahSakitResource
{
    protected static ?string $model = Magazine::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationLabel = 'Magazine';
    protected static ?string $modelLabel = 'Magazine';
    protected static ?string $pluralModelLabel = 'Magazine';
    protected static string | null $navigationGroup = 'Media Informasi';

    protected static ?int $navigationSort = 7;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                static::rsFormField(),

                Forms\Components\TextInput::make('judul')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, callable $set, $record) {
                        if (! $record) {
                            $set('slug', \Illuminate\Support\Str::slug($state));
                        }
                    }),

                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->maxLength(100)
                    ->unique(table: 'magazines', column: 'slug', ignoreRecord: true)
                    ->helperText('Otomatis dari judul. Bisa diubah manual.'),

                Forms\Components\DatePicker::make('published_at')
                    ->label('Tanggal Terbit')
                    ->nullable(),

                Forms\Components\Textarea::make('deskripsi')
                    ->rows(3)
                    ->nullable(),

                Forms\Components\Toggle::make('aktif')
                    ->default(true),

                Forms\Components\FileUpload::make('cover')
                    ->label('Cover (Thumbnail)')
                    ->image()
                    ->disk('public')
                    ->directory('magazine/cover')
                    ->nullable(),

                Forms\Components\FileUpload::make('file_pdf')
                    ->label('File PDF')
                    ->acceptedFileTypes(['application/pdf'])
                    ->disk('public')
                    ->directory('magazine/pdf')
                    ->maxSize(20480)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->defaultSort('sort_order', 'asc')
            ->columns([
                Tables\Columns\ImageColumn::make('cover')
                    ->disk('public')
                    ->square(),

                Tables\Columns\TextColumn::make('judul')
                    ->searchable()
                    ->sortable(),

                static::rsTableColumn(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Urutan')
                    ->sortable(),

                Tables\Columns\TextColumn::make('published_at')
                    ->label('Tanggal Terbit')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\IconColumn::make('aktif')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('published_at', 'desc')
            ->filters([
                static::rsTableFilter(),

                Tables\Filters\TernaryFilter::make('aktif'),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageMagazines::route('/'),
        ];
    }
}
