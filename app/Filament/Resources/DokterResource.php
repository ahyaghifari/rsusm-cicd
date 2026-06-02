<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DokterResource\Pages;
use App\Filament\Resources\DokterResource\RelationManagers;
use App\Models\Dokter;
use App\Models\Spesialis;
use Filament\Forms;
use Filament\Forms\Form;
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
    protected static ?string $navigationIcon = 'fas-user-doctor';

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
                            ->unique(Dokter::class, ignoreRecord: true),
                        Forms\Components\FileUpload::make('foto')
                            ->image()
                            ->directory('dokter/foto')
                            ->disk('public'),
                        Forms\Components\Textarea::make('deskripsi')
                            ->rows(3)
                            ->maxLength(1000),
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
                            ->required()
                            ->disabled(fn (Forms\Get $get) => static::isSuperAdmin() && !$get('rumah_sakit_id'))
                            ->searchable()
                            ->preload(),
                        Forms\Components\RichEditor::make('pendidikan')
                            ->placeholder('Masukkan riwayat pendidikan dokter...'),
                        Forms\Components\RichEditor::make('pelatihan')
                            ->placeholder('Masukkan riwayat pelatihan dokter...'),
                    ])->columnSpan(1),
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
