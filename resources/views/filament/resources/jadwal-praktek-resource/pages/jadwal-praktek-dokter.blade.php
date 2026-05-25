    <x-filament-panels::page>
        <div class="space-y-6">
            
            <x-filament::section>
                {{ $this->filterForm }}
            </x-filament::section>

            {{-- AREA TAMPILAN JADWAL: Tampil jika dokter dan rumah sakit telah valid dipilih --}}
            @if($selectedDokterId && $this->getActiveRumahSakitId())
                
                {{-- SEKSI UTAMA 2: MODE PREVIEW JADWAL (Kondisi saat $isEditing == false) --}}
                @if(!$isEditing)
                    <x-filament::section>
                        <x-slot name="heading">
                        Jadwal Praktek <span class="text-primary-500 font-bold">{{ $this->namaDokter }}</span>
                        </x-slot>

                        {{-- Slot tombol header kanan untuk memicu aksi edit --}}
                        <x-slot name="headerEnd">
                            <x-filament::button wire:click="startEdit" icon="heroicon-m-pencil-square">
                                Edit Jadwal
                            </x-filament::button>
                        </x-slot>
                        
                        <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                                        <th class="px-6 py-4 text-sm font-semibold text-gray-700 dark:text-gray-200 w-1/4">Hari</th>
                                        <th class="px-6 py-4 text-sm font-semibold text-gray-700 dark:text-gray-200">Jam Praktek</th>
                                        <th class="px-6 py-4 text-sm font-semibold text-gray-700 dark:text-gray-200 w-1/4">Sesuai Perjanjian</th>
                                        <th class="px-6 py-4 text-sm font-semibold text-gray-700 dark:text-gray-200 w-1/4">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                    @foreach($schedule as $item)
                                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition">
                                            <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {{ $item['hari'] }}
                                            </td>
                                            
                                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                                    <span class="font-mono bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded">
                                                        {{ \Carbon\Carbon::parse($item['waktu_mulai'])->format('H:i') }}
                                                    </span>
                                                    <span class="mx-2 text-gray-400">-</span>
                                                    <span class="font-mono bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded">
                                                        {{ \Carbon\Carbon::parse($item['waktu_selesai'])->format('H:i') }}
                                                    </span>
                                                
                                            </td>
                                            
                                            <td class="px-6 py-4 text-sm">
                                                @if($item['sesuai_perjanjian'])
                                                    <x-filament::badge color="success" icon="heroicon-m-check-circle">
                                                        Ya
                                                    </x-filament::badge>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 text-sm">
                                                @if($item['libur'])
                                                    <x-filament::badge color="danger" icon="heroicon-m-x-circle">
                                                        Libur
                                                    </x-filament::badge>
                                                @else
                                                    <x-filament::badge color="success" icon="heroicon-m-check-circle">
                                                        Buka
                                                    </x-filament::badge>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    
                    </x-filament::section>
                
                {{-- SEKSI UTAMA 3: MODE EDIT JADWAL REPEATER (Kondisi saat $isEditing == true) --}}
                @else
                    <form wire:submit.prevent="save" class="space-y-6">
                        <x-filament::section>
                            <x-slot name="heading">
                                Edit Jadwal Praktek
                            </x-slot>

                            {{-- Me-render object schema form utama dari function form() yang berisi komponen Filament Repeater --}}
                            {{ $this->form }}

                            <div class="flex items-center gap-3 mt-6 justify-end">
                                <x-filament::button color="gray" wire:click="cancelEdit" type="button">
                                    Batal
                                </x-filament::button>

                                <x-filament::button type="submit" color="primary">
                                    Simpan
                                </x-filament::button>
                            </div>
                        </x-filament::section>
                    </form>
                @endif
            @endif
        </div>
    </x-filament-panels::page>