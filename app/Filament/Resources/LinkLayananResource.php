<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LinkLayananResource\Pages;
use App\Models\LinkLayanan;
use Filament\Forms;
use Filament\Forms\Form;

use Filament\Tables;
use Filament\Tables\Table;

class LinkLayananResource extends BaseRumahSakitResource
{
    protected static ?string $model = LinkLayanan::class;

    protected static ?int $navigationSort = 1;
    protected static string | null $navigationGroup = 'Layanan';
    protected static ?string $navigationIcon = 'heroicon-o-link';

    protected static ?string $navigationLabel = 'Link Layanan';

    protected static ?string $modelLabel = 'Link Layanan';
    protected static bool $shouldRegisterNavigation = false;


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                static::rsFormField(),

                Forms\Components\TextInput::make('label')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('value')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Textarea::make('deskripsi_singkat')
                    ->rows(3)
                    ->nullable(),

                Forms\Components\Textarea::make('link')
                    ->rows(2)
                    ->required(),

                Forms\Components\FileUpload::make('gambar')
                    ->image()
                    ->directory('link-layanan/gambar')
                    ->maxSize(2048)
                    ->nullable(),

                Forms\Components\Toggle::make('aktif')
                    ->default(true)
                    ->required(),
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

                Tables\Columns\TextColumn::make('label')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('value')
                    ->searchable(),

                Tables\Columns\TextColumn::make('deskripsi_singkat')
                    ->limit(50)
                    ->toggleable(),

                Tables\Columns\ImageColumn::make('gambar')
                    ->label('Gambar'),

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

                Tables\Filters\TernaryFilter::make('aktif')
                    ->label('Status Aktif'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                ->mutateFormDataUsing(function (array $data): array {

                        if (! static::isSuperAdmin()) {

                            $data['rumah_sakit_id']
                                = static::rumahSakitId();

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
            'index' => Pages\ManageLinkLayanan::route('/'),
        ];
    }
}
