<?php

namespace App\Filament\Widgets;

use App\Models\Promo;
use App\Models\RumahSakit;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;

class PromoPopupWidget extends Widget implements HasForms
{
    use InteractsWithForms;

    protected static string $view = 'filament.widgets.promo-popup-widget';

    protected static ?int $sort = 101;

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
            'rumah_sakit_id' => $rs?->id,
            'popup_promo_id' => $this->getPopupPromoId($rs?->id),
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

                Forms\Components\Select::make('popup_promo_id')
                    ->label('Promo yang ditampilkan sebagai popup')
                    ->placeholder('Tidak ada promo yang dijadikan popup')
                    ->options(fn () => $this->getPromoOptions())
                    ->helperText('Pilih salah satu promo aktif untuk ditampilkan sebagai popup di halaman beranda.')
                    ->columnSpanFull(),

                Forms\Components\Section::make('Tambah Promo Baru')
                    ->description('Promo baru otomatis berstatus aktif dan bisa langsung dijadikan popup.')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Forms\Components\TextInput::make('judul_baru')
                            ->label('Judul Promo')
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\RichEditor::make('deskripsi_baru')
                            ->label('Deskripsi')
                            ->columnSpanFull(),

                        Forms\Components\FileUpload::make('gambar_baru')
                            ->label('Gambar Promo')
                            ->image()
                            ->disk('public')
                            ->directory('promo')
                            ->imageEditor()
                            ->columnSpanFull(),

                        Forms\Components\Toggle::make('jadikan_popup_baru')
                            ->label('Jadikan promo ini sebagai popup'),
                    ]),
            ])
            ->statePath('data');
    }

    protected function getPromoOptions(): array
    {
        $rsId = $this->data['rumah_sakit_id'] ?? null;

        if (! $rsId) {
            return [];
        }

        return Promo::where('rumah_sakit_id', $rsId)
            ->aktif()
            ->orderByDesc('created_at')
            ->pluck('judul', 'id')
            ->toArray();
    }

    protected function getPopupPromoId(?int $rsId): ?int
    {
        if (! $rsId) {
            return null;
        }

        return Promo::where('rumah_sakit_id', $rsId)
            ->popup()
            ->value('id');
    }

    public function loadForRumahSakit(): void
    {
        $rsId = $this->data['rumah_sakit_id'] ?? null;

        $this->form->fill([
            'rumah_sakit_id' => $rsId,
            'popup_promo_id' => $this->getPopupPromoId($rsId),
            'judul_baru' => null,
            'deskripsi_baru' => null,
            'gambar_baru' => null,
            'jadikan_popup_baru' => false,
        ]);
    }

    public function save(): void
    {
        /** @var \App\Models\User $user */
        $user = filament()->auth()->user();

        $state = $this->form->getState();

        $rsId = $user->isSuperAdmin()
            ? ($state['rumah_sakit_id'] ?? null)
            : $user->rumah_sakit_id;

        if (! $rsId) {
            Notification::make()
                ->title('Rumah sakit tidak ditemukan.')
                ->danger()
                ->send();

            return;
        }

        $popupPromoId = $state['popup_promo_id'] ?? null;

        if (! empty($state['judul_baru'])) {
            $promo = Promo::create([
                'rumah_sakit_id' => $rsId,
                'judul' => $state['judul_baru'],
                'deskripsi' => $state['deskripsi_baru'] ?? null,
                'gambar' => $state['gambar_baru'] ?? null,
                'aktif' => true,
                'popup' => false,
            ]);

            if ($state['jadikan_popup_baru'] ?? false) {
                $popupPromoId = $promo->id;
            }
        }

        $resetPopup = Promo::where('rumah_sakit_id', $rsId)->where('popup', true);

        if ($popupPromoId) {
            $resetPopup->where('id', '!=', $popupPromoId);
        }

        $resetPopup->update(['popup' => false]);

        if ($popupPromoId) {
            Promo::where('id', $popupPromoId)
                ->where('rumah_sakit_id', $rsId)
                ->update(['popup' => true]);
        }

        Notification::make()
            ->title('Popup promo berhasil disimpan.')
            ->success()
            ->send();

        $this->loadForRumahSakit();
    }
}
