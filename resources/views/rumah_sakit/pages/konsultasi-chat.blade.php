@php
    use App\Enums\StatusSesiKonsultasi;
@endphp
<div>
    <x-page-hero
        title="Konsultasi dengan {{ $sesi->dokter->nama }}"
    />

    <div class="w-11/12 md:w-3/4 lg:w-2/3 xl:w-1/2 mx-auto py-12">

        {{-- ── Banner izin push notification ─────────────────────────────── --}}
        @if($sesi->status === StatusSesiKonsultasi::MENUNGGU || $sesi->status === StatusSesiKonsultasi::BERLANGSUNG)
        <div
            x-data="pushPermisiBanner('{{ $sesi->token }}')"
            x-init="periksa()"
            x-show="tampil"
            x-transition:enter="transition ease-out duration-500"
            x-transition:enter-start="opacity-0 -translate-y-2 scale-[0.98]"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 -translate-y-2 scale-[0.98]"
            class="mb-4 rounded-2xl overflow-hidden shadow-lg"
            style="background: linear-gradient(135deg, #6d28d9 0%, #4338ca 100%); display: none;"
        >
            {{-- Lingkaran dekoratif --}}
            <div class="relative px-5 py-4 flex gap-4 items-center" style="overflow: hidden;">
                <div class="absolute -top-6 -right-6 w-28 h-28 rounded-full bg-white/5 pointer-events-none"></div>
                <div class="absolute -bottom-8 left-1/3 w-36 h-36 rounded-full bg-white/5 pointer-events-none"></div>

                {{-- Ikon lonceng --}}
                <div class="shrink-0 w-12 h-12 rounded-2xl bg-white/20 backdrop-blur flex items-center justify-center shadow-inner">
                    <span class="material-symbols-outlined text-white text-[26px]"
                          style="font-variation-settings: 'FILL' 1; animation: bellRing 2.4s ease-in-out infinite;">
                        notifications_active
                    </span>
                </div>

                {{-- Teks --}}
                <div class="flex-1 min-w-0">
                    <p class="font-bold text-white text-sm leading-snug">Aktifkan Notifikasi Chat</p>
                    <p class="text-white/70 text-[11.5px] mt-0.5 leading-relaxed">
                        Agar kamu tahu saat dokter membalas — meski berpindah halaman atau menutup browser.
                    </p>
                    <div class="flex items-center gap-3 mt-2.5">
                        <button
                            @click="aktifkan()"
                            class="px-4 py-1.5 bg-white text-violet-700 text-xs font-bold rounded-full
                                   hover:bg-white/90 active:scale-95 transition-all duration-150 shadow-sm"
                        >
                            Aktifkan
                        </button>
                        <button
                            @click="lewati()"
                            class="text-white/60 hover:text-white text-xs transition-colors duration-150"
                        >
                            Lewati
                        </button>
                    </div>
                </div>

                {{-- Tombol tutup --}}
                <button
                    @click="lewati()"
                    class="shrink-0 self-start text-white/40 hover:text-white/80 transition-colors duration-150 -mt-0.5"
                >
                    <span class="material-symbols-outlined text-[18px]">close</span>
                </button>
            </div>
        </div>
        @endif

        <div class="border border-outline-variant/40 rounded-2xl overflow-hidden bg-white shadow-sm
                    flex flex-col h-[70vh] min-h-[480px]">

            {{-- Header sesi --}}
            <div class="px-4 py-3 border-b border-outline-variant/30 flex items-center gap-3 shrink-0 bg-surface-container/40">
                <div class="w-10 h-10 rounded-full bg-surface-container overflow-hidden flex items-center justify-center shrink-0">
                    @if($sesi->dokter->foto)
                        <img src="{{ Storage::url($sesi->dokter->foto) }}" alt="{{ $sesi->dokter->nama }}" class="w-full h-full object-contain">
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
                <div wire:key="konsultasi-status-{{ $sesi->status->value }}" class="flex-1 flex flex-col items-center justify-center text-center p-8 gap-4">
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
                    wire:key="konsultasi-status-{{ $sesi->status->value }}"
                    x-data="konsultasiTimer('{{ $sesi->berakhir_at?->toIso8601String() }}')"
                    x-init="start()"
                    class="flex flex-col flex-1 min-h-0"
                >
                {{-- Timer bar --}}
                <div class="px-4 py-2 border-b border-outline-variant/20 bg-green-50/60 flex items-center justify-between shrink-0">
                    <p class="text-xs text-green-700 flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[14px]">timer</span>
                        Sisa waktu sesi
                    </p>
                    <p class="text-sm font-semibold tabular-nums" :class="habis ? 'text-red-600' : 'text-green-700'" x-text="label"></p>
                </div>

                {{-- Overlay waktu habis --}}
                <div x-show="habis" style="display: none" class="flex-1 flex flex-col items-center justify-center text-center p-8 gap-4">
                    <div class="relative w-16 h-16">
                        <div class="w-16 h-16 rounded-full bg-orange-100 border border-orange-200 flex items-center justify-center">
                            <span class="material-symbols-outlined text-orange-500 text-2xl">timer_off</span>
                        </div>
                    </div>
                    <div>
                        <p class="font-semibold text-on-surface">Waktu konsultasi telah habis</p>
                        <p class="text-sm text-on-surface-variant mt-1.5 max-w-sm">
                            Menunggu dokter menutup sesi dan memberikan catatan…
                        </p>
                    </div>
                </div>

                <div
                    id="chat-msgs"
                    x-show="!habis"
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
                                <div class="px-3 py-2 rounded-[15px] rounded-br-sm bg-primary text-on-primary text-[13px] leading-snug wrap-break-word w-full">
                                    {{ $p['isi'] }}
                                </div>
                                <div class="text-[10px] text-on-surface-variant/60 mt-0.5">{{ \Illuminate\Support\Carbon::parse($p['created_at'])->format('H:i') }}</div>
                            </div>
                        </div>
                        @else
                        <div class="flex gap-2 items-end shrink-0" data-msg="dokter">
                            <div class="w-7 h-7 rounded-full bg-surface-container overflow-hidden flex items-center justify-center shrink-0 mb-4">
                                @if($sesi->dokter->foto)
                                    <img src="{{ Storage::url($sesi->dokter->foto) }}" alt="" class="w-full h-full object-contain">
                                @else
                                    <span class="material-symbols-outlined text-[14px] text-on-surface-variant/40">person</span>
                                @endif
                            </div>
                            <div class="flex flex-col max-w-[80%]">
                                <div class="px-3 py-2 rounded-[15px] rounded-bl-sm bg-surface-container border border-outline-variant/30 text-[13px] leading-snug text-on-surface wrap-break-word">
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
                    x-show="!habis"
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
                    <p x-show="!habis" class="text-xs text-red-600 px-4 pb-2">{{ $message }}</p>
                @enderror
                </div>{{-- /konsultasiTimer wrapper --}}

            {{-- ── SELESAI / KEDALUWARSA: transkrip read-only ─────────── --}}
            @else
                <div wire:key="konsultasi-status-{{ $sesi->status->value }}" class="contents">
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
                                <div class="px-3 py-2 rounded-[15px] rounded-br-sm bg-surface-container border border-outline-variant/30 text-[13px] leading-snug text-on-surface wrap-break-word w-full">
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
                                <div class="px-3 py-2 rounded-[15px] rounded-bl-sm bg-white border border-outline-variant/30 text-[13px] leading-snug text-on-surface wrap-break-word">
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

                {{-- Catatan / kesimpulan dokter --}}
                @if($sesi->kesimpulan)
                <div class="shrink-0 border-t border-blue-100 bg-blue-50/70 px-4 py-4">
                    <div class="flex items-start gap-2.5">
                        <div class="w-7 h-7 rounded-full bg-blue-100 border border-blue-200 flex items-center justify-center shrink-0 mt-0.5">
                            @if($sesi->dokter->foto)
                                <img src="{{ Storage::url($sesi->dokter->foto) }}" alt="" class="w-full h-full object-cover rounded-full">
                            @else
                                <span class="material-symbols-outlined text-[14px] text-blue-500">medical_services</span>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-[11px] font-semibold text-blue-700 mb-1">
                                Catatan dari {{ $sesi->dokter->nama }}
                            </p>
                            <p class="text-sm text-blue-900 leading-snug whitespace-pre-line">{{ $sesi->kesimpulan }}</p>
                        </div>
                    </div>
                </div>
                @endif
                </div>
            @endif
        </div>

        <p class="text-xs text-on-surface-variant/60 text-center mt-6">
            Layanan ini bersifat informasi umum dan bukan pengganti pemeriksaan langsung oleh dokter.
            Simpan tautan halaman ini untuk kembali ke sesi Anda.
        </p>
    </div>

