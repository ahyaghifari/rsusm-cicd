<div>

<x-page-hero
    title="Syifa Magazine"
    subtitle="Baca edisi terbaru majalah kesehatan kami"
    icon=""
/>

<section class="w-10/12 mx-auto px-4 py-12">

    @if($magazines->isEmpty())
        <div class="flex flex-col items-center justify-center py-24 text-center">
            <span class="material-symbols-outlined text-6xl text-outline-variant mb-4">menu_book</span>
            <p class="text-lg font-semibold text-on-surface-variant">Belum ada edisi magazine</p>
            <p class="text-sm text-on-surface-variant/70 mt-1">Edisi terbaru akan segera hadir</p>
        </div>
    @else
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-6">
            @foreach($magazines as $magazine)
            <div class="magazine-card group cursor-pointer"
                 data-pdf-url="{{ Storage::url($magazine->file_pdf) }}"
                 data-judul="{{ $magazine->judul }}">

                <div class="relative aspect-[3/4] rounded-xl overflow-hidden shadow-md
                            group-hover:shadow-xl group-hover:-translate-y-1
                            transition-all duration-300 bg-gray-100">
                    @if($magazine->cover)
                        <img src="{{ Storage::url($magazine->cover) }}"
                             alt="{{ $magazine->judul }}"
                             class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                             loading="lazy">
                    @else
                        <div class="w-full h-full flex flex-col items-center justify-center bg-gradient-to-br from-primary/10 to-primary/30">
                            <span class="material-symbols-outlined text-5xl text-primary/50">menu_book</span>
                        </div>
                    @endif

                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100
                                transition-opacity duration-300 flex items-center justify-center">
                        <span class="inline-flex items-center gap-1.5 bg-white text-primary
                                     text-sm font-bold px-4 py-2 rounded-full shadow-lg">
                            <span class="material-symbols-outlined text-[16px]">auto_stories</span>
                            Baca
                        </span>
                    </div>
                </div>

                <div class="mt-3 px-1">
                    <p class="font-semibold text-sm text-on-surface leading-snug line-clamp-2">
                        {{ $magazine->judul }}
                    </p>
                    @if($magazine->published_at)
                        <p class="text-xs text-on-surface-variant mt-1">
                            {{ $magazine->published_at->translatedFormat('F Y') }}
                        </p>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    @endif

</section>

