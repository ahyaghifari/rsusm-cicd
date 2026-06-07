{{-- Partial: satu baris dokter dengan semua sesi jamnya --}}
{{-- Variables: $sesiGroup (Collection<JadwalPraktek>), $warna (hex string) --}}
@php
    $sesi      = $sesiGroup->first();
    $dokterUrl = $sesi->dokter?->slug
        ? route('rumahsakit.dokter_show', ['rumahsakit' => $rs->slug, 'dokter' => $sesi->dokter->slug])
        : null;
@endphp

@if($dokterUrl)
<a wire:navigate href="{{ $dokterUrl }}"
   class="flex items-start gap-3 px-4 py-3 hover:bg-black/4 transition-colors duration-150 group">
@else
<div class="flex items-start gap-3 px-4 py-3">
@endif

    {{-- Avatar dokter --}}
    <div class="shrink-0 pt-0.5">
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

    {{-- Info dokter + semua jam --}}
    <div class="flex-1 min-w-0">
        <p class="font-semibold text-sm text-on-surface truncate leading-snug
                  {{ $dokterUrl ? 'group-hover:text-primary transition-colors duration-150' : '' }}">
            {{ $sesi->nama_dokter ?? $sesi->dokter?->nama ?? '—' }}
            @if($dokterUrl)
                <span class="material-symbols-outlined text-[11px] opacity-40 group-hover:opacity-70 align-middle ml-0.5">open_in_new</span>
            @endif
        </p>

        {{-- Semua sesi jam: iterasi seluruh jadwal dokter ini --}}
        <div class="flex flex-wrap gap-1.5 mt-1">
            @foreach($sesiGroup as $s)
                @php $isExec = isset($rs) && $rs->executive_clinic && $s->is_executive; @endphp

                @if($s->waktu_mulai)
                    @if($isExec)
                        <span class="inline-flex items-center gap-1 text-xs font-medium shrink-0 rounded-full px-2 py-0.5"
                              style="background-color: #e8cd84; color: #363c38;">
                            <span class="font-bold italic">Executive Clinic</span>
                            <span class="opacity-50">·</span>
                            <span class="tabular-nums">
                                {{ $s->waktu_mulai->format('H:i') }}
                                @if($s->waktu_selesai)
                                    &ndash; {{ $s->waktu_selesai->format('H:i') }}
                                @else
                                    &ndash; selesai
                                @endif
                            </span>
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 text-xs font-medium shrink-0 text-on-surface-variant">
                            <span class="material-symbols-outlined text-[13px]"
                                  style="color: {{ $warna }}; opacity: 0.75;">schedule</span>
                            <span class="tabular-nums">
                                {{ $s->waktu_mulai->format('H:i') }}
                                @if($s->waktu_selesai)
                                    &ndash; {{ $s->waktu_selesai->format('H:i') }}
                                @else
                                    &ndash; selesai
                                @endif
                            </span>
                        </span>
                    @endif
                @endif

                @if($s->sesuai_perjanjian)
                    <span class="inline-flex items-center gap-1 text-[10px] font-bold
                                 text-green-700 bg-green-50 border border-green-200
                                 px-2 py-0.5 rounded-full shrink-0">
                        <span class="material-symbols-outlined text-[10px]">calendar_clock</span>
                        Perjanjian
                    </span>
                @endif
            @endforeach
        </div>

        @if($sesi->catatan)
            <p class="text-[11px] text-on-surface-variant/60 italic mt-0.5 leading-snug">
                {{ $sesi->catatan }}
            </p>
        @endif
    </div>

@if($dokterUrl)
</a>
@else
</div>
@endif
