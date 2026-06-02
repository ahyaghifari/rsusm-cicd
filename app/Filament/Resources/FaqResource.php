<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FaqResource\Pages;
use App\Models\Faq;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;

class FaqResource extends BaseRumahSakitResource
{
    protected static ?string $model = Faq::class;

    protected static ?string $navigationIcon = 'heroicon-o-question-mark-circle';

    protected static ?string $navigationLabel = 'FAQ';

    protected static ?string $modelLabel = 'FAQ';

    protected static ?string $pluralModelLabel = 'FAQ';

    protected static ?string $navigationGroup = 'Media Informasi';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                static::rsFormField(),

                TextInput::make('judul')
                    ->label('Pertanyaan')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                RichEditor::make('deskripsi')
                    ->label('Jawaban')
                    ->required()
                    ->columnSpanFull(),

                TextInput::make('kata_kunci')
                    ->label('Kata Kunci')
                    ->nullable()
                    ->maxLength(500)
                    ->columnSpanFull()
                    ->placeholder('Contoh: BPJS, asuransi, biaya, pembayaran, tagihan')
                    ->helperText('Kata kunci tambahan untuk pencarian, dipisah koma.'),

                Toggle::make('aktif')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->defaultSort('sort_order', 'asc')
            ->columns([
                TextColumn::make('sort_order')
                    ->label('#')
                    ->sortable(),

                TextColumn::make('judul')
                    ->label('Pertanyaan')
                    ->searchable()
                    ->limit(60),

                static::rsTableColumn(),

                ToggleColumn::make('aktif')
                    ->label('Aktif'),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                static::rsTableFilter(),

                TernaryFilter::make('aktif')
                    ->label('Status Aktif'),
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

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListFaqs::route('/'),
            'create' => Pages\CreateFaq::route('/create'),
            'edit'   => Pages\EditFaq::route('/{record}/edit'),
        ];
    }
}
