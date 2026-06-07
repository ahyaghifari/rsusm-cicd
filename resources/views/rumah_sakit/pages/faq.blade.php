<div>

<x-page-hero
    title="FAQ"
    subtitle="Pertanyaan yang sering ditanyakan"
/>

<section class="bg-gradient-to-b from-surface-container/40 to-white">
<div class="w-11/12 lg:w-10/12 mx-auto py-12 lg:py-16">

    @if($faqs->isEmpty())
        <div class="flex flex-col items-center justify-center py-24 text-center">
            <div class="w-20 h-20 bg-primary/10 rounded-2xl flex items-center justify-center mx-auto mb-5">
                <span class="material-symbols-outlined text-4xl text-primary">help</span>
            </div>
            <p class="text-lg font-semibold text-on-surface">Belum ada FAQ tersedia</p>
            <p class="text-sm text-on-surface-variant mt-1">Silakan hubungi kami jika ada pertanyaan</p>
            <a wire:navigate href="{{ rumahsakit_route('rumahsakit.hubungi_kami') }}"
               class="mt-6 inline-flex items-center gap-2 bg-primary text-white text-sm font-semibold px-5 py-2.5 rounded-xl hover:bg-primary/90 transition-colors">
                <span class="material-symbols-outlined text-[18px]">call</span>
                Hubungi Kami
            </a>
        </div>
    @else
        <div class="flex flex-col lg:flex-row gap-10 lg:gap-14 items-start">

            {{-- Sidebar --}}
            <div class="w-full lg:w-72 shrink-0 lg:sticky lg:top-24 flex flex-col gap-4">
                {{-- Info card --}}
                <div class="bg-white border border-outline-variant/30 rounded-2xl overflow-hidden shadow-sm">
                    <div class="h-1.5 bg-linear-to-r from-primary to-secondary"></div>
                    <div class="p-6">
                        <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center mb-4">
                            <span class="material-symbols-outlined text-primary text-2xl">quiz</span>
                        </div>
                        <h2 class="text-on-surface font-bold text-base leading-snug mb-1.5">Pertanyaan Umum</h2>
                        <p class="text-on-surface-variant text-sm leading-relaxed">
                            Temukan jawaban atas pertanyaan yang paling sering ditanyakan oleh pasien kami.
                        </p>

                        <div class="mt-5 pt-5 border-t border-outline-variant/20 flex items-center gap-3">
                            <div class="w-9 h-9 bg-secondary/10 rounded-lg flex items-center justify-center shrink-0">
                                <span class="material-symbols-outlined text-secondary text-lg">format_list_numbered</span>
                            </div>
                            <div>
                                <p class="text-xl font-bold text-on-surface leading-none">{{ $faqs->count() }}</p>
                                <p class="text-xs text-on-surface-variant mt-0.5">pertanyaan terjawab</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- CTA card --}}
                <div class="bg-primary rounded-2xl p-5 relative overflow-hidden">
                    <div class="absolute -top-6 -right-6 w-24 h-24 bg-white/10 rounded-full pointer-events-none"></div>
                    <div class="absolute -bottom-4 -left-4 w-16 h-16 bg-white/5 rounded-full pointer-events-none"></div>
                    <span class="material-symbols-outlined text-white/70 text-2xl mb-2 block relative z-10">chat_bubble</span>
                    <p class="text-white font-bold text-sm leading-snug mb-1 relative z-10">Masih ada pertanyaan?</p>
                    <p class="text-white/70 text-xs leading-relaxed mb-4 relative z-10">Tim kami siap membantu Anda.</p>
                    <a wire:navigate href="{{ rumahsakit_route('rumahsakit.hubungi_kami') }}"
                       class="relative z-10 inline-flex items-center gap-1.5 bg-white text-primary text-xs font-bold px-4 py-2 rounded-xl hover:bg-white/90 transition-colors group">
                        Hubungi Kami
                        <span class="material-symbols-outlined text-[14px] transition-transform group-hover:translate-x-0.5">arrow_forward</span>
                    </a>
                </div>
            </div>

            {{-- Accordion list --}}
            <div class="flex-1 min-w-0">
                <div class="hs-accordion-group flex flex-col gap-2.5">
                    @foreach($faqs as $index => $faq)
                    <div class="hs-accordion bg-white border border-outline-variant/30 rounded-2xl overflow-hidden shadow-sm hover:shadow-md hover:border-primary/20 transition-all duration-200"
                         id="faq-{{ $faq->id }}">
                        <button
                            class="hs-accordion-toggle w-full flex items-start gap-4 px-5 py-4 text-left group"
                            aria-expanded="false"
                            aria-controls="faq-body-{{ $faq->id }}">
                            {{-- Number badge --}}
                            <span class="shrink-0 w-7 h-7 mt-0.5 rounded-full bg-surface-container text-on-surface-variant text-xs font-bold flex items-center justify-center hs-accordion-active:bg-primary hs-accordion-active:text-white transition-colors duration-200">
                                {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}
                            </span>
                            {{-- Question text --}}
                            <span class="flex-1 font-semibold text-on-surface text-sm sm:text-[15px] leading-snug pt-0.5 group-hover:text-primary hs-accordion-active:text-primary transition-colors duration-200">
                                {{ $faq->judul }}
                            </span>
                            {{-- Chevron --}}
                            <span class="material-symbols-outlined shrink-0 text-outline-variant hs-accordion-active:text-primary hs-accordion-active:rotate-180 transition-all duration-200 mt-0.5 text-[22px]" aria-hidden="true">
                                expand_more
                            </span>
                        </button>
                        <div id="faq-body-{{ $faq->id }}"
                             class="hs-accordion-content hidden overflow-hidden transition-all duration-300"
                             role="region"
                             aria-labelledby="faq-{{ $faq->id }}">
                            <div class="px-5 pb-5 text-on-surface-variant leading-relaxed text-sm border-t border-outline-variant/15 pt-4 pl-16 prose prose-sm max-w-none prose-a:text-primary prose-strong:text-on-surface">
                                {!! str($faq->deskripsi)->sanitizeHtml() !!}
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

        </div>
    @endif

</div>
</section>

</div>
