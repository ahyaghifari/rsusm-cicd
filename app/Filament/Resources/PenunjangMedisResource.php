<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PenunjangMedisResource\Pages;
use App\Models\PenunjangMedis;
use Filament\Forms;
use Filament\Forms\Form;

use Filament\Tables;
use Filament\Tables\Table;

class PenunjangMedisResource extends BaseRumahSakitResource
{
    protected static ?string $model = PenunjangMedis::class;

    protected static ?int $navigationSort = 4;
    protected static string | null $navigationGroup = 'Layanan';
    protected static ?string $navigationIcon = 'heroicon-o-beaker';

    protected static ?string $navigationLabel = 'Penunjang Medis';

    protected static ?string $modelLabel = 'Penunjang Medis';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                static::rsFormField(),

                Forms\Components\TextInput::make('nama')
                    ->required()
                    ->maxLength(255),

                Forms\Components\FileUpload::make('gambar')
                    ->image()
                    ->directory('penunjang-medis')
                    ->maxSize(2048),

                Forms\Components\Toggle::make('aktif')
                    ->default(true)
                    ->required(),

                Forms\Components\Textarea::make('deskripsi')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->defaultSort('sort_order', 'asc')
            ->columns([
                static::rsTableColumn(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Urutan')
                    ->sortable(),

                Tables\Columns\TextColumn::make('nama')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\ImageColumn::make('gambar'),

                Tables\Columns\IconColumn::make('aktif')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                static::rsTableFilter(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                ->mutateFormDataUsing(function (array $data): array {

                        if (!static::isSuperAdmin()) {

                            $data['rumah_sakit_id'] = static::rumahSakitId();

                        }

                        return $data;
                    }),
                Tables\Actions\DeleteAction::make(),
            ]);
            // ->bulkActions([
            //     Tables\Actions\BulkActionGroup::make([
            //         Tables\Actions\DeleteBulkAction::make(),
            //     ]),
            // ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManagePenunjangMedis::route('/'),
        ];
    }
}
