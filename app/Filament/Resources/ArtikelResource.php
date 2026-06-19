<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ArtikelResource\Pages;
use App\Models\Artikel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;

class ArtikelResource extends BaseRumahSakitResource
{
    protected static ?string $model = Artikel::class;

    protected static ?string $navigationIcon = 'heroicon-o-newspaper';

    protected static ?string $navigationLabel = 'Artikel & Berita';

    protected static ?string $navigationGroup = 'Media Informasi';

    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                static::rsFormField(),
                TextInput::make('judul')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, Forms\Set $set, $record) {
                        if (! $record) {
                            $set('slug', \Illuminate\Support\Str::slug($state));
                        }
                    })
                    ->columnSpanFull(),

                TextInput::make('slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(Artikel::class, 'slug', ignoreRecord: true, modifyRuleUsing: function ($rule, Forms\Get $get) {
                        return $rule->where('rumah_sakit_id', static::isSuperAdmin() ? $get('rumah_sakit_id') : static::rumahSakitId());
                    })
                    ->helperText('Otomatis dari judul. Bisa diubah manual.'),

                Select::make('kategori_artikel_id')
                    ->label('Kategori')
                    ->relationship(
                        name: 'kategori',
                        titleAttribute: 'nama',
                        modifyQueryUsing: function ($query, Forms\Get $get) {
                            return $query->where(
                                'rumah_sakit_id',
                                static::isSuperAdmin() ? $get('rumah_sakit_id') : static::rumahSakitId()
                            );
                        },
                    )
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->createOptionForm([
                        TextInput::make('nama')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, Forms\Set $set) => $set('slug', \Illuminate\Support\Str::slug($state))),
                        TextInput::make('slug')
                            ->required(),
                    ]),

                FileUpload::make('gambar')
                    ->image()
                    ->disk('public')
                    ->directory('artikel')
                    ->imageEditor()
                    ->nullable(),

                TextInput::make('penulis')
                    ->maxLength(100)
                    ->nullable(),

                DatePicker::make('tanggal_publish')
                    ->default(now())
                    ->required(),

                Textarea::make('ringkasan')
                    ->rows(3)
                    ->maxLength(300)
                    ->columnSpanFull()
                    ->helperText('Ditampilkan di card listing & meta description.'),

                RichEditor::make('konten')
                    ->required()
                    ->columnSpanFull(),

                Toggle::make('unggulan')
                    ->label('Artikel Unggulan')
                    ->default(false),

                Toggle::make('aktif')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('tanggal_publish', 'desc')
            ->columns([
                ImageColumn::make('gambar')
                    ->disk('public')
                    ->square(),

                TextColumn::make('judul')
                    ->searchable()
                    ->limit(60),

                TextColumn::make('kategori.nama')
                    ->label('Kategori')
                    ->badge()
                    ->toggleable(),

                TextColumn::make('penulis')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('tanggal_publish')
                    ->date('d M Y')
                    ->sortable(),

                static::rsTableColumn(),

                IconColumn::make('unggulan')
                    ->boolean()
                    ->sortable(),

                ToggleColumn::make('aktif'),

                TextColumn::make('created_at')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                static::rsTableFilter(),
                SelectFilter::make('kategori_artikel_id')
                    ->label('Kategori')
                    ->relationship('kategori', 'nama'),
                TernaryFilter::make('aktif')->label('Status Aktif'),
                TernaryFilter::make('unggulan')->label('Artikel Unggulan'),
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
        if (!static::isSuperAdmin()) {

            $data['rumah_sakit_id'] = static::rumahSakitId();

        }

        return $data;
    }

    public static function mutateFormDataBeforeSave(array $data): array
    {
        if (!static::isSuperAdmin()) {

            $data['rumah_sakit_id'] = static::rumahSakitId();

        }

        return $data;
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListArtikels::route('/'),
            'create' => Pages\CreateArtikel::route('/create'),
            'edit'   => Pages\EditArtikel::route('/{record}/edit'),
        ];
    }
}
