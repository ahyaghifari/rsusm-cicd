<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class UserResource extends BaseResource
{
    protected static ?string $model = User::class;

    protected static ?int $navigationSort = 4;
    protected static ?string $navigationIcon = 'heroicon-o-users';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->label('Nama'),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->unique('users', 'email')
                    ->required()
                    ->maxLength(255)
                    ->label('Email'),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->required(fn (string $operation) => $operation === 'create')
                    ->minLength(8)
                    ->maxLength(255)
                    ->label('Password'),
                Forms\Components\Select::make('role')
                    ->options([
                        'superadmin' => 'Superadmin',
                        'admin' => 'Admin Rumah Sakit',
                    ])
                    ->live()
                    ->required(),
                Forms\Components\Select::make('rumah_sakit_id')
                    ->relationship('rumahSakit', 'nama')
                    ->searchable()
                    ->preload()
                    ->visible(fn ($get) => $get('role') === 'admin')
                    ->required(fn ($get) => $get('role') === 'admin')
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                ->label('Nama')
                ->searchable()
                ->sortable(),

                TextColumn::make('email')
                    ->searchable()
                    ->sortable(),

                BadgeColumn::make('role')
                    ->colors([
                        'danger' => 'superadmin',
                        'primary' => 'admin',
                    ]),

                TextColumn::make('rumahSakit.nama')
                    ->label('Rumah Sakit')
                    ->placeholder('-')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return static::isSuperAdmin();
    }
}
