<div class="rounded-2xl overflow-hidden shadow-sm group cursor-pointer">
    <div class="h-64 w-full overflow-hidden relative">
        <img src="{{ Storage::url($kamarInap->thumbnail) }}" class="h-full w-full object-cover group-hover:scale-110 transition-all ease-in-out" alt="">
    </div>
    <div class="p-3 col-span-2">
        <h4 class="font-extrabold text-xl text-primary">{{ $kamarInap->nama }}</h4>
        <div class="mt-5 flex justify-between items-center">
            @if($kamarInap->kelas == "VIP" || $kamarInap->kelas == "VVIP" || $kamarInap->kelas == "VIP Plus")
                <p class="bg-linear-to-r from-yellow-500 to-amber-500 p-3 rounded-xl text-xs text-white font-semibold shadow">{{ $kamarInap->kelas }}</p>
            @else
                <p class="text-sm text-gray-600">Untuk <span class="font-bold text-secondary">{{ $kamarInap->kapasitas }} Pasien</span></p>
            @endif
            <p class="px-2 py-1 text-white bg-secondary text-sm shadow-lg">Rp. {{ number_format($kamarInap->harga, 0, ',', '.') }}<span class="text-xs">/malam</span></p>
        </div>
        <hr class="my-5 border-gray-200">
        <div class="mt-5 grid grid-cols-2 gap-2">
            @foreach($kamarInap->fasilitasRawatInap as $f)
            <div class="flex items-center text-primary text-sm">
                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" class="size-5" width="24px" fill="currentColor"><path d="m424-408-86-86q-11-11-28-11t-28 11q-11 11-11 28t11 28l114 114q12 12 28 12t28-12l226-226q11-11 11-28t-11-28q-11-11-28-11t-28 11L424-408Zm56 328q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Zm0-80q134 0 227-93t93-227q0-134-93-227t-227-93q-134 0-227 93t-93 227q0 134 93 227t227 93Zm0-320Z"/></svg>
                <span class="ml-2 text-on-surface">{{ $f->nama }}</span>
            </div>
            @endforeach
        </div>
    </div>
</div>