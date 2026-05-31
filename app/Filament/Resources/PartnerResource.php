<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PartnerResource\Pages;
use App\Models\Partner;
use Filament\Forms;
use Filament\Forms\Form;

use Filament\Tables;
use Filament\Tables\Table;

class PartnerResource extends BaseRumahSakitResource
{
    protected static ?string $model = Partner::class;

    protected static ?int $navigationSort = 2;
    protected static string | null $navigationGroup = 'Lainnya';
    protected static ?string $navigationIcon = 'fas-hand-holding-hand';

    protected static ?string $navigationLabel = 'Partner';

    protected static ?string $modelLabel = 'Partner';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                static::rsFormField(),

                Forms\Components\TextInput::make('nama')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Select::make('kategori')
                    ->options([
                        'ASURANSI' => 'Asuransi',
                        'PERUSAHAAN' => 'Perusahaan',
                    ])
                    ->required(),

                Forms\Components\FileUpload::make('logo')
                    ->image()
                    ->directory('partner')
                    ->maxSize(2048),

                Forms\Components\Toggle::make('aktif')
                    ->default(true)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                static::rsTableColumn(),

                Tables\Columns\TextColumn::make('nama')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('kategori')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'ASURANSI' => 'info',
                        'PERUSAHAAN' => 'warning',
                    })
                    ->sortable(),

                Tables\Columns\ImageColumn::make('logo'),

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

                Tables\Filters\SelectFilter::make('kategori')
                    ->options([
                        'ASURANSI' => 'Asuransi',
                        'PERUSAHAAN' => 'Perusahaan',
                    ])
                    ->label('Filter Kategori'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                ->mutateFormDataUsing(function (array $data): array {

                        if (! static::isSuperAdmin()) {

                            $data['rumah_sakit_id'] = static::rumahSakitId();

                        }

                        return $data;
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
            'index' => Pages\ManagePartner::route('/'),
        ];
    }
}
