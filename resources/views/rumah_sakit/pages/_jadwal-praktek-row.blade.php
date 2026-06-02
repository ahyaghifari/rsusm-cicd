{{-- Partial: 1 baris dokter pada kartu jadwal praktek --}}
{{-- Variables: $sesi (JadwalPraktek), $warna (hex string) --}}
<div class="flex items-center gap-3 px-4 py-3">

    {{-- Avatar dokter --}}
    <div class="shrink-0">
        @if($sesi->dokter?->foto)
            <img
                src="{{ Storage::url($sesi->dokter->foto) }}"
                alt="{{ $sesi->nama_dokter ?? $sesi->dokter->nama }}"
                class="w-10 h-10 rounded-full object-cover"
                style="outline: 2px solid {{ $warna }}55; outline-offset: 2px;">
        @else
            <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0"
                 style="background-color: {{ $warna }}18; outline: 2px solid {{ $warna }}30; outline-offset: 2px;">
                <span class="material-symbols-outlined text-[18px]"
                      style="color: {{ $warna }};">person</span>
            </div>
        @endif
    </div>

    {{-- Info dokter + jam --}}
    <div class="flex-1 min-w-0">
        <p class="font-semibold text-sm text-on-surface truncate leading-snug">
            {{ $sesi->nama_dokter ?? $sesi->dokter?->nama ?? '—' }}
        </p>
        <div class="flex items-center gap-2 mt-0.5 flex-wrap">
            @if($sesi->waktu_mulai)
                <span class="flex items-center gap-1 text-xs text-on-surface-variant">
                    <span class="material-symbols-outlined text-[13px]"
                          style="color: {{ $warna }}; opacity: 0.75;">schedule</span>
                    <span class="tabular-nums font-medium">
                        {{ $sesi->waktu_mulai->format('H:i') }}
                        @if($sesi->waktu_selesai)
                            &ndash; {{ $sesi->waktu_selesai->format('H:i') }}
                        @else
                            &ndash; selesai
                        @endif
                    </span>
                </span>
            @endif
            @if($sesi->sesuai_perjanjian)
                <span class="inline-flex items-center gap-1 text-[10px] font-bold
                             text-amber-700 bg-amber-50 border border-amber-200
                             px-2 py-0.5 rounded-full shrink-0">
                    <span class="material-symbols-outlined text-[10px]">calendar_clock</span>
                    Perjanjian
                </span>
            @endif
        </div>
        @if($sesi->catatan)
            <p class="text-[11px] text-on-surface-variant/60 italic mt-0.5 leading-snug">
                {{ $sesi->catatan }}
            </p>
        @endif
    </div>

</div>
