@php
    use App\Enums\StatusSesiKonsultasi;
@endphp
<div>
    <x-page-hero
        title="Konsultasi dengan {{ $sesi->dokter->nama }}"
    />

    <div class="w-11/12 md:w-3/4 lg:w-2/3 xl:w-1/2 mx-auto py-12">
        <div class="border border-outline-variant/40 rounded-2xl overflow-hidden bg-white shadow-sm
                    flex flex-col h-[70vh] min-h-[480px]">

            {{-- Header sesi --}}
            <div class="px-4 py-3 border-b border-outline-variant/30 flex items-center gap-3 shrink-0 bg-surface-container/40">
                <div class="w-10 h-10 rounded-full bg-surface-container overflow-hidden flex items-center justify-center shrink-0">
                    @if($sesi->dokter->foto)
                        <img src="{{ Storage::url($sesi->dokter->foto) }}" alt="{{ $sesi->dokter->nama }}" class="w-full h-full object-cover">
                    @else
                        <span class="material-symbols-outlined text-xl text-on-surface-variant/40">person</span>
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-on-surface truncate">{{ $sesi->dokter->nama }}</p>
                    <p class="text-xs text-on-surface-variant truncate">{{ $sesi->dokter->namaSpesialis() }}</p>
                </div>

                {{-- Badge status --}}
                <span @class([
                    'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold border shrink-0',
                    'bg-amber-100 text-amber-700 border-amber-200' => $sesi->status === StatusSesiKonsultasi::MENUNGGU,
                    'bg-green-100 text-green-700 border-green-200' => $sesi->status === StatusSesiKonsultasi::BERLANGSUNG,
                    'bg-gray-100 text-gray-500 border-gray-200'    => in_array($sesi->status, [StatusSesiKonsultasi::SELESAI, StatusSesiKonsultasi::KEDALUWARSA]),
                ])>
                    {{ $sesi->status->getLabel() }}
                </span>
            </div>

            {{-- ── MENUNGGU: ruang tunggu ─────────────────────────────── --}}
            @if($sesi->status === StatusSesiKonsultasi::MENUNGGU)
                <div class="flex-1 flex flex-col items-center justify-center text-center p-8 gap-4">
                    <div class="relative w-16 h-16">
                        <span class="absolute inset-0 rounded-full bg-amber-200/60 animate-ping"></span>
                        <div class="relative w-16 h-16 rounded-full bg-amber-100 border border-amber-200 flex items-center justify-center">
                            <span class="material-symbols-outlined text-amber-600 text-2xl">hourglass_top</span>
                        </div>
                    </div>
                    <div>
                        <p class="font-semibold text-on-surface">Menunggu dokter menerima sesi Anda…</p>
                        <p class="text-sm text-on-surface-variant mt-1.5 max-w-sm">
                            Halaman ini akan otomatis berpindah ke ruang chat begitu
                            <span class="font-medium">{{ $sesi->dokter->nama }}</span> menerima permintaan Anda.
                            Mohon jangan tutup halaman ini.
                        </p>
                    </div>
                    <p class="text-xs text-on-surface-variant/60">
                        Sesi dibuat untuk: <span class="font-medium">{{ $sesi->nama_pasien }}</span>
                    </p>
                </div>

            {{-- ── BERLANGSUNG: chat aktif ────────────────────────────── --}}
            @elseif($sesi->status === StatusSesiKonsultasi::BERLANGSUNG)
                <div
                    class="px-4 py-2 border-b border-outline-variant/20 bg-green-50/60 flex items-center justify-between shrink-0"
                    x-data="konsultasiTimer('{{ $sesi->berakhir_at?->toIso8601String() }}')"
                    x-init="start()"
                >
                    <p class="text-xs text-green-700 flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[14px]">timer</span>
                        Sisa waktu sesi
                    </p>
                    <p class="text-sm font-semibold tabular-nums" :class="habis ? 'text-red-600' : 'text-green-700'" x-text="label"></p>
                </div>

                <div
                    id="chat-msgs"
                    class="flex-1 overflow-y-auto p-4 flex flex-col gap-3"
                    x-init="
                        const el = $el;
                        el.scrollTop = el.scrollHeight;
                        const observer = new MutationObserver(() => {
                            el.scrollTo({ top: el.scrollHeight, behavior: 'smooth' });
                        });
                        observer.observe(el, { childList: true, subtree: true });
                    "
                >
                    <div class="bg-blue-50 border border-blue-200 rounded-[9px] p-2.5 text-[11.5px] text-blue-700/80 flex items-start gap-1.5 shrink-0">
                        <span class="material-symbols-outlined text-blue-600 text-[16px] shrink-0">info</span>
                        <span>Sesi sedang berlangsung. Layanan ini bersifat informasi umum dan bukan pengganti pemeriksaan langsung.</span>
                    </div>

                    @foreach($riwayat as $p)
                        @if($p['pengirim'] === 'PASIEN')
                        <div class="flex flex-row-reverse gap-2 items-end shrink-0" data-msg="pasien">
                            <div class="flex flex-col items-end max-w-[80%]">
                                <div class="px-3 py-2 rounded-[15px] rounded-br-[4px] bg-primary text-on-primary text-[13px] leading-snug break-words w-full">
                                    {{ $p['isi'] }}
                                </div>
                                <div class="text-[10px] text-on-surface-variant/60 mt-0.5">{{ \Illuminate\Support\Carbon::parse($p['created_at'])->format('H:i') }}</div>
                            </div>
                        </div>
                        @else
                        <div class="flex gap-2 items-end shrink-0" data-msg="dokter">
                            <div class="w-7 h-7 rounded-full bg-surface-container overflow-hidden flex items-center justify-center shrink-0 mb-4">
                                @if($sesi->dokter->foto)
                                    <img src="{{ Storage::url($sesi->dokter->foto) }}" alt="" class="w-full h-full object-cover">
                                @else
                                    <span class="material-symbols-outlined text-[14px] text-on-surface-variant/40">person</span>
                                @endif
                            </div>
                            <div class="flex flex-col max-w-[80%]">
                                <div class="px-3 py-2 rounded-[15px] rounded-bl-[4px] bg-surface-container border border-outline-variant/30 text-[13px] leading-snug text-on-surface break-words">
                                    {{ $p['isi'] }}
                                </div>
                                <div class="text-[10px] text-on-surface-variant/60 mt-0.5">{{ \Illuminate\Support\Carbon::parse($p['created_at'])->format('H:i') }}</div>
                            </div>
                        </div>
                        @endif
                    @endforeach
                </div>

                {{-- Input footer --}}
                <form
                    wire:submit="kirim"
                    class="border-t border-outline-variant/30 p-3 shrink-0 flex gap-2 items-center"
                >
                    <input
                        type="text"
                        wire:model="pesanBaru"
                        wire:keydown.enter.prevent="kirim"
                        placeholder="Ketik pesan Anda…"
                        maxlength="1000"
                        wire:loading.attr="disabled"
                        wire:target="kirim"
                        class="flex-1 border border-outline-variant/40 rounded-full px-4 py-2 text-sm
                               focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary
                               disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                    <button type="submit"
                        wire:loading.attr="disabled" wire:target="kirim"
                        class="w-10 h-10 rounded-full bg-primary hover:bg-primary/90 text-on-primary flex items-center justify-center
                               shrink-0 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                        <span wire:loading.remove wire:target="kirim" class="material-symbols-outlined text-[18px]">send</span>
                        <span wire:loading wire:target="kirim" class="material-symbols-outlined text-[18px] animate-spin">progress_activity</span>
                    </button>
                </form>
                @error('pesanBaru')
                    <p class="text-xs text-red-600 px-4 pb-2">{{ $message }}</p>
                @enderror

            {{-- ── SELESAI / KEDALUWARSA: transkrip read-only ─────────── --}}
            @else
                <div class="px-4 py-3 border-b border-outline-variant/20 bg-gray-50 shrink-0">
                    <p class="text-sm font-medium text-on-surface">
                        @if($sesi->status === StatusSesiKonsultasi::SELESAI)
                            Sesi konsultasi telah selesai.
                        @else
                            Sesi ini telah kedaluwarsa karena tidak direspons tepat waktu.
                        @endif
                    </p>
                    <p class="text-xs text-on-surface-variant mt-0.5">Berikut transkrip percakapan Anda (jika ada).</p>
                </div>

                <div class="flex-1 overflow-y-auto p-4 flex flex-col gap-3">
                    @forelse($riwayat as $p)
                        @if($p['pengirim'] === 'PASIEN')
                        <div class="flex flex-row-reverse gap-2 items-end shrink-0">
                            <div class="flex flex-col items-end max-w-[80%]">
                                <div class="px-3 py-2 rounded-[15px] rounded-br-[4px] bg-surface-container border border-outline-variant/30 text-[13px] leading-snug text-on-surface break-words w-full">
                                    {{ $p['isi'] }}
                                </div>
                                <div class="text-[10px] text-on-surface-variant/60 mt-0.5">{{ \Illuminate\Support\Carbon::parse($p['created_at'])->format('d M Y, H:i') }}</div>
                            </div>
                        </div>
                        @else
                        <div class="flex gap-2 items-end shrink-0">
                            <div class="w-7 h-7 rounded-full bg-surface-container overflow-hidden flex items-center justify-center shrink-0 mb-4">
                                @if($sesi->dokter->foto)
                                    <img src="{{ Storage::url($sesi->dokter->foto) }}" alt="" class="w-full h-full object-cover">
                                @else
                                    <span class="material-symbols-outlined text-[14px] text-on-surface-variant/40">person</span>
                                @endif
                            </div>
                            <div class="flex flex-col max-w-[80%]">
                                <div class="px-3 py-2 rounded-[15px] rounded-bl-[4px] bg-white border border-outline-variant/30 text-[13px] leading-snug text-on-surface break-words">
                                    {{ $p['isi'] }}
                                </div>
                                <div class="text-[10px] text-on-surface-variant/60 mt-0.5">{{ \Illuminate\Support\Carbon::parse($p['created_at'])->format('d M Y, H:i') }}</div>
                            </div>
                        </div>
                        @endif
                    @empty
                        <div class="flex-1 flex flex-col items-center justify-center text-center py-12">
                            <span class="material-symbols-outlined text-3xl text-on-surface-variant/30 mb-2">forum</span>
                            <p class="text-sm text-on-surface-variant/60">Tidak ada percakapan yang tercatat pada sesi ini.</p>
                        </div>
                    @endforelse
                </div>
            @endif
        </div>

        <p class="text-xs text-on-surface-variant/60 text-center mt-6">
            Layanan ini bersifat informasi umum dan bukan pengganti pemeriksaan langsung oleh dokter.
            Simpan tautan halaman ini untuk kembali ke sesi Anda.
        </p>
    </div>
</div>

<script>
    function konsultasiTimer(berakhirAt) {
        return {
            label: '--:--',
            habis: false,
            target: berakhirAt ? new Date(berakhirAt).getTime() : null,
            timer: null,
            start() {
                if (! this.target) return;
                this.tick();
                this.timer = setInterval(() => this.tick(), 1000);
            },
            tick() {
                const diff = Math.max(0, Math.floor((this.target - Date.now()) / 1000));
                const m = Math.floor(diff / 60);
                const s = diff % 60;
                this.label = String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
                if (diff <= 0) {
                    this.habis = true;
                    clearInterval(this.timer);
                }
            }
        };
    }
</script>
