<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>[TEST] Preview 360 — Photo Sphere Viewer</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-surface text-on-surface font-sans antialiased min-h-screen flex flex-col items-center justify-center p-8">

    <div class="max-w-md text-center">
        <p class="inline-flex items-center gap-1 bg-yellow-100 text-yellow-800 text-xs font-bold uppercase tracking-widest px-3 py-1.5 rounded-full mb-4">
            Halaman Tes — Bukan UI Final
        </p>
        <h1 class="text-2xl font-bold mb-2">Preview 360 Kamar Rawat Inap</h1>
        <p class="text-on-surface-variant text-sm mb-6">
            Tes integrasi Photo Sphere Viewer, pakai foto contoh
            <code class="bg-gray-100 px-1.5 py-0.5 rounded text-xs">public/img/360.jpg</code>.
            Modal di bawah ini sengaja dibuat polos (vanilla JS, tanpa Preline) supaya
            yang ditest cuma Photo Sphere Viewer-nya saja.
        </p>

        <button id="btn-open-360-test" type="button"
            class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-primary text-white font-bold text-sm
                   hover:bg-primary/90 transition-colors shadow-lg shadow-primary/30">
            <span class="material-symbols-outlined text-[18px]">360</span>
            Buka Preview 360
        </button>
    </div>

    {{-- Modal sederhana, tanpa Preline --}}
    <div id="modal-360-test" class="hidden fixed inset-0 z-50 bg-black/70 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden w-full max-w-3xl">
            <div class="flex justify-between items-center py-3 px-4 border-b border-outline-variant/20">
                <h3 class="font-bold text-on-surface">Preview 360 — Tes</h3>
                <button id="btn-close-360-test" type="button"
                    class="flex justify-center items-center size-8 rounded-full text-on-surface-variant hover:bg-gray-100">
                    <span class="sr-only">Tutup</span>
                    <span class="material-symbols-outlined text-[18px]">close</span>
                </button>
            </div>
            <div id="viewer-360-test" style="width:100%; height:70vh; background:#111;"></div>
        </div>
    </div>

    <script>
        const modal = document.getElementById('modal-360-test');
        const btnOpen = document.getElementById('btn-open-360-test');
        const btnClose = document.getElementById('btn-close-360-test');

        let psvInitialized = false;

        function openModal() {
            modal.classList.remove('hidden');

            if (!psvInitialized) {
                setTimeout(() => {
                    new window.PSVViewer({
                        container: document.getElementById('viewer-360-test'),
                        panorama: '{{ asset('img/361.jpg') }}',
                    });
                    psvInitialized = true;
                }, 50);
            }
        }

        function closeModal() {
            modal.classList.add('hidden');
        }

        btnOpen.addEventListener('click', openModal);
        btnClose.addEventListener('click', closeModal);
        modal.addEventListener('click', (e) => {
            if (e.target === modal) closeModal();
        });
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !modal.classList.contains('hidden')) closeModal();
        });
    </script>
</body>

</html>
