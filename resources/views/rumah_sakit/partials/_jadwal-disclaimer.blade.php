{{-- Disclaimer jadwal: hanya tampilkan kontak berkategori PENDAFTARAN --}}
@php $kontakJadwal = isset($kontakRumahSakit) ? $kontakRumahSakit->where('kategori', 'PENDAFTARAN') : collect(); @endphp
@if($kontakJadwal->isNotEmpty())
<div class="mt-8 mx-auto max-w-2xl">
    <div class="flex items-start gap-3 bg-amber-50 border border-amber-200 rounded-2xl px-5 py-4">
        <span class="material-symbols-outlined text-amber-500 text-[20px] shrink-0 mt-0.5"
              style="font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24;">
            info
        </span>
        <div class="text-sm leading-relaxed">
            <p class="font-semibold text-amber-800">
                Jadwal dapat berubah sewaktu-waktu.
            </p>
            <p class="text-amber-700 mt-1">
                Info lebih lanjut, silakan hubungi:
            </p>
            <div class="flex flex-wrap gap-x-4 gap-y-1 mt-2">
                @foreach($kontakJadwal as $kontak)
                    @if($kontak->link)
                        <a href="{{ $kontak->link }}"
                           target="{{ str_starts_with($kontak->link, 'http') ? '_blank' : '_self' }}"
                           rel="{{ str_starts_with($kontak->link, 'http') ? 'noopener noreferrer' : '' }}"
                           class="inline-flex items-center gap-1.5 font-semibold text-amber-800
                                  hover:text-amber-600 hover:underline transition-colors">
                            @if(str_contains($kontak->link, 'wa.me') || str_contains($kontak->link, 'whatsapp'))
                                <span class="material-symbols-outlined text-[14px]">chat</span>
                            @elseif(str_starts_with($kontak->link, 'tel:'))
                                <span class="material-symbols-outlined text-[14px]">call</span>
                            @else
                                <span class="material-symbols-outlined text-[14px]">open_in_new</span>
                            @endif
                            {{ $kontak->label }}{{ $kontak->value ? ': ' . $kontak->value : '' }}
                        </a>
                    @else
                        <span class="inline-flex items-center gap-1.5 text-amber-800">
                            <span class="material-symbols-outlined text-[14px]">phone</span>
                            {{ $kontak->label }}{{ $kontak->value ? ': ' . $kontak->value : '' }}
                        </span>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
</div>
@endif
