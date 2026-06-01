{{-- Partial: 1 baris jadwal praktek dokter di halaman profil --}}
{{-- Variables: $sesi (JadwalPraktek), $warna (hex string) --}}
<div class="flex items-center gap-3 px-4 py-3">

    {{-- Nama poliklinik --}}
    <div class="flex-1 min-w-0">
        <p class="text-xs font-bold uppercase tracking-wide truncate"
           style="color: {{ $warna }};">
            {{ $sesi->poliklinik?->nama ?? '—' }}
        </p>
        <div class="flex items-center gap-2 mt-0.5 flex-wrap">
            {{-- Jam --}}
            @if($sesi->waktu_mulai)
                <span class="flex items-center gap-1 text-sm text-on-surface-variant">
                    <span class="material-symbols-outlined text-[14px]"
                          style="color: {{ $warna }}; opacity: 0.7;">schedule</span>
                    <span class="tabular-nums font-medium text-on-surface">
                        {{ $sesi->waktu_mulai->format('H:i') }}
                        @if($sesi->waktu_selesai)
                            &ndash; {{ $sesi->waktu_selesai->format('H:i') }}
                        @else
                            &ndash; Selesai
                        @endif
                    </span>
                </span>
            @else
                <span class="text-sm text-on-surface-variant italic">Jam menyesuaikan</span>
            @endif

            {{-- Badge perjanjian --}}
            @if($sesi->sesuai_perjanjian)
                <span class="inline-flex items-center gap-1 text-[10px] font-bold
                             text-amber-700 bg-amber-50 border border-amber-200
                             px-2 py-0.5 rounded-full shrink-0">
                    <span class="material-symbols-outlined text-[10px]">calendar_clock</span>
                    Perjanjian
                </span>
            @endif
        </div>

        {{-- Catatan --}}
        @if($sesi->catatan)
            <p class="text-xs text-on-surface-variant/60 italic mt-0.5 leading-snug">
                {{ $sesi->catatan }}
            </p>
        @endif
    </div>

</div>
