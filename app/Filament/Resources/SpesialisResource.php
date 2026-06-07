<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SpesialisResource\Pages;
use App\Filament\Resources\SpesialisResource\RelationManagers;
use App\Models\Spesialis;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;

class SpesialisResource extends BaseRumahSakitResource
{
    protected static ?string $model = Spesialis::class;

    protected static ?int $navigationSort = 3;
    protected static string | null $navigationGroup = 'Dokter';
    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                static::rsFormField(),
                Forms\Components\TextInput::make('nama')
                    ->required()
                    ->maxLength(100)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) => $operation === 'create' ? $set('slug', \Illuminate\Support\Str::slug($state)) : null),
                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->maxLength(100)
                    ->unique(
                        table: 'spesialis',
                        column: 'slug',
                        ignoreRecord: true,
                        modifyRuleUsing: function (\Illuminate\Validation\Rules\Unique $rule) {
                            $rsId = static::isSuperAdmin()
                                ? request()->input('data.rumah_sakit_id')
                                : static::rumahSakitId();
                            return $rsId ? $rule->where('rumah_sakit_id', $rsId) : $rule;
                        }
                    ),
                // Forms\Components\FileUpload::make('logo')
                //     ->image()
                //     ->directory('spesialis/logo')
                //     ->disk('public'),
                Forms\Components\Toggle::make('aktif')
                    ->required()
                    ->default(true),
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
                Tables\Columns\TextColumn::make('slug')
                    ->searchable()
                    ->sortable(),
                // Tables\Columns\ImageColumn::make('logo')
                //     ->disk('public'),
                Tables\Columns\IconColumn::make('aktif')
                    ->boolean(),
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
                Tables\Filters\TrashedFilter::make(),
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
                Tables\Actions\RestoreAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
            ]);
            // ->bulkActions([
            //     Tables\Actions\BulkActionGroup::make([
            //         Tables\Actions\DeleteBulkAction::make(),
            //         Tables\Actions\RestoreBulkAction::make(),
            //         Tables\Actions\ForceDeleteBulkAction::make(),
            //     ]),
            // ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageSpesialis::route('/'),
        ];
    }
}
