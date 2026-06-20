<div>
    <x-page-hero title="Rawat Inap" subtitle="Pilih kelas kamar sesuai kebutuhan dan kenyamanan Anda" />

    {{-- Rail kelas + tautan cek ketersediaan --}}
    <div class="mt-7 w-10/12 mx-auto flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">

        @if($kelasOptions->isNotEmpty())
        <div>
            <span class="text-xs font-bold text-on-surface-variant/70 uppercase tracking-wide mb-2 block">Pilih Kelas Kamar</span>
            <div class="flex flex-wrap items-center gap-2">
                <button wire:click="$set('kelasFilter', null)" type="button"
                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full text-xs font-semibold border transition-all duration-200 hover:scale-105 active:scale-95
                           {{ ! $kelasFilter
                               ? 'bg-primary text-white border-primary shadow-md shadow-primary/25'
                               : 'bg-white text-on-surface-variant border-outline-variant hover:border-primary/40 hover:text-primary' }}">
                    <span class="material-symbols-outlined text-[16px]">grid_view</span>
                    Semua Kelas
                </button>
                @foreach($kelasOptions as $kelas)
                    @php $aktif = $kelasFilter === $kelas->id; @endphp
                    <button wire:click="$set('kelasFilter', {{ $kelas->id }})" type="button"
                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full text-xs font-semibold border transition-all duration-200 hover:scale-105 active:scale-95
                               {{ $aktif
                                   ? ($kelas->is_vip
                                       ? 'bg-amber-400 text-amber-900 border-amber-400 shadow-md shadow-amber-400/30 ring-2 ring-amber-200'
                                       : 'bg-primary text-white border-primary shadow-md shadow-primary/25')
                                   : 'bg-white text-on-surface-variant border-outline-variant hover:border-primary/40 hover:text-primary' }}">
                        <span class="material-symbols-outlined text-[16px] {{ ! $aktif && $kelas->is_vip ? 'text-amber-500' : '' }}"
                              @if($kelas->is_vip) style="font-variation-settings:'FILL' 1" @endif>
                            {{ $kelas->is_vip ? 'star' : 'bed' }}
                        </span>
                        {{ $kelas->nama }}
                    </button>
                @endforeach
            </div>
        </div>
        @endif

        <a wire:navigate href="{{ rumahsakit_route('rumahsakit.ketersediaan_rawat_inap') }}"
           class="inline-flex items-center gap-2 text-sm font-semibold text-tertiary hover:text-tertiary/80
                  transition-colors shrink-0 group {{ $kelasOptions->isEmpty() ? 'mx-auto' : '' }}">
            <span class="relative flex size-2">
                <span class="absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75 animate-ping"></span>
                <span class="relative inline-flex rounded-full size-2 bg-emerald-500"></span>
            </span>
            Cek ketersediaan kamar real-time
            <span class="material-symbols-outlined text-[16px] transition-transform group-hover:translate-x-0.5">arrow_forward</span>
        </a>
    </div>

    <div class="mt-10 w-10/12 mx-auto">
        @if($totalRooms === 0)
            <div class="flex flex-col items-center justify-center py-24 text-center">
                <div class="w-20 h-20 bg-primary/10 rounded-2xl flex items-center justify-center mx-auto mb-5">
                    <span class="material-symbols-outlined text-4xl text-primary">bed</span>
                </div>
                <p class="text-lg font-semibold text-on-surface">Tidak ada kamar yang sesuai kelas ini</p>
            </div>
        @elseif($hasGedung)
            @foreach($gedungs as $gedung)
                @continue($gedung->rawatInap->isEmpty())

                <section class="mt-20">
                    <div class="grid grid-cols-5 items-center gap-5">
                        <div class="h-0.5 bg-primary/50 w-full"></div>
                        <h2 class="text-4xl text-center font-bold col-span-3">Gedung <span class="text-primary">{{ $gedung->nama }}</span></h2>
                        <div class="h-0.5 bg-primary/50 w-full"></div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-10">

                        @foreach($gedung->rawatInap as $room)
                                <div data-aos="fade-up">
                                    <x-rawat-inap :rawat-inap="$room" />
                                </div>
                        @endforeach

                    </div>
                </section>

            @endforeach

        @else
                @foreach($rawatInap as $room)

                    <div data-aos="fade-up">
                        <x-rawat-inap :rawat-inap="$room" />
                    </div>

                @endforeach
        @endif
    </div>

    <script>
        function initCarousels() {
            // Tunggu sedikit agar DOM fully rendered
            setTimeout(() => {
                // HSE Carousel: auto-initialize semua elemen dengan [data-hs-carousel]
                if (window.HSStaticMethods && typeof window.HSStaticMethods.autoInit === 'function') {
                    window.HSStaticMethods.autoInit();
                }

                // Trigger lazy loading untuk gambar yang baru ditambahkan
                if (window.LazyLoad) {
                    window.LazyLoad.updateImages();
                }

                // Trigger AOS refresh untuk fade-up animations
                if (window.AOS && typeof window.AOS.refresh === 'function') {
                    window.AOS.refresh();
                }
            }, 100);
        }

        // Init on page load
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initCarousels);
        } else {
            initCarousels();
        }

        // Reinit saat Livewire update (filter diubah)
        document.addEventListener('livewire:updated', initCarousels);
    </script>
</div>
