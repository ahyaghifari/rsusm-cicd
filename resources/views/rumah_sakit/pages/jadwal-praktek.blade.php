<div>
    <x-page-hero
        title="Jadwal Praktek"
        subtitle="Cek jadwal dokter yang berpraktek dan temukan dokter yang Anda butuhkan."
    />

    <div class="w-10/12 mx-auto py-10">

        {{-- ============================================================ --}}
        {{-- TAB HARI (tengah) --}}
        {{-- ============================================================ --}}
        <div class="mb-8 text-center">
            <p class="text-xs text-on-surface-variant uppercase tracking-widest font-semibold mb-3">Pilih Hari</p>
            <div class="inline-flex flex-wrap justify-center gap-2" role="tablist">
                @foreach($hariList as $hari)
                    @php
                        $isActive = $activeHari === $hari;
                        $isToday  = $hariHariIni === $hari;
                        $label    = ucfirst(strtolower($hari));
                    @endphp
                    <button
                        wire:click="setHari('{{ $hari }}')"
                        role="tab"
                        class="relative inline-flex items-center gap-1.5 px-5 py-2.5 rounded-full font-semibold text-sm
                               transition-all duration-200 focus:outline-none
                               {{ $isActive
                                   ? 'bg-primary text-white shadow-lg shadow-primary/25'
                                   : 'bg-surface-container text-on-surface-variant hover:bg-primary/10 hover:text-primary border border-outline-variant' }}">
                        {{ $label }}
                        @if($isToday && $isActive)
                            <span class="w-2 h-2 rounded-full bg-yellow-400 inline-block shrink-0"></span>
                        @elseif($isToday)
                            <span class="w-2 h-2 rounded-full bg-primary inline-block shrink-0"></span>
                        @endif
                    </button>
                @endforeach
            </div>
        </div>

        {{-- ============================================================ --}}
        {{-- FILTER SPESIALIS --}}
        {{-- ============================================================ --}}
        <div class="flex justify-center mb-8">
            <div class="relative w-full max-w-sm">
                <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant text-[20px] pointer-events-none">stethoscope</span>
                <select
                    wire:model.live="spesialisId"
                    class="w-full pl-12 pr-4 py-3 border border-outline-variant rounded-xl bg-white text-on-surface
                           focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition
                           text-sm appearance-none cursor-pointer">
                    <option value="">— Semua Spesialis —</option>
                    @foreach($spesialisList as $sp)
                        <option value="{{ $sp->id }}">{{ $sp->nama }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- ============================================================ --}}
        {{-- RESULT COUNT --}}
        {{-- ============================================================ --}}
        <div class="flex items-center gap-3 mb-6">
            <div class="h-px flex-1 bg-outline-variant/30"></div>
            <p class="text-xs text-on-surface-variant shrink-0">
                <span class="font-semibold text-primary">{{ $jadwal->count() }}</span>
                dokter praktek hari
                <span class="font-semibold text-on-surface">{{ ucfirst(strtolower($activeHari)) }}</span>
            </p>
            <div class="h-px flex-1 bg-outline-variant/30"></div>
        </div>

        {{-- ============================================================ --}}
        {{-- GRID DOKTER --}}
        {{-- ============================================================ --}}
        @if($jadwal->isEmpty())

            <div class="text-center py-24">
                <span class="material-symbols-outlined text-7xl text-outline/40 block mb-4">calendar_today</span>
                <p class="text-on-surface font-semibold text-xl mb-2">Tidak ada jadwal praktek</p>
                <p class="text-on-surface-variant text-sm max-w-sm mx-auto leading-relaxed">
                    Tidak ada dokter yang berpraktek hari
                    <span class="font-semibold text-on-surface">{{ ucfirst(strtolower($activeHari)) }}</span>
                    @if($spesialisId) dengan spesialis yang dipilih @endif.
                </p>
            </div>

        @else

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
                @foreach($jadwal as $item)
                    @php $dokter = $item->dokter; @endphp
                    <div
                        data-aos="fade-up"
                        data-aos-delay="{{ $loop->index * 50 }}"
                        wire:key="jadwal-{{ $item->id }}"
                        class="group bg-white rounded-2xl shadow-sm border border-outline-variant/30
                               hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col overflow-hidden">

                        {{-- Accent bar --}}
                        <div class="h-1 w-full bg-linear-to-r from-primary to-secondary shrink-0"></div>

                        {{-- Body --}}
                        <div class="p-5 flex flex-col items-center text-center flex-1">

                            {{-- Avatar --}}
                            <div class="relative mb-4 shrink-0">
                                @if($dokter->foto)
                                    <img
                                        src="{{ Storage::url($dokter->foto) }}"
                                        alt="{{ $dokter->nama }}"
                                        class="w-20 h-20 rounded-full object-cover ring-2 ring-primary/20
                                               group-hover:ring-primary/50 transition-all duration-300">
                                @else
                                    <div class="w-20 h-20 rounded-full bg-primary/10 flex items-center justify-center
                                                ring-2 ring-primary/20 group-hover:ring-primary/50 transition-all duration-300">
                                        <span class="material-symbols-outlined text-3xl text-primary/60">person</span>
                                    </div>
                                @endif
                            </div>

                            {{-- Nama --}}
                            <h3 class="font-bold text-on-surface text-sm leading-snug
                                       group-hover:text-primary transition-colors duration-200 mb-2">
                                {{ $dokter->nama }}
                            </h3>

                            {{-- Spesialis --}}
                            <span class="inline-flex items-center gap-1 text-xs font-semibold text-primary
                                         bg-primary/10 px-2.5 py-1 rounded-full mb-4">
                                <span class="material-symbols-outlined text-[12px]">stethoscope</span>
                                {{ $dokter->spesialis->nama }}
                            </span>

                            {{-- Jam + badge sesuai perjanjian (inline di desktop) --}}
                            <div class="flex flex-col sm:flex-row items-center justify-center gap-2 flex-wrap">
                                <div class="flex items-center gap-1.5 text-sm text-on-surface-variant">
                                    <span class="material-symbols-outlined text-[16px] text-primary/70">schedule</span>
                                    <span class="font-medium tabular-nums">
                                        {{ \Carbon\Carbon::parse($item->waktu_mulai)->format('H:i') }}
                                        @if($item->waktu_selesai)
                                            &ndash; {{ \Carbon\Carbon::parse($item->waktu_selesai)->format('H:i') }}
                                        @else
                                            &ndash; selesai
                                        @endif
                                    </span>
                                </div>
                                @if($item->sesuai_perjanjian)
                                    <span class="inline-flex items-center gap-1 text-xs font-semibold text-amber-700
                                                 bg-amber-50 border border-amber-200 px-2.5 py-1 rounded-full shrink-0">
                                        <span class="material-symbols-outlined text-[12px]">calendar_clock</span>
                                        Perjanjian
                                    </span>
                                @endif
                            </div>

                        </div>

                        {{-- Footer: tombol profil --}}
                        <div class="px-5 pb-5 pt-0">
                            <div class="h-px bg-outline-variant/20 mb-4"></div>
                            <a
                                href="{{ route('rumahsakit.dokter_show', ['rumahsakit' => $rsSlug, 'dokter' => $dokter->slug]) }}"
                                class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl
                                       bg-primary/5 hover:bg-primary text-primary hover:text-white
                                       font-semibold text-sm transition-all duration-200 group/btn">
                                <span class="material-symbols-outlined text-[16px]">person</span>
                                Lihat Profil
                                <span class="material-symbols-outlined text-[14px] group-hover/btn:translate-x-0.5 transition-transform duration-150">arrow_forward</span>
                            </a>
                        </div>

                    </div>
                @endforeach
            </div>

        @endif

    </div>
</div>
