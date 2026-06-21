@if($currentRumahSakit->jadwal_poliklinik_aktif && $currentRumahSakit->jadwal_poliklinik_gambar)

<script>
function jadwalPoliklinikPopup() {
    return {
        visible: false,
        init() {
            setTimeout(() => { this.visible = true; }, 1800);
        },
        close() {
            this.visible = false;
        }
    };
}
</script>

<div
    x-data="jadwalPoliklinikPopup()"
    x-show="visible"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-210 flex items-center justify-center sm:p-4"
    style="display:none;">

    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="close()"></div>

    {{-- Modal: fullscreen di mobile, besar & terpusat di desktop --}}
    <div class="relative z-10 w-full h-full sm:h-auto sm:max-h-[90vh] sm:max-w-2xl
                bg-white sm:rounded-3xl overflow-hidden shadow-2xl flex flex-col">

        {{-- Tombol tutup --}}
        <button @click="close()"
            class="absolute top-3 right-3 z-20 w-11 h-11 bg-black/25 hover:bg-black/50
                   text-white rounded-full flex items-center justify-center transition-colors duration-150">
            <span class="material-symbols-outlined text-[20px]">close</span>
        </button>

        {{-- Badge judul --}}
        <div class="absolute top-3 left-3 z-20">
            <span class="inline-flex items-center gap-1 text-[10px] font-bold uppercase tracking-widest
                         bg-yellow-400 text-primary px-3 py-1 rounded-full">
                <span class="material-symbols-outlined text-[11px]">calendar_month</span>
                Jadwal Poliklinik
            </span>
        </div>

        {{-- Poster: tampil ukuran penuh, scroll kalau lebih panjang dari viewport --}}
        <div class="flex-1 min-h-0 overflow-y-auto bg-gray-50">
            <img src="{{ Storage::url($currentRumahSakit->jadwal_poliklinik_gambar) }}"
                 alt="Jadwal Poliklinik {{ $currentRumahSakit->nama }}"
                 class="block w-full h-auto"
                 loading="lazy">
        </div>

        {{-- CTA --}}
        <div class="p-4 border-t border-outline-variant/20 shrink-0">
            <a wire:navigate href="{{ rumahsakit_route('rumahsakit.jadwal_praktek') }}"
               @click="close()"
               class="flex items-center justify-center gap-2 w-full px-4 py-2.5 rounded-xl
                      bg-primary text-white text-sm font-semibold hover:opacity-90 transition-colors">
                <span class="material-symbols-outlined text-[18px]">calendar_month</span>
                Lihat Jadwal Praktek
            </a>
        </div>
    </div>
</div>

@endif
