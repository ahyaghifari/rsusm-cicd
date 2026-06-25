{{-- Informational placeholder with button to open the Zone Editor page --}}
<div class="flex flex-col items-center gap-3 py-6 text-center">
    <div class="w-14 h-14 rounded-2xl bg-primary-50 dark:bg-primary-500/10 flex items-center justify-center">
        <svg class="w-7 h-7 text-primary-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M9.53 16.122a3 3 0 00-5.78 1.128 2.25 2.25 0 01-2.4 2.245 4.5 4.5 0 008.4-2.245c0-.399-.078-.78-.22-1.128zm0 0a15.998 15.998 0 003.388-1.62m-5.043-.025a15.994 15.994 0 011.622-3.395m3.42 3.42a15.995 15.995 0 004.764-4.648l3.876-5.814a1.151 1.151 0 00-1.597-1.597L14.146 6.32a15.996 15.996 0 00-4.649 4.763m3.42 3.42a6.776 6.776 0 00-3.42-3.42"/>
        </svg>
    </div>
    <div>
        <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">Buka Zone Editor</p>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
            Atur posisi logo, zona jadwal, tinggi hero, dan style card poli secara visual.
        </p>
    </div>
    <a href="{{ \App\Filament\Resources\PosterTemplateResource::getUrl('zone-editor', ['record' => $record]) }}"
       class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-white
              bg-primary-600 hover:bg-primary-500 rounded-lg transition shadow-sm">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
        </svg>
        Buka Zone Editor
    </a>
</div>
