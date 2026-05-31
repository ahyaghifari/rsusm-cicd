<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\RumahSakit;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Role;

class UserResource extends BaseResource
{
    protected static ?string $model = User::class;

    protected static ?int $navigationSort = 5;
    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function form(Form $form): Form
    {
        $isSuperAdmin = static::isSuperAdmin();

        $availableRoles = $isSuperAdmin
            ? Role::orderBy('name')->pluck('name', 'name')
            : Role::whereIn('name', ['humas', 'informasi'])->orderBy('name')->pluck('name', 'name');

        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(255)
                ->label('Nama'),

            Forms\Components\TextInput::make('email')
                ->email()
                ->unique('users', 'email', ignoreRecord: true)
                ->required()
                ->maxLength(255)
                ->label('Email'),

            Forms\Components\TextInput::make('password')
                ->password()
                ->required(fn (string $operation) => $operation === 'create')
                ->minLength(8)
                ->maxLength(255)
                ->dehydrateStateUsing(fn ($state) => filled($state) ? bcrypt($state) : null)
                ->dehydrated(fn ($state) => filled($state))
                ->label('Password'),

            Forms\Components\Select::make('role_name')
                ->label('Role')
                ->options($availableRoles)
                ->required()
                ->afterStateHydrated(function (Forms\Components\Select $component, $record) {
                    if ($record) {
                        $component->state($record->roles->first()?->name);
                    }
                })
                ->live(),

            Forms\Components\Select::make('rumah_sakit_id')
                ->label('Rumah Sakit')
                ->options(fn () => $isSuperAdmin
                    ? RumahSakit::pluck('nama', 'id')
                    : RumahSakit::where('id', static::rumahSakitId())->pluck('nama', 'id'))
                ->visible(fn ($get) => $get('role_name') !== 'super_admin')
                ->required(fn ($get) => $get('role_name') !== 'super_admin')
                ->default(fn () => $isSuperAdmin ? null : static::rumahSakitId())
                ->disabled(fn () => ! $isSuperAdmin),
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

                TextColumn::make('roles.name')
                    ->label('Role')
                    ->badge()
                    ->separator(','),

                TextColumn::make('rumahSakit.nama')
                    ->label('Rumah Sakit')
                    ->placeholder('-')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([])
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

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (! static::isSuperAdmin()) {
            $query->where('rumah_sakit_id', static::rumahSakitId())
                ->whereHas('roles', fn ($q) => $q->whereIn('name', ['humas', 'informasi']));
        }

        return $query;
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit'   => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
