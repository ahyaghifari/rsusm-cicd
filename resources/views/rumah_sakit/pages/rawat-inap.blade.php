<div>
    <x-page-hero title="Rawat Inap" subtitle="Pilih kelas kamar sesuai kebutuhan dan kenyamanan Anda" />

    {{-- Rail kelas + tautan cek ketersediaan --}}
    <div class="mt-7 w-10/12 mx-auto flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">

        @if($kelasOptions->isNotEmpty())
        <div>
            <span class="text-xs font-bold text-on-surface-variant/70 uppercase tracking-wide mb-2 block">Pilih Kelas Kamar</span>
            <div class="flex flex-wrap items-center gap-2">
                <button wire:click="$set('kelasFilter', null)" type="button"
                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full text-xs font-semibold border transition-all duration-200 hover:scale-105 active:scale-95
                           {{ ! $kelasFilter
                               ? 'bg-primary text-white border-primary shadow-md shadow-primary/25'
                               : 'bg-white text-on-surface-variant border-outline-variant hover:border-primary/40 hover:text-primary' }}">
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
                                <div>
                                    <x-rawat-inap :rawat-inap="$room" />
                                </div>
                        @endforeach

                    </div>
                </section>

            @endforeach

        @else
                @foreach($rawatInap as $room)

                    <div>
                        <x-rawat-inap :rawat-inap="$room" />
                    </div>

                @endforeach
        @endif
    </div>

    {{-- Modal preview 360° — dirender di sini (level halaman), BUKAN di dalam kartu kamar
         (components/rawat-inap.blade.php). Kartu kamar punya `hover:-translate-y-1` (CSS
         transform), dan ancestor dengan transform jadi containing block untuk descendant
         `position: fixed` — kalau modal ditaruh di dalam kartu, dia "fixed" relatif ke kartu
         yang sedang ter-hover, bukan ke viewport, sehingga kelihatan kejepit di dalam kartu.
         Render satu kali di sini lewat gabungan semua kamar (gedung maupun non-gedung)
         supaya tetap aman dari masalah itu.

         Tampilannya sengaja gelap & full-bleed (bukan kartu putih dengan header terpisah) —
         konsisten dengan GLightbox (viewer foto datar) yang sudah dipakai di kartu yang sama,
         supaya "cara melihat kamar" di situs ini terasa satu bahasa visual, bukan dua produk
         berbeda. Header mengambang tipis (frosted) di atas viewer, bukan bar solid, supaya
         foto 360 langsung jadi fokus penuh. Chip "360°" memakai bg-primary yang sama dengan
         badge trigger di kartu, supaya ada jejak visual yang jelas dari mana modal ini dibuka. --}}
    @php
        $semuaRoomUntukModal360 = $hasGedung
            ? $gedungs->flatMap(fn ($gedung) => $gedung->rawatInap)
            : $rawatInap;
    @endphp
    @foreach($semuaRoomUntukModal360 as $room)
        @if($room->foto_360)
            <div id="modal-360-{{ $room->id }}"
                 role="dialog" aria-modal="true" aria-labelledby="modal-360-title-{{ $room->id }}"
                 tabindex="-1"
                 class="invisible opacity-0 fixed inset-0 z-200 bg-black/90
                        flex items-center justify-center
                        transition-opacity duration-200 motion-reduce:transition-none"
                 onclick="if (event.target === this) closeModal360({{ $room->id }})">

                <div class="relative w-full h-full sm:h-auto sm:max-w-5xl sm:max-h-[85vh]"
                     onclick="event.stopPropagation()">

                    {{-- Header mengambang — pointer-events dilepas di lapisan luar supaya
                         area kosong (gradient) tetap bisa di-drag untuk memutar panorama;
                         cuma chip+judul dan tombol tutup yang interaktif. --}}
                    <div class="absolute top-0 inset-x-0 z-10 flex items-center justify-between gap-3
                                px-4 sm:px-5 py-3 bg-linear-to-b from-black/70 to-transparent
                                pointer-events-none">
                        <div class="flex items-center gap-2 min-w-0 pointer-events-auto">
                            <span class="inline-flex items-center gap-1 bg-primary text-white text-[11px]
                                         font-bold px-2 py-0.5 rounded-full shrink-0">
                                <span class="material-symbols-outlined text-[12px]">360</span>
                                360°
                            </span>
                            <h3 id="modal-360-title-{{ $room->id }}"
                                class="font-bold text-white text-sm sm:text-base truncate">
                                {{ $room->nama }}
                            </h3>
                        </div>

                        <button type="button" data-modal-360-close onclick="closeModal360({{ $room->id }})"
                                aria-label="Tutup preview 360° kamar {{ $room->nama }}"
                                class="shrink-0 pointer-events-auto flex justify-center items-center size-9 rounded-full
                                       bg-white/10 text-white hover:bg-white/20
                                       focus:outline-none focus:ring-2 focus:ring-white/80
                                       transition-colors">
                            <span class="material-symbols-outlined text-[20px]">close</span>
                        </button>
                    </div>

                    <div data-psv-container data-panorama="{{ Storage::url($room->foto_360) }}"
                         id="viewer-360-{{ $room->id }}"
                         class="w-full h-full sm:h-[70vh] sm:rounded-2xl overflow-hidden"
                         style="background:#111;"></div>
                </div>
            </div>
        @endif
    @endforeach

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

        // Modal preview panorama kamar — vanilla JS lewat atribut onclick di markup
        // (bukan Preline hs-overlay + addEventListener). Sengaja begini karena script ini
        // ikut di-render ulang Livewire tiap filter kelas diubah, dan kalau init-nya pakai
        // document.addEventListener, listener bisa menumpuk tiap kali script-nya ikut
        // di-reinsert oleh morph DOM Livewire — efeknya beberapa instance PSVViewer dibuat
        // bersamaan ke container yang sama saat modal dibuka → kedip-kedip cepat (beberapa
        // canvas WebGL rebutan render). Function declaration di bawah ini aman dari masalah
        // itu: redefinisi `function openModal360()` berkali-kali cuma menimpa definisi lama
        // (idempotent), tidak menumpuk seperti addEventListener.
        // Elemen yang fokusnya dikembalikan saat modal ditutup — disimpan per pembukaan,
        // bukan per modal, karena cuma satu modal 360 yang realistis terbuka sekaligus.
        let lastFocused360Trigger = null;

        function openModal360(id) {
            const modal = document.getElementById('modal-360-' + id);
            if (!modal) return;

            lastFocused360Trigger = document.activeElement;

            modal.classList.remove('invisible', 'opacity-0');
            document.body.style.overflow = 'hidden';

            // Fokus dipindah ke tombol tutup begitu modal terbuka (dialog WAI-ARIA: fokus
            // wajib masuk ke dalam dialog saat dibuka, dikembalikan ke trigger saat ditutup).
            const closeBtn = modal.querySelector('[data-modal-360-close]');
            if (closeBtn) closeBtn.focus();

            const viewerEl = modal.querySelector('[data-psv-container]');
            if (viewerEl && !viewerEl.dataset.psvInitialized) {
                viewerEl.dataset.psvInitialized = '1';

                // Beri jeda kecil supaya transisi opacity sempat mulai & browser sempat
                // layout ulang sebelum container diukur oleh viewer.
                setTimeout(() => {
                    new window.PSVViewer({
                        container: viewerEl,
                        panorama: viewerEl.dataset.panorama,
                    });
                }, 50);
            }
        }

        function closeModal360(id) {
            const modal = document.getElementById('modal-360-' + id);
            if (!modal) return;

            modal.classList.add('invisible', 'opacity-0');
            document.body.style.overflow = '';

            if (lastFocused360Trigger) {
                lastFocused360Trigger.focus();
                lastFocused360Trigger = null;
            }
        }

        document.addEventListener('keydown', function (e) {
            const openModal = document.querySelector('[id^="modal-360-"]:not(.invisible)');
            if (!openModal) return;

            if (e.key === 'Escape') {
                closeModal360(openModal.id.replace('modal-360-', ''));
                return;
            }

            // Trap fokus sederhana — modal ini cuma punya 1-2 elemen interaktif (tombol
            // tutup, judul tidak fokusable), cukup cegah Tab keluar dari dialog ke halaman
            // di belakangnya selagi modal terbuka.
            if (e.key === 'Tab') {
                const focusable = openModal.querySelectorAll('button, [href], [tabindex]:not([tabindex="-1"])');
                if (focusable.length === 0) return;

                const first = focusable[0];
                const last = focusable[focusable.length - 1];

                if (e.shiftKey && document.activeElement === first) {
                    e.preventDefault();
                    last.focus();
                } else if (!e.shiftKey && document.activeElement === last) {
                    e.preventDefault();
                    first.focus();
                }
            }
        });
    </script>
</div>