{{-- Modal Flipbook — wire:ignore agar Livewire tidak menyentuh DOM ini --}}
<div id="magazine-modal" wire:ignore
     class="hidden fixed inset-0 z-[200] bg-black/95 flex flex-col">

    {{-- Header --}}
    <div class="shrink-0 flex items-center justify-between px-4 py-3 border-b border-white/10">
        <span id="modal-judul" class="text-white font-bold text-sm md:text-base truncate max-w-[60vw]"></span>
        <button id="modal-close"
                class="flex items-center gap-1.5 text-white/70 hover:text-white
                       bg-white/10 hover:bg-white/20 px-3 py-1.5 rounded-lg text-sm transition-colors">
            <span class="material-symbols-outlined text-[16px]">close</span>
            Tutup
        </button>
    </div>

    {{-- Loading --}}
    <div id="flipbook-loading" class="flex flex-col items-center justify-center flex-1 gap-4">
        <div class="w-12 h-12 border-4 border-white/20 border-t-white rounded-full animate-spin"></div>
        <p class="text-white/70 text-sm">Memuat magazine...</p>
        <div class="w-48 bg-white/10 rounded-full h-1.5">
            <div id="loading-bar" class="bg-yellow-400 h-1.5 rounded-full transition-all duration-200" style="width:0%"></div>
        </div>
        <p id="loading-progress" class="text-white/50 text-xs">0%</p>
    </div>

    {{-- Flipbook container — flex-1 agar mengisi sisa tinggi layar --}}
    <div id="flipbook-container"
         class="hidden flex-1 items-center justify-center overflow-auto py-4">
        {{-- #flipbook di-replace setiap close untuk reset state StPageFlip --}}
        <div id="flipbook"></div>
    </div>

    {{-- Kontrol navigasi --}}
    <div id="flipbook-controls"
         class="hidden shrink-0 items-center justify-center gap-4 py-3 border-t border-white/10">
        <button id="btn-prev"
                class="flex items-center gap-1 text-white/80 hover:text-white bg-white/10 hover:bg-white/20
                       px-4 py-2 rounded-lg text-sm transition-colors">
            <span class="material-symbols-outlined text-[16px]">chevron_left</span>
            <span class="hidden sm:inline">Sebelumnya</span>
        </button>
        <span id="page-counter" class="text-white/60 text-sm min-w-20 text-center">Hal 1 / 1</span>
        <button id="btn-next"
                class="flex items-center gap-1 text-white/80 hover:text-white bg-white/10 hover:bg-white/20
                       px-4 py-2 rounded-lg text-sm transition-colors">
            <span class="hidden sm:inline">Berikutnya</span>
            <span class="material-symbols-outlined text-[16px]">chevron_right</span>
        </button>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/page-flip@2.0.7/dist/js/page-flip.browser.min.js"></script>

<script>
(function () {
    const WORKER_URL = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
    let pageFlipInstance = null;
    let totalPages = 0;

    // Helper: selalu ambil elemen segar dari DOM
    const el = (id) => document.getElementById(id);

    function show(id, display = 'flex') {
        const e = el(id);
        e.classList.remove('hidden');
        e.classList.add(display);
    }
    function hide(id) {
        const e = el(id);
        e.classList.add('hidden');
        e.classList.remove('flex', 'block');
    }

    // Buat ulang #flipbook div agar StPageFlip mendapat elemen bersih
    function resetFlipbookEl() {
        const container = el('flipbook-container');
        const old = el('flipbook');
        if (old) old.remove();
        const fresh = document.createElement('div');
        fresh.id = 'flipbook';
        container.appendChild(fresh);
        return fresh;
    }

    // Skala CSS agar flipbook (ukuran fixed px) muat di dalam container
    let _lastFitArgs = null;
    function fitFlipbook(pageW, pageH, isMobile) {
        _lastFitArgs = { pageW, pageH, isMobile };

        const flipEl      = el('flipbook');
        const containerEl = el('flipbook-container');
        if (!flipEl || !containerEl) return;

        // Ukur lebar aktual yang dirender StPageFlip (.stf__parent)
        const stfParent  = flipEl.querySelector('.stf__parent');
        const renderedW  = stfParent ? stfParent.offsetWidth  : (isMobile ? pageW : pageW * 2);
        const renderedH  = stfParent ? stfParent.offsetHeight : pageH;

        const availW = containerEl.clientWidth  - 16;
        const availH = containerEl.clientHeight - 16;

        const fitScale = Math.min(availW / renderedW, availH / renderedH, 1);

        if (fitScale < 0.99) {
            const heightDiff = renderedH - (renderedH * fitScale);
            flipEl.style.transform       = `scale(${fitScale.toFixed(4)})`;
            flipEl.style.transformOrigin = 'top center';
            flipEl.style.marginBottom    = `-${Math.ceil(heightDiff)}px`;
        } else {
            flipEl.style.transform    = '';
            flipEl.style.marginBottom = '';
        }
    }

    // Re-fit saat jendela diubah ukuran (rotasi layar, resize browser)
    window.addEventListener('resize', () => {
        if (_lastFitArgs && pageFlipInstance) {
            fitFlipbook(_lastFitArgs.pageW, _lastFitArgs.pageH, _lastFitArgs.isMobile);
        }
    });

    // Klik card → buka modal
    document.querySelectorAll('.magazine-card').forEach(card => {
        card.addEventListener('click', () => {
            openMagazine(card.dataset.pdfUrl, card.dataset.judul);
        });
    });

    async function openMagazine(pdfUrl, judul) {
        // Reset state & tampilkan modal
        show('magazine-modal');
        el('modal-judul').textContent = judul;
        show('flipbook-loading');
        hide('flipbook-container');
        hide('flipbook-controls');
        el('loading-bar').style.width = '0%';
        el('loading-progress').textContent = '0%';

        // Pastikan flipbook div bersih sebelum mulai
        resetFlipbookEl();

        const pdfjsLib = window['pdfjs-dist/build/pdf'];
        pdfjsLib.GlobalWorkerOptions.workerSrc = WORKER_URL;

        try {
            const pdf = await pdfjsLib.getDocument(pdfUrl).promise;
            totalPages = pdf.numPages;

            // Dimensi dari halaman pertama
            const firstPage     = await pdf.getPage(1);
            const baseViewport  = firstPage.getViewport({ scale: 1 });

            // Hitung scale responsif
            const isMobile = window.innerWidth < 640;
            const headerH  = 56 + 56; // header + controls (px)
            const availW   = window.innerWidth;
            const availH   = window.innerHeight - headerH;

            let scale;
            if (isMobile) {
                // Mobile: single page, pakai hampir seluruh lebar
                const maxW = availW * 0.94;
                const maxH = availH * 0.90;
                scale = Math.min(maxW / baseViewport.width, maxH / baseViewport.height);
            } else {
                // Desktop: double page berdampingan
                const maxW = Math.min(availW * 0.44, 520);
                const maxH = availH * 0.88;
                scale = Math.min(maxW / baseViewport.width, maxH / baseViewport.height, 1.5);
            }

            const pageW = Math.floor(baseViewport.width  * scale);
            const pageH = Math.floor(baseViewport.height * scale);

            // Render semua halaman
            const images = [];
            for (let i = 1; i <= totalPages; i++) {
                const page     = await pdf.getPage(i);
                const viewport = page.getViewport({ scale });
                const canvas   = document.createElement('canvas');
                canvas.width   = pageW;
                canvas.height  = pageH;
                await page.render({ canvasContext: canvas.getContext('2d'), viewport }).promise;
                images.push(canvas.toDataURL('image/jpeg', 0.85));

                const pct = Math.round((i / totalPages) * 100);
                el('loading-bar').style.width  = pct + '%';
                el('loading-progress').textContent = pct + '%';
            }

            // Tampilkan flipbook
            hide('flipbook-loading');
            show('flipbook-container');
            show('flipbook-controls');

            // Ambil elemen segar (resetFlipbookEl sudah dipanggil di atas)
            const flipbookEl = el('flipbook');

            pageFlipInstance = new St.PageFlip(flipbookEl, {
                width: pageW,
                height: pageH,
                showCover: true,
                mobileScrollSupport: false,
                usePortrait: isMobile,
            });
            pageFlipInstance.loadFromImages(images);

            el('page-counter').textContent = `Hal 1 / ${totalPages}`;
            pageFlipInstance.on('flip', (e) => {
                el('page-counter').textContent = `Hal ${e.data + 1} / ${totalPages}`;
            });

            // Fit flipbook ke container setelah StPageFlip selesai render
            requestAnimationFrame(() => fitFlipbook(pageW, pageH, isMobile));

        } catch (err) {
            el('flipbook-loading').innerHTML =
                '<p class="text-red-400 text-sm px-4 text-center">Gagal memuat PDF. Pastikan file valid dan coba lagi.</p>';
            console.error(err);
        }
    }

    function closeMagazine() {
        hide('magazine-modal');
        if (pageFlipInstance) {
            try { pageFlipInstance.destroy(); } catch (_) {}
            pageFlipInstance = null;
        }
        _lastFitArgs = null;
        // Replace flipbook div → clean state untuk buka berikutnya
        resetFlipbookEl();
    }

    // Tutup via tombol atau klik backdrop
    el('modal-close').addEventListener('click', closeMagazine);
    el('magazine-modal').addEventListener('click', function (e) {
        if (e.target === this) closeMagazine();
    });

    // Navigasi halaman
    el('btn-prev').addEventListener('click', () => pageFlipInstance?.flipPrev());
    el('btn-next').addEventListener('click', () => pageFlipInstance?.flipNext());

    // Keyboard
    document.addEventListener('keydown', (e) => {
        if (!el('magazine-modal').classList.contains('flex')) return;
        if (e.key === 'Escape')      closeMagazine();
        if (e.key === 'ArrowLeft')   pageFlipInstance?.flipPrev();
        if (e.key === 'ArrowRight')  pageFlipInstance?.flipNext();
    });
})();
</script>

</div>