<style>
    @keyframes bellRing {
        0%, 100% { transform: rotate(0deg); }
        10%       { transform: rotate(14deg); }
        20%       { transform: rotate(-10deg); }
        30%       { transform: rotate(10deg); }
        40%       { transform: rotate(-6deg); }
        50%       { transform: rotate(4deg); }
        60%       { transform: rotate(0deg); }
    }
</style>

<script>
    // ── Push Notification Setup ──────────────────────────────────────────────

    (function initKonsultasiPush() {
        const SESI_KEY = 'konsultasi_sesi';

        // Simpan data sesi ke localStorage agar global listener bisa subscribe
        @if($sesi->status !== \App\Enums\StatusSesiKonsultasi::SELESAI && $sesi->status !== \App\Enums\StatusSesiKonsultasi::KEDALUWARSA)
        localStorage.setItem(SESI_KEY, JSON.stringify({
            token:      '{{ $sesi->token }}',
            url:        '{{ url()->current() }}',
            dokterNama: '{{ addslashes($sesi->dokter->nama) }}'
        }));
        @else
        localStorage.removeItem(SESI_KEY);
        @endif

        // Hapus dari localStorage saat sesi berakhir via WebSocket
        window.addEventListener('sesi-berakhir', () => localStorage.removeItem(SESI_KEY));

        // Returning user: izin sudah pernah diberikan → langsung subscribe tanpa banner
        @if($sesi->status === \App\Enums\StatusSesiKonsultasi::BERLANGSUNG || $sesi->status === \App\Enums\StatusSesiKonsultasi::MENUNGGU)
        if (typeof Notification !== 'undefined' && Notification.permission === 'granted') {
            daftarPushSubscription();
        }
        @endif
    })();

    // Dipanggil oleh banner saat user klik "Aktifkan", atau oleh IIFE jika izin sudah ada
    async function daftarPushSubscription() {
        if (!('serviceWorker' in navigator) || !('PushManager' in window)) return;
        try {
            const reg  = await navigator.serviceWorker.register('/sw.js');
            const perm = Notification.permission === 'granted'
                ? 'granted'
                : await Notification.requestPermission();

            if (perm !== 'granted') return;

            let sub = await reg.pushManager.getSubscription();
            if (!sub) {
                sub = await reg.pushManager.subscribe({
                    userVisibleOnly:      true,
                    applicationServerKey: urlBase64ToUint8Array('{{ config("webpush.vapid.public_key") }}'),
                });
            }

            @this.simpanPushSubscription(JSON.stringify(sub));
        } catch (err) {
            console.warn('[sw] Push subscription gagal:', err);
        }
    }

    // Alpine component untuk banner izin push
    function pushPermisiBanner(token) {
        return {
            tampil: false,

            periksa() {
                if (!('Notification' in window)) return;
                if (Notification.permission !== 'default') return;
                if (localStorage.getItem('push_banner_skip_' + token)) return;
                setTimeout(() => { this.tampil = true; }, 900);
            },

            async aktifkan() {
                this.tampil = false;
                await daftarPushSubscription();
            },

            lewati() {
                localStorage.setItem('push_banner_skip_' + token, '1');
                this.tampil = false;
            },
        };
    }

    function urlBase64ToUint8Array(base64) {
        const pad = '='.repeat((4 - base64.length % 4) % 4);
        const b64 = (base64 + pad).replace(/-/g, '+').replace(/_/g, '/');
        const raw = atob(b64);
        return Uint8Array.from([...raw].map((c) => c.charCodeAt(0)));
    }

    // ────────────────────────────────────────────────────────────────────────

    function konsultasiTimer(berakhirAt) {
        return {
            label: '--:--',
            habis: false,
            target: berakhirAt ? new Date(berakhirAt).getTime() : null,
            timer: null,
            start() {
                if (! this.target) return;
                this.tick();
                if (! this.habis) {
                    this.timer = setInterval(() => this.tick(), 1000);
                }
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
</div>
