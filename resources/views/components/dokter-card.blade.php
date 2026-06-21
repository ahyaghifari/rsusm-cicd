@props([
    'dokter',
    'rumahsakitSlug',
    'delay' => 0,
])

<div {{ $attributes->class([
        'p-[1.5px] rounded-2xl animate-fade-in bg-outline-variant/25
         hover:bg-linear-to-br hover:from-amber-400 hover:via-primary hover:to-amber-300
         shadow-sm hover:shadow-2xl hover:-translate-y-1.5 transition-all duration-400',
    ]) }}
     style="animation-delay: {{ $delay }}ms">
    <a wire:navigate href="{{ route('rumahsakit.dokter_show', [$rumahsakitSlug, $dokter->slug]) }}"
       class="group bg-white rounded-2xl overflow-hidden flex flex-col h-full">

        {{-- Foto dengan badge spesialis --}}
        <div class="relative overflow-hidden h-72 bg-gray-100 shrink-0">
            <img src="{{ Storage::url($dokter->foto) }}" alt="{{ $dokter->nama }}"
                 class="w-full h-full object-contain object-bottom
                        group-hover:scale-105 transition-transform duration-700"
                 loading="lazy">

            <div class="absolute bottom-0 left-0 right-0 px-4 pb-3">
                <span class="inline-flex items-center bg-linear-to-r from-amber-500 to-amber-400
                             text-white text-[11px] font-bold uppercase tracking-wider
                             px-2.5 py-1 rounded-full shadow-sm max-w-full truncate">
                    {{ $dokter->namaSpesialis() }}
                </span>
            </div>
        </div>

        {{-- Info --}}
        <div class="p-5 flex flex-col flex-1">
            <h3 class="font-bold text-on-surface text-base md:text-lg leading-snug mb-2
                       group-hover:text-primary transition-colors duration-200">
                {{ $dokter->nama }}
            </h3>

            @if($dokter->deskripsi)
                <p class="text-sm text-on-surface-variant/80 leading-relaxed line-clamp-2 flex-1">
                    {{ \Illuminate\Support\Str::limit(strip_tags($dokter->deskripsi), 120) }}
                </p>
            @else
                <div class="flex-1"></div>
            @endif

            {{-- Footer --}}
            <div class="flex items-center justify-between mt-4 pt-3 border-t border-outline-variant/20">
                <span class="text-xs text-on-surface-variant uppercase tracking-widest font-semibold">
                    {{ $dokter->spesialis?->nama ? 'SPESIALIS' : 'UMUM' }}
                </span>
                <span class="inline-flex items-center gap-1 text-sm font-bold text-primary
                             group-hover:gap-2 transition-all duration-200">
                    Lihat Profil
                    <span class="material-symbols-outlined text-[15px]">arrow_forward</span>
                </span>
            </div>
        </div>
    </a>
</div>
