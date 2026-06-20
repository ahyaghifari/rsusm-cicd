<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HalamanResource\Pages;
use App\Models\Halaman;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;

class HalamanResource extends BaseRumahSakitResource
{
    protected static ?string $model = Halaman::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Halaman Statis';
    protected static ?int $navigationSort = 5;
    protected static string | null $navigationGroup = 'Tentang Kami';


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
                    ->helperText('Otomatis dari judul. Bisa diubah manual.')
                    ->unique(
                        table: 'halaman',
                        column: 'slug',
                        ignoreRecord: true,
                        modifyRuleUsing: function (\Illuminate\Validation\Rules\Unique $rule) {
                            $rsId = static::isSuperAdmin()
                                ? request()->input('data.rumah_sakit_id')
                                : static::rumahSakitId();
                            return $rsId ? $rule->where('rumah_sakit_id', $rsId) : $rule;
                        }
                    ),

                Forms\Components\Toggle::make('aktif')
                    ->default(true)
                    ->inline(false),

                Forms\Components\RichEditor::make('konten')
                    ->nullable()
                    ->columnSpanFull()
                    ->helperText('Gunakan Heading untuk judul, List untuk poin-poin seperti visi & misi.'),

                Forms\Components\TextInput::make('kata_kunci')
                    ->label('Kata Kunci')
                    ->nullable()
                    ->maxLength(500)
                    ->columnSpanFull()
                    ->placeholder('Contoh: visi, misi, sejarah, profil, tentang kami')
                    ->helperText('Kata kunci tambahan untuk pencarian, dipisah koma.'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                static::rsTableColumn(),
                Tables\Columns\TextColumn::make('judul')
                    ->searchable(),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable(),
                Tables\Columns\IconColumn::make('aktif')
                    ->boolean(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
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

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHalamen::route('/'),
            'create' => Pages\CreateHalaman::route('/create'),
            'edit' => Pages\EditHalaman::route('/{record}/edit'),
        ];
    }

}
