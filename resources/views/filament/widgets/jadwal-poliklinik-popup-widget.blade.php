<x-filament-widgets::widget>
    <x-filament::section collapsible collapsed>
        <x-slot name="heading">
            Popup Jadwal Poliklinik
        </x-slot>
        <x-slot name="description">
            Atur gambar poster jadwal poliklinik yang akan tampil sebagai popup beberapa saat
            setelah pengunjung membuka halaman beranda portal.
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
