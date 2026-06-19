<div wire:poll.30s>
    <x-page-hero title="Ketersediaan Rawat Inap" subtitle="Cek kamar kosong, terisi, dan reservasi secara real-time" />

    <div class="mt-5 w-10/12 mx-auto pb-16">

        {{-- Indikator data diperbarui --}}
        @if($lastSyncedAt)
            <p class="text-xs text-on-surface-variant text-center mb-6">
                Data diperbarui pukul {{ \Illuminate\Support\Carbon::parse($lastSyncedAt)->translatedFormat('H:i:s, d F Y') }}
            </p>
        @endif

        {{-- Ringkasan --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-green-50 border border-green-200 rounded-2xl p-4 text-center">
                <p class="text-3xl font-bold text-green-700">{{ $ringkasan[1] }}</p>
                <p class="text-sm text-green-700 font-medium mt-1">Kosong</p>
            </div>
            <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 text-center">
                <p class="text-3xl font-bold text-amber-700">{{ $ringkasan[2] }}</p>
                <p class="text-sm text-amber-700 font-medium mt-1">Reservasi</p>
            </div>
            <div class="bg-red-50 border border-red-200 rounded-2xl p-4 text-center">
                <p class="text-3xl font-bold text-red-700">{{ $ringkasan[3] }}</p>
                <p class="text-sm text-red-700 font-medium mt-1">Terisi</p>
            </div>
            <div class="bg-gray-100 border border-gray-300 rounded-2xl p-4 text-center">
                <p class="text-3xl font-bold text-gray-600">{{ $ringkasan[6] }}</p>
                <p class="text-sm text-gray-600 font-medium mt-1">Perbaikan</p>
            </div>
        </div>

        {{-- Filter --}}
        <div class="flex flex-col sm:flex-row gap-3 mb-8">
            <select wire:model.live="kelasFilter"
                class="w-full sm:w-64 rounded-xl border-gray-300 text-sm shadow-sm focus:ring-primary-500 focus:border-primary-500">
                <option value="">Semua Kelas</option>
                @foreach($kelasOptions as $kelas)
                    <option value="{{ $kelas->id }}">{{ $kelas->nama }}</option>
                @endforeach
            </select>

            <select wire:model.live="namaKamarFilter"
                class="w-full sm:w-64 rounded-xl border-gray-300 text-sm shadow-sm focus:ring-primary-500 focus:border-primary-500">
                <option value="">Semua Kamar</option>
                @foreach($namaKamarOptions as $namaKamar)
                    <option value="{{ $namaKamar }}">{{ $namaKamar }}</option>
                @endforeach
            </select>
        </div>

        {{-- List Kamar --}}
        @if($kamarList->isEmpty())
            <div class="flex flex-col items-center justify-center py-24 text-center">
                <div class="w-20 h-20 bg-primary/10 rounded-2xl flex items-center justify-center mx-auto mb-5">
                    <span class="material-symbols-outlined text-4xl text-primary">bed</span>
                </div>
                <p class="text-lg font-semibold text-on-surface">Data ketersediaan belum tersedia</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($kamarList as $beds)
                    @php $first = $beds->first(); @endphp
                    <div class="bg-white rounded-2xl border border-outline-variant/20 shadow-sm p-5">
                        <div class="flex items-start justify-between gap-2 mb-3">
                            <h3 class="font-bold text-on-surface text-sm leading-snug">{{ $first->nama_kamar }}</h3>
                            @if($first->kelasRawatInap)
                                <span class="shrink-0 text-[11px] font-bold uppercase tracking-wide bg-primary/10 text-primary px-2 py-1 rounded-full">
                                    {{ $first->kelasRawatInap->nama }}
                                </span>
                            @endif
                        </div>

                        <div class="space-y-2">
                            @foreach($beds as $bed)
                                @php
                                    $statusEnum = $bed->status_enum;
                                    $colorClass = match($bed->status) {
                                        1 => 'bg-green-100 text-green-700',
                                        2 => 'bg-amber-100 text-amber-700',
                                        3 => 'bg-red-100 text-red-700',
                                        6 => 'bg-gray-200 text-gray-600',
                                        default => 'bg-gray-200 text-gray-600',
                                    };
                                @endphp
                                <div class="flex items-center justify-between gap-2 text-sm">
                                    <span class="text-on-surface-variant">{{ $bed->tempat_tidur }}</span>
                                    <span class="text-[11px] font-bold px-2 py-1 rounded-full {{ $colorClass }}">
                                        {{ $statusEnum?->getLabel() ?? \App\Enums\StatusKetersediaanKamar::labelFor($bed->status) }}
                                    </span>
                                </div>
                                @if($bed->keterangan)
                                    <p class="text-xs text-on-surface-variant/80 italic">{{ $bed->keterangan }}</p>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

    </div>
</div>
