<div>
    <x-page-hero
        title="Tanya Dokter"
        subtitle="Konsultasi chat langsung dengan dokter kami — tanpa perlu datang ke rumah sakit."
        icon="stethoscope"
    />

    <div class="w-11/12 lg:w-9/12 mx-auto py-12">

        <div class="flex items-start gap-3 p-4 mb-8 rounded-xl bg-blue-50 border border-blue-200">
            <span class="material-symbols-outlined text-blue-600 text-xl">info</span>
            <div>
                <p class="font-semibold text-blue-800 text-sm">Cara Kerja Konsultasi Chat</p>
                <p class="text-blue-700/80 text-xs mt-0.5">
                    Pilih dokter yang berstatus "Tersedia", isi nama dan kontak Anda, lalu mulai sesi chat.
                    Sesi akan berlangsung sesuai durasi yang ditentukan oleh dokter.
                </p>
            </div>
        </div>

        @if($dokter->isEmpty())
            <div class="flex flex-col items-center justify-center py-24 text-center">
                <div class="w-20 h-20 rounded-full bg-surface-container flex items-center justify-center mb-5">
                    <span class="material-symbols-outlined text-4xl text-on-surface-variant/50">stethoscope</span>
                </div>
                <p class="font-semibold text-on-surface-variant text-lg">Belum ada dokter untuk konsultasi chat</p>
                <p class="text-sm text-on-surface-variant/60 mt-1.5">Silakan kembali lagi nanti.</p>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($dokter as $d)
                {{-- Outer wrapper: border tipis — hijau jika tersedia, abu jika tidak --}}
                <div @class([
                    'p-[1.5px] rounded-2xl shadow-sm transition-colors duration-200',
                    'bg-green-400/70 shadow-green-100' => $d->tersedia_konsultasi,
                    'bg-outline-variant/25'             => ! $d->tersedia_konsultasi,
                ])>
                    <div class="bg-white rounded-2xl overflow-hidden flex flex-col h-full">

                        <div class="relative h-48 bg-gray-100 overflow-hidden">
                            @if($d->foto)
                                <img src="{{ Storage::url($d->foto) }}" alt="{{ $d->nama }}"
                                     class="absolute inset-0 w-full h-full object-contain" loading="lazy">
                            @else
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <span class="material-symbols-outlined text-5xl text-on-surface-variant/30">person</span>
                                </div>
                            @endif

                            {{-- Badge status ketersediaan --}}
                            <div class="absolute top-3 right-3">
                                @if($d->tersedia_konsultasi)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-green-100 text-green-700 border border-green-200">
                                        <span class="size-1.5 rounded-full bg-green-500 animate-pulse"></span>
                                        Tersedia
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-gray-100 text-gray-500 border border-gray-200">
                                        <span class="size-1.5 rounded-full bg-gray-400"></span>
                                        Tidak Tersedia
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="p-4 flex flex-col gap-2 grow">
                            <div>
                                <p class="font-semibold text-on-surface">{{ $d->nama }}</p>
                                <p class="text-xs text-on-surface-variant mt-0.5">{{ $d->namaSpesialis() }}</p>
                            </div>

                            {{-- Info sesi: durasi & harga --}}
                            <div class="flex items-center gap-3 mt-1">
                                <span class="inline-flex items-center gap-1 text-[11px] text-on-surface-variant/80">
                                    <span class="material-symbols-outlined text-[13px]">timer</span>
                                    {{ $d->durasi_sesi_menit }} menit / sesi
                                </span>
                                <span class="inline-flex items-center gap-1 text-[11px] text-on-surface-variant/80">
                                    <span class="material-symbols-outlined text-[13px]">payments</span>
                                    Rp —
                                </span>
                            </div>
                        </div>

                        <div class="p-4 pt-0">
                            @if($d->tersedia_konsultasi)
                                <button type="button" wire:click="pilihDokter({{ $d->id }})"
                                    class="w-full inline-flex items-center justify-center gap-1.5 py-2.5 rounded-lg text-sm font-semibold
                                           bg-primary text-on-primary hover:bg-primary/90 transition-colors duration-150">
                                    <span class="material-symbols-outlined text-[16px]">chat</span>
                                    Mulai Konsultasi
                                </button>
                            @else
                                <button type="button" disabled
                                    class="w-full inline-flex items-center justify-center gap-1.5 py-2.5 rounded-lg text-sm font-semibold
                                           bg-gray-100 text-gray-400 cursor-not-allowed">
                                    Sedang Tidak Tersedia
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @endif

        <p class="text-xs text-on-surface-variant/60 text-center mt-10">
            Layanan ini bersifat informasi umum dan bukan pengganti pemeriksaan langsung oleh dokter.
        </p>
    </div>

    {{-- Modal pengisian data sebelum memulai sesi --}}
    @if($dokterDipilih)
        @php($dokterTerpilih = $dokter->firstWhere('id', $dokterDipilih))
        <div class="fixed inset-0 z-200 flex items-center justify-center p-4">

            {{-- Backdrop --}}
            <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" wire:click="batalkanPilihan"></div>

            <div class="relative z-10 w-full max-w-md bg-white rounded-3xl overflow-hidden shadow-2xl">
                <button wire:click="batalkanPilihan"
                    class="absolute top-3 right-3 z-20 w-8 h-8 bg-black/10 hover:bg-black/20
                           text-on-surface-variant rounded-full flex items-center justify-center transition-colors duration-150">
                    <span class="material-symbols-outlined text-[18px]">close</span>
                </button>

                <div class="p-6">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-12 h-12 rounded-full bg-gray-100 overflow-hidden flex items-center justify-center shrink-0">
                            @if($dokterTerpilih?->foto)
                                <img src="{{ Storage::url($dokterTerpilih->foto) }}" alt="{{ $dokterTerpilih->nama }}"
                                     class="w-full h-full object-contain">
                            @else
                                <span class="material-symbols-outlined text-2xl text-on-surface-variant/40">person</span>
                            @endif
                        </div>
                        <div>
                            <p class="font-semibold text-on-surface text-sm">{{ $dokterTerpilih?->nama }}</p>
                            <p class="text-xs text-on-surface-variant">{{ $dokterTerpilih?->namaSpesialis() }}</p>
                        </div>
                    </div>

                    {{-- Info sesi ringkas --}}
                    <div class="flex items-center gap-4 mb-4 px-3 py-2.5 rounded-xl bg-surface-container/50 border border-outline-variant/20">
                        <span class="inline-flex items-center gap-1.5 text-xs text-on-surface-variant">
                            <span class="material-symbols-outlined text-[15px]">timer</span>
                            {{ $dokterTerpilih?->durasi_sesi_menit }} menit
                        </span>
                        <span class="w-px h-4 bg-outline-variant/30"></span>
                        <span class="inline-flex items-center gap-1.5 text-xs text-on-surface-variant">
                            <span class="material-symbols-outlined text-[15px]">payments</span>
                            Rp —
                        </span>
                    </div>

                    <p class="text-sm text-on-surface-variant mb-4">
                        Isi data berikut untuk memulai sesi konsultasi chat.
                    </p>

                    <form wire:submit="mulaiSesi" class="flex flex-col gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-on-surface-variant mb-1.5">Nama Anda</label>
                            <input type="text" wire:model="nama" placeholder="Contoh: Budi Santoso"
                                class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant/40 text-sm
                                       focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                            @error('nama')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-on-surface-variant mb-1.5">Nomor WhatsApp / Kontak</label>
                            <input type="text" wire:model="kontak" placeholder="Contoh: 081234567890"
                                class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant/40 text-sm
                                       focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                            @error('kontak')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        @error('dokterDipilih')
                            <p class="text-xs text-red-600">{{ $message }}</p>
                        @enderror

                        <button type="submit"
                            wire:loading.attr="disabled" wire:target="mulaiSesi"
                            class="w-full inline-flex items-center justify-center gap-1.5 py-2.5 rounded-lg text-sm font-semibold
                                   bg-primary text-on-primary hover:bg-primary/90 transition-colors duration-150
                                   disabled:opacity-60 disabled:cursor-not-allowed">
                            <span wire:loading.remove wire:target="mulaiSesi" class="material-symbols-outlined text-[16px]">chat</span>
                            <span wire:loading wire:target="mulaiSesi" class="material-symbols-outlined text-[16px] animate-spin">progress_activity</span>
                            Mulai Sesi Chat
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
