<?php

namespace App\Filament\Widgets;

use App\Models\RumahSakit;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;

class JadwalPoliklinikPopupWidget extends Widget implements HasForms
{
    use InteractsWithForms;

    protected static string $view = 'filament.widgets.jadwal-poliklinik-popup-widget';

    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    public ?array $data = [];

    public static function canView(): bool
    {
        /** @var \App\Models\User $user */
        $user = filament()->auth()->user();

        return $user->hasAnyRole(['super_admin', 'admin', 'humas', 'informasi']);
    }

    public function mount(): void
    {
        /** @var \App\Models\User $user */
        $user = filament()->auth()->user();

        $rs = $user->isSuperAdmin()
            ? RumahSakit::orderBy('nama')->first()
            : $user->rumahSakit;

        $this->form->fill([
            'rumah_sakit_id'           => $rs?->id,
            'jadwal_poliklinik_gambar' => $rs?->jadwal_poliklinik_gambar,
            'jadwal_poliklinik_aktif'  => $rs?->jadwal_poliklinik_aktif ?? false,
        ]);
    }

    public function form(Form $form): Form
    {
        /** @var \App\Models\User $user */
        $user = filament()->auth()->user();

        return $form
            ->schema([
                Forms\Components\Select::make('rumah_sakit_id')
                    ->label('Rumah Sakit')
                    ->options(RumahSakit::orderBy('nama')->pluck('nama', 'id'))
                    ->visible($user->isSuperAdmin())
                    ->live()
                    ->required()
                    ->afterStateUpdated(fn () => $this->loadForRumahSakit()),

                Forms\Components\FileUpload::make('jadwal_poliklinik_gambar')
                    ->label('Gambar Poster Jadwal Poliklinik')
                    ->image()
                    ->disk('public')
                    ->directory('rumah-sakit/jadwal-poliklinik')
                    ->helperText('Gambar ini akan muncul sebagai popup beberapa saat setelah pengunjung membuka halaman beranda.')
                    ->columnSpanFull(),

                Forms\Components\Toggle::make('jadwal_poliklinik_aktif')
                    ->label('Tampilkan popup di halaman beranda'),
            ])
            ->statePath('data');
    }

    public function loadForRumahSakit(): void
    {
        $rsId = $this->data['rumah_sakit_id'] ?? null;
        $rs   = $rsId ? RumahSakit::find($rsId) : null;

        $this->form->fill([
            'rumah_sakit_id'           => $rsId,
            'jadwal_poliklinik_gambar' => $rs?->jadwal_poliklinik_gambar,
            'jadwal_poliklinik_aktif'  => $rs?->jadwal_poliklinik_aktif ?? false,
        ]);
    }

    public function save(): void
    {
        /** @var \App\Models\User $user */
        $user = filament()->auth()->user();

        $state = $this->form->getState();

        $rs = $user->isSuperAdmin()
            ? RumahSakit::find($state['rumah_sakit_id'] ?? null)
            : $user->rumahSakit;

        if (! $rs) {
            Notification::make()
                ->title('Rumah sakit tidak ditemukan.')
                ->danger()
                ->send();

            return;
        }

        $rs->update([
            'jadwal_poliklinik_gambar' => $state['jadwal_poliklinik_gambar'] ?? null,
            'jadwal_poliklinik_aktif'  => $state['jadwal_poliklinik_aktif'] ?? false,
        ]);

        Notification::make()
            ->title('Popup jadwal poliklinik berhasil disimpan.')
            ->success()
            ->send();
    }
}
