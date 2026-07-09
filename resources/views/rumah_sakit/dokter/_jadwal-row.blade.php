{{-- Partial: 1 baris jadwal praktek dokter di halaman profil --}}
{{-- Variables: $sesi (JadwalPraktek), $warna (hex string), $rs (RumahSakit) --}}
@php $isExec = isset($rs) && $rs->executive_clinic && $sesi->is_executive; @endphp
<div class="flex items-start gap-3 px-4 py-3"
     @if($isExec) style="background-color: #e8cd84;" @endif>

    {{-- Nama poliklinik + jam --}}
    <div class="flex-1 min-w-0">
        <div class="flex items-center gap-2 flex-wrap">
            <p class="text-xs font-bold uppercase tracking-wide truncate"
               style="color: {{ $warna }};">
                {{ $sesi->poliklinik?->nama ?? '—' }}
            </p>
            @if($isExec)
                <span class="inline-flex items-center text-[10px] uppercase tracking-wide shrink-0"
                      style="color: #363c38; font-weight: 700; font-style: italic;">
                    Executive Clinic
                </span>
            @endif
        </div>
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
                <span class="inline-flex items-center gap-1 text-[11px] font-bold
                             text-green-700 bg-green-50 border border-green-200
                             px-2 py-0.5 rounded-full shrink-0">
                    <span class="material-symbols-outlined text-[11px]">calendar_clock</span>
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

        {{-- Perubahan hari ini --}}
        @if($perubahan ?? null)
            @php
                $isLibur = $perubahan->status_layanan === \App\Enums\StatusLayanan::LIBUR;
            @endphp
            <div class="mt-1.5 flex flex-wrap items-center gap-1.5">
                @if($isLibur)
                    <span class="inline-flex items-center gap-1 text-[11px] font-bold
                                 text-red-700 bg-red-50 border border-red-200
                                 px-2 py-0.5 rounded-full shrink-0">
                        <span class="material-symbols-outlined text-[11px]">event_busy</span>
                        Libur Hari Ini
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 text-[11px] font-bold
                                 text-amber-700 bg-amber-50 border border-amber-200
                                 px-2 py-0.5 rounded-full shrink-0">
                        <span class="material-symbols-outlined text-[11px]">schedule</span>
                        Jam berubah:
                        {{ $perubahan->jam_mulai?->format('H:i') ?? '?' }}
                        @if($perubahan->jam_selesai) &ndash; {{ $perubahan->jam_selesai->format('H:i') }} @endif
                    </span>
                @endif
                @if($perubahan->catatan)
                    <p class="text-xs text-on-surface-variant/60 italic w-full leading-snug">
                        {{ $perubahan->catatan }}
                    </p>
                @endif
            </div>
        @endif
    </div>

</div>
