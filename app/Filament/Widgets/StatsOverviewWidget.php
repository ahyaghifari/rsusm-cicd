<?php

namespace App\Filament\Widgets;

use App\Models\Banner;
use App\Models\Dokter;
use App\Models\Halaman;
use App\Models\JadwalHarian;
use App\Models\Magazine;
use App\Models\PoliKlinik;
use App\Models\Promo;
use App\Models\RawatInap;
use App\Models\RumahSakit;
use App\Models\UnitLayanan;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        /** @var \App\Models\User $user */
        $user = filament()->auth()->user();

        if ($user->isSuperAdmin()) {
            return $this->superAdminStats();
        }

        $rsId = $user->rumah_sakit_id;

        return match (true) {
            $user->hasRole('admin')     => $this->adminStats($rsId),
            $user->hasRole('humas')     => $this->humasStats($rsId),
            $user->hasRole('informasi') => $this->informasiStats($rsId),
            default                     => [],
        };
    }

    private function superAdminStats(): array
    {
        return [
            Stat::make('Rumah Sakit Aktif', RumahSakit::where('aktif', true)->count())
                ->description('Terdaftar & aktif')
                ->descriptionIcon('heroicon-m-building-office')
                ->color('primary'),
            Stat::make('Total Pengguna', User::count())
                ->description('Semua role')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),
            Stat::make('Total Dokter', Dokter::count())
                ->description('Seluruh rumah sakit')
                ->descriptionIcon('heroicon-m-user-circle')
                ->color('success'),
            Stat::make('Total Unit Layanan', UnitLayanan::count())
                ->description('Seluruh rumah sakit')
                ->descriptionIcon('heroicon-m-squares-2x2')
                ->color('warning'),
        ];
    }

    private function adminStats(?int $rsId): array
    {
        $scope = fn ($q) => $q->where('rumah_sakit_id', $rsId);

        return [
            Stat::make('Dokter', Dokter::where('rumah_sakit_id', $rsId)->count())
                ->description('Dokter RS ini')
                ->descriptionIcon('heroicon-m-user-circle')
                ->color('primary'),
            Stat::make('Poliklinik', PoliKlinik::whereHas('unitLayanan', $scope)->count())
                ->description('Poliklinik RS ini')
                ->descriptionIcon('heroicon-m-building-office-2')
                ->color('success'),
            Stat::make('Unit Layanan', UnitLayanan::where('rumah_sakit_id', $rsId)->count())
                ->description('Unit layanan RS ini')
                ->descriptionIcon('heroicon-m-squares-2x2')
                ->color('info'),
            Stat::make('Jadwal Hari Ini', JadwalHarian::whereHas('poliklinik.unitLayanan', $scope)->whereDate('tanggal', today())->count())
                ->description('Entri jadwal harian')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('warning'),
        ];
    }

    private function humasStats(?int $rsId): array
    {
        return [
            Stat::make('Banner', Banner::where('rumah_sakit_id', $rsId)->count())
                ->description('Banner aktif')
                ->descriptionIcon('heroicon-m-photo')
                ->color('primary'),
            Stat::make('Promo', Promo::where('rumah_sakit_id', $rsId)->count())
                ->description('Total promo')
                ->descriptionIcon('heroicon-m-tag')
                ->color('warning'),
            Stat::make('Majalah', Magazine::where('rumah_sakit_id', $rsId)->count())
                ->description('Edisi majalah')
                ->descriptionIcon('heroicon-m-newspaper')
                ->color('success'),
            Stat::make('Halaman', Halaman::where('rumah_sakit_id', $rsId)->count())
                ->description('Halaman statis')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('info'),
        ];
    }

    private function informasiStats(?int $rsId): array
    {
        $scope = fn ($q) => $q->where('rumah_sakit_id', $rsId);

        return [
            Stat::make('Dokter', Dokter::where('rumah_sakit_id', $rsId)->count())
                ->description('Dokter RS ini')
                ->descriptionIcon('heroicon-m-user-circle')
                ->color('primary'),
            Stat::make('Poliklinik', PoliKlinik::whereHas('unitLayanan', $scope)->count())
                ->description('Poliklinik RS ini')
                ->descriptionIcon('heroicon-m-building-office-2')
                ->color('success'),
            Stat::make('Unit Layanan', UnitLayanan::where('rumah_sakit_id', $rsId)->count())
                ->description('Unit layanan RS ini')
                ->descriptionIcon('heroicon-m-squares-2x2')
                ->color('info'),
            Stat::make('Rawat Inap', RawatInap::where('rumah_sakit_id', $rsId)->count())
                ->description('Kamar rawat inap')
                ->descriptionIcon('heroicon-m-home-modern')
                ->color('warning'),
        ];
    }
}
