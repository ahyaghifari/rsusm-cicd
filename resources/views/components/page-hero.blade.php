@props([
    'title',
    'subtitle' => null,
    'icon' => null,
])

<div id="hero" class="relative overflow-hidden bg-primary py-12 md:py-14 lg:py-16 px-6">
    {{-- Dot grid --}}
    <div class="absolute inset-0 pointer-events-none opacity-[0.04]"
         style="background-image: radial-gradient(circle, white 1.5px, transparent 1.5px); background-size: 28px 28px;"></div>
    {{-- Corner blobs --}}
    <div class="absolute -top-12 -right-12 w-60 h-60 bg-white/8 rounded-full pointer-events-none"></div>
    <div class="absolute -bottom-16 -left-16 w-72 h-72 bg-white/5 rounded-full pointer-events-none"></div>
    {{-- Accent bars --}}
    <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1.5 h-24 bg-yellow-400/50 rounded-r-full pointer-events-none"></div>
    <div class="absolute left-3 top-1/2 -translate-y-1/2 w-1 h-14 bg-yellow-400/25 rounded-r-full pointer-events-none"></div>
    {{-- Diamond shapes --}}
    <div class="absolute top-8 left-1/4 w-10 h-10 border-2 border-white/10 rotate-45 pointer-events-none"></div>
    <div class="absolute bottom-8 right-1/4 w-6 h-6 border border-white/10 rotate-45 pointer-events-none"></div>

    <div class="relative z-10 text-center max-w-2xl mx-auto">
        @if($icon)
            <span class="material-symbols-outlined text-5xl text-white/60 block mb-3">{{ $icon }}</span>
        @endif
        <h1 class="text-3xl md:text-4xl lg:text-5xl font-bold text-white leading-tight {{ $subtitle ? 'mb-4' : '' }}">{{ $title }}</h1>
        @if($subtitle)
            <p class="text-white/65 leading-relaxed text-sm lg:text-base">{{ $subtitle }}</p>
        @endif
    </div>
</div>
