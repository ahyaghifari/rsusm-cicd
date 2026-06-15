<x-filament-widgets::widget>
    <x-filament::section collapsible>
        <x-slot name="heading">
            Popup Promo
        </x-slot>
        <x-slot name="description">
            Tambah promo baru atau pilih promo aktif yang akan tampil sebagai popup
            beberapa saat setelah pengunjung membuka halaman beranda portal.
        </x-slot>

        <form wire:submit="save" class="space-y-4">
            {{ $this->form }}

            <div class="flex justify-end">
                <x-filament::button type="submit">
                    Simpan
                </x-filament::button>
            </div>
        </form>
    </x-filament::section>
</x-filament-widgets::widget>
