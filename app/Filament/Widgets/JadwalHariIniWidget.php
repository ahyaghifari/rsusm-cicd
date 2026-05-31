<?php

namespace App\Filament\Widgets;

use App\Models\JadwalLayananHarian;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class JadwalHariIniWidget extends BaseWidget
{
    protected static ?int $sort = 2;
    protected int|string|array $columnSpan = 'full';
    protected static ?string $heading = 'Jadwal Layanan Hari Ini';

    public static function canView(): bool
    {
        /** @var \App\Models\User $user */
        $user = filament()->auth()->user();
        return $user->hasAnyRole(['admin', 'informasi']);
    }

    public function table(Table $table): Table
    {
        /** @var \App\Models\User $user */
        $user  = filament()->auth()->user();
        $rsId  = $user->rumah_sakit_id;
        $scope = fn ($q) => $q->where('rumah_sakit_id', $rsId);

        return $table
            ->query(
                JadwalLayananHarian::with(['poliklinik', 'dokter'])
                    ->whereHas('poliklinik.unitLayanan', $scope)
                    ->whereDate('tanggal', today())
                    ->orderBy('jam_mulai')
            )
            ->columns([
                Tables\Columns\TextColumn::make('poliklinik.nama')
                    ->label('Poliklinik')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nama_dokter')
                    ->label('Dokter')
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('jam_mulai')
                    ->label('Jam Mulai')
                    ->formatStateUsing(fn ($state) => $state?->format('H:i')),
                Tables\Columns\TextColumn::make('jam_selesai')
                    ->label('Jam Selesai')
                    ->formatStateUsing(fn ($state) => $state?->format('H:i'))
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('status_layanan')
                    ->label('Status')
                    ->badge(),
                Tables\Columns\TextColumn::make('catatan')
                    ->label('Catatan')
                    ->placeholder('—')
                    ->limit(50),
            ])
            ->paginated(false)
            ->emptyStateHeading('Tidak ada jadwal hari ini')
            ->emptyStateDescription('Belum ada entri jadwal layanan harian untuk ' . today()->translatedFormat('l, d F Y'))
            ->emptyStateIcon('heroicon-o-calendar');
    }
}
