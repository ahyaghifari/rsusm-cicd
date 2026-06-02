<div>
    <x-page-hero title="Hubungi Kami" />

    <div class="w-10/12 mx-auto pb-20 mt-10">

        {{-- Operasional --}}
        @if($operasional->count() > 0)
        <div class="mb-16">
            <div class="flex items-center gap-3 mb-8">
                <div class="w-1 h-7 bg-primary rounded-full"></div>
                <h2 class="text-on-surface font-bold text-2xl">Operasional</h2>
                <span class="ml-1 bg-primary/10 text-primary text-xs font-semibold px-2.5 py-1 rounded-full">
                    {{ $operasional->count() }} kontak
                </span>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 gap-5">
                @foreach($operasional as $item)
                @php
                    $icon = 'call';
                    $iconBg = 'bg-primary/10';
                    $iconColor = 'text-primary';
                    $val = strtolower($item->value);
                    $lbl = strtolower($item->label);
                    if (str_contains($val, '@')) {
                        $icon = 'mail';
                    } elseif (str_contains($lbl, 'alamat') || str_contains($item->link ?? '', 'maps')) {
                        $icon = 'location_on';
                        $iconBg = 'bg-tertiary/10';
                        $iconColor = 'text-tertiary';
                    } elseif (str_contains($item->link ?? '', 'whatsapp')) {
                        $icon = 'chat';
                        $iconBg = 'bg-secondary/10';
                        $iconColor = 'text-secondary';
                    } elseif (str_contains($lbl, 'online') || str_contains($lbl, 'website') || str_contains($lbl, 'pendaftaran online')) {
                        $icon = 'public';
                        $iconBg = 'bg-secondary/10';
                        $iconColor = 'text-secondary';
                    }
                @endphp

                <div
                    data-aos="fade-up"
                    class="group relative bg-white border border-outline-variant/30 rounded-2xl overflow-hidden shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300"
                >
                    {{-- Accent bar top --}}
                    <div class="h-1 w-full bg-linear-to-r from-primary to-primary/40"></div>

                    <div class="p-6 flex flex-col gap-4">
                        {{-- Icon / Gambar --}}
                        @if($item->gambar)
                            <img
                                src="{{ Storage::url($item->gambar) }}"
                                alt="{{ $item->label }}"
                                class="w-16 h-16 object-cover rounded-xl flex-shrink-0"
                            >
                        @else
                            <div class="w-16 h-16 {{ $iconBg }} rounded-xl flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform duration-300">
                                <span class="material-symbols-outlined {{ $iconColor }} text-3xl">{{ $icon }}</span>
                            </div>
                        @endif

                        {{-- Label --}}
                        <span class="text-sm font-semibold text-on-surface-variant uppercase tracking-wide leading-tight">
                            {{ $item->label }}
                        </span>

                        {{-- Value --}}
                        @if($item->link)
                            <a
                                href="{{ $item->link }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="text-primary font-bold text-base hover:underline decoration-primary/50 underline-offset-2 break-words leading-snug flex items-center gap-1"
                            >
                                {{ $item->value }}
                                <span class="material-symbols-outlined text-base opacity-60">open_in_new</span>
                            </a>
                        @else
                            <p class="text-on-surface font-bold text-base break-words leading-snug">
                                {{ $item->value }}
                            </p>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

       

        {{-- Kosong --}}
        @if($operasional->count() === 0 && $sosial_media->count() === 0)
        <div class="text-center py-24">
            <div class="w-16 h-16 bg-surface-container rounded-2xl flex items-center justify-center mx-auto mb-4">
                <span class="material-symbols-outlined text-on-surface-variant text-3xl">phone_disabled</span>
            </div>
            <p class="text-on-surface-variant font-medium">Belum ada informasi kontak yang tersedia.</p>
        </div>
        @endif

    </div>
</div>
