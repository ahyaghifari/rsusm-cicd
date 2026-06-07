<div>
    <x-page-hero
        title="Rawat Jalan"
        subtitle="Temukan berbagai layanan poliklinik spesialis yang tersedia untuk mendukung kesehatan Anda dan keluarga."
    />

    {{-- ============================================================ --}}
    {{-- KONTEN UTAMA --}}
    {{-- ============================================================ --}}
    <div class="w-11/12 lg:w-10/12 mx-auto py-16">

        @if($poliklinik->isEmpty())
            <div class="text-center py-24">
                <span class="material-symbols-outlined text-7xl text-outline/40 block mb-4">healing</span>
                <p class="text-on-surface-variant text-lg">Belum ada poliklinik tersedia.</p>
            </div>
        @else
            <div x-data
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-3"
                 x-transition:enter-end="opacity-100 translate-y-0">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 lg:gap-4">
                    @foreach($poliklinik as $poli)
                        <div
                            onclick="window.location='{{ route('rumahsakit.rawat_jalan_show', ['rumahsakit' => $rsSlug, 'poliklinik' => $poli->slug]) }}'"
                            wire:key="poli-card-{{ $poli->id }}"
                            class="group bg-white rounded-2xl shadow-sm border border-outline-variant/30
                                   hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300
                                   overflow-hidden cursor-pointer">

                            {{-- Aksen atas --}}
                            <div class="h-1 w-full shrink-0 bg-linear-to-r from-primary to-primary/50"></div>

                            {{-- Body: ikon kiri, nama + tombol kanan --}}
                            <div class="flex items-center gap-5 px-5 py-5">

                                {{-- Ikon --}}
                                <div class="w-16 h-16 rounded-2xl flex items-center justify-center shrink-0 overflow-hidden
                                            bg-primary/10 border border-primary/25">
                                    @if($poli->gambar)
                                        <img src="{{ Storage::url($poli->gambar) }}"
                                             alt="{{ $poli->nama }}"
                                             class="w-11 h-11 object-contain
                                                    group-hover:scale-105 transition-transform duration-300"
                                             loading="lazy">
                                    @else
                                        <span class="material-symbols-outlined text-[30px] text-primary
                                                     group-hover:scale-105 transition-transform duration-300">
                                            medical_services
                                        </span>
                                    @endif
                                </div>

                                {{-- Nama + Tombol --}}
                                <div class="flex flex-col gap-2 flex-1 min-w-0">
                                    <h3 class="font-bold text-on-surface text-sm leading-snug
                                               group-hover:text-primary transition-colors duration-200"
                                        style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                        {{ $poli->nama }}
                                    </h3>
                                    <span class="inline-flex items-center gap-1 text-xs font-semibold text-primary w-fit
                                                 group-hover:gap-2 transition-all duration-150">
                                        Lihat Detail
                                        <span class="material-symbols-outlined text-[13px]">arrow_forward</span>
                                    </span>
                                </div>

                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

    </div>
    {{-- END KONTEN UTAMA --}}
</div>
