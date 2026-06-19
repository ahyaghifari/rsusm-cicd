<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KelasRawatInapResource\Pages;
use App\Models\KelasRawatInap;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;

class KelasRawatInapResource extends BaseRumahSakitResource
{
    protected static ?string $model = KelasRawatInap::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationLabel = 'Kelas Rawat Inap';

    protected static ?string $modelLabel = 'Kelas Rawat Inap';

    protected static string|null $navigationGroup = 'Rawat Inap';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                static::rsFormField()->live(),

                Forms\Components\TextInput::make('nama')
                    ->required()
                    ->maxLength(100)
                    ->placeholder('Mis. VIP, Kelas 1, Stroke Center'),

                Forms\Components\TextInput::make('id_kelas_api')
                    ->label('ID Kelas (API Ranap)')
                    ->numeric()
                    ->nullable()
                    ->helperText('Cocokkan dengan field "idKelas" dari sistem Ranap, kalau kelas ini punya pasangan data ketersediaan real-time. Kosongkan kalau tidak ada.')
                    ->unique(KelasRawatInap::class, 'id_kelas_api', ignoreRecord: true, modifyRuleUsing: function ($rule, Forms\Get $get) {
                        return $rule->where('rumah_sakit_id', static::isSuperAdmin() ? $get('rumah_sakit_id') : static::rumahSakitId());
                    }),

                Forms\Components\Toggle::make('is_vip')
                    ->label('Tandai sebagai VIP')
                    ->helperText('Menentukan styling badge "VIP" di halaman publik Rawat Inap.')
                    ->default(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('id_kelas_api')
                    ->label('ID Kelas (API)')
                    ->placeholder('-'),

                IconColumn::make('is_vip')
                    ->label('VIP')
                    ->boolean(),

                TextColumn::make('rawat_inap_count')
                    ->label('Jumlah Tipe Kamar')
                    ->state(fn (KelasRawatInap $record): int => $record->rawatInap()->count()),

                static::rsTableColumn(),
            ])
            ->filters([
                static::rsTableFilter(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->mutateFormDataUsing(fn (array $data, KelasRawatInap $record): array => static::mutateFormDataBeforeSave($data)),
                Tables\Actions\DeleteAction::make(),
            ]);
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
            'index' => Pages\ManageKelasRawatInap::route('/'),
        ];
    }
}
