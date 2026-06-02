<div
    x-data="{ scrollToBottom() { const el = $refs.msgs; if(el) el.scrollTop = el.scrollHeight; } }"
    x-init="scrollToBottom()"
    x-on:message-added.window="$nextTick(() => scrollToBottom())"
    class="font-sans flex justify-center w-full"
>
    <div class="w-full max-w-md h-[500px] rounded-2xl border border-[#d6c0ce] overflow-hidden flex flex-col bg-[#f8f9ff] shadow-xl">

        {{-- Header --}}
        <div class="bg-[#d606b0] px-4 py-3 flex items-center gap-3 flex-shrink-0">
            <div class="w-10 h-10 rounded-full bg-white/20 border-2 border-white/35 flex items-center justify-center flex-shrink-0">
                <span class="material-symbols-outlined text-white text-[22px]" aria-hidden="true">stethoscope</span>
            </div>
            <div class="flex-1 min-w-0">
                <div class="text-white text-[15px] font-medium truncate">Syifa Assistant</div>
                <div class="text-white/80 text-[11.5px] flex items-center gap-1 truncate">
                    <span class="w-[7px] h-[7px] rounded-full bg-[#96f6c8] inline-block flex-shrink-0"></span>
                    @if($this->branchSelected)
                        {{ $this->activeBranch->lokasi }}
                    @else
                        Pilih cabang untuk memulai
                    @endif
                </div>
            </div>
            <div class="flex gap-2 flex-shrink-0">
                @if($this->branchSelected)
                    <button
                        wire:click="changeBranch"
                        class="bg-white/15 hover:bg-white/28 border-none rounded-lg w-8 h-8 flex items-center justify-center cursor-pointer text-white transition-colors"
                        title="Ganti cabang" aria-label="Ganti cabang"
                    >
                        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">sync_alt</span>
                    </button>
                @endif
                <button class="bg-white/15 hover:bg-white/28 border-none rounded-lg w-8 h-8 flex items-center justify-center cursor-pointer text-white transition-colors" aria-label="Telepon">
                    <span class="material-symbols-outlined text-[18px]" aria-hidden="true">call</span>
                </button>
            </div>
        </div>

        {{-- Branch Selection Screen --}}
        @if(! $this->branchSelected)
        <div class="p-5 flex flex-col gap-4 flex-1 overflow-y-auto">
            <div class="text-center pt-1 pb-2">
                <div class="w-14 h-14 rounded-full bg-[#fce7f9] flex items-center justify-center mx-auto mb-3">
                    <span class="material-symbols-outlined text-[#d606b0] text-[30px]" aria-hidden="true">corporate_fare</span>
                </div>
                <div class="text-[16px] font-medium text-[#0b1c30] mb-1">Pilih cabang RSU Syifa Medika</div>
                <div class="text-[13px] text-[#84727e]">Layanan akan disesuaikan dengan cabang yang Anda pilih</div>
            </div>

            <div class="flex flex-col gap-2.5">
                @foreach($branches as $branch)
                <button
                    wire:click="selectBranch('{{ $branch->slug }}')"
                    class="bg-white border-[1.5px] border-[#d6c0ce] hover:border-[#d606b0] hover:bg-[#fdf0fc] rounded-[14px] px-4 py-3.5 cursor-pointer flex items-center gap-3.5 text-left transition-all w-full"
                    aria-label="Pilih cabang {{ $branch->nama }}"
                >
                    <div class="w-11 h-11 rounded-xl bg-[#e5eeff] flex items-center justify-center flex-shrink-0">
                        <span class="material-symbols-outlined text-[#d606b0] text-[24px]" aria-hidden="true">corporate_fare</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-[14px] font-medium text-[#0b1c30] mb-0.5">{{ $branch->nama }}</div>
                        <div class="text-[12px] text-[#84727e] flex items-center gap-1">
                            <span class="material-symbols-outlined text-[14px] text-[#84727e]" aria-hidden="true">location_on</span>
                            {{ $branch->lokasi }}
                        </div>
                    </div>
                    <span class="text-[10.5px] px-2 py-1 rounded-full font-medium bg-[#e8faf3] text-[#006c4b] flex-shrink-0">
                        Buka 24 jam
                    </span>
                    <span class="material-symbols-outlined text-[#d6c0ce] text-[20px] flex-shrink-0" aria-hidden="true">chevron_right</span>
                </button>
                @endforeach
            </div>

            <p class="text-[11.5px] text-[#84727e] text-center mt-auto flex items-center justify-center gap-1">
                <span class="material-symbols-outlined text-[15px] text-[#84727e]" aria-hidden="true">info</span>
                <span>Untuk kondisi darurat, langsung hubungi IGD atau call 119</span>
            </p>
        </div>
        @endif

        {{-- Chat Screen --}}
        @if($this->branchSelected)
        <div class="flex flex-col flex-1 h-full min-h-0">

            {{-- Quick Topics --}}
            <div class="bg-[#eff4ff] border-b border-[#d6c0ce] px-3 py-2 flex gap-1.5 overflow-x-auto flex-shrink-0 scrollbar-none items-center">
                @foreach([['calendar_month','Jadwal dokter'],['assignment','Pendaftaran'],['medical_services','Info IGD'],['corporate_fare','Fasilitas'],['receipt_long','Biaya & tarif'],['location_on','Lokasi']] as [$icon, $label])
                <button
                    wire:click="sendQuick('{{ $label }}')"
                    class="flex-shrink-0 bg-white border border-[#d6c0ce] rounded-full px-3 py-[5px] text-[11.5px] text-[#d606b0] hover:bg-[#d606b0] hover:text-white hover:border-[#d606b0] cursor-pointer whitespace-nowrap font-medium font-sans transition-all flex items-center gap-1"
                >
                    <span class="material-symbols-outlined text-[13px]" aria-hidden="true">{{ $icon }}</span>
                    <span>{{ $label }}</span>
                </button>
                @endforeach
            </div>

            {{-- Messages Area --}}
            <div class="flex-1 overflow-y-auto p-3.5 flex flex-col gap-3" x-ref="msgs" id="chat-msgs">

                <div class="bg-[#fff8fe] border border-[#d6c0ce] rounded-[9px] p-2.5 text-[11.5px] text-[#84727e] flex items-start gap-1.5 flex-shrink-0">
                    <span class="material-symbols-outlined text-[#d606b0] text-[16px] flex-shrink-0" aria-hidden="true">info</span>
                    <span>Asisten ini memberikan informasi umum. Untuk darurat, segera hubungi IGD.</span>
                </div>

                @foreach($this->messages as $msg)
                    @if($msg['type'] === 'user')
                    <div class="flex flex-row-reverse gap-2 items-end flex-shrink-0">
                        <div class="flex flex-col items-end max-w-[80%]">
                            <div class="px-3 py-2 rounded-[15px] rounded-br-[4px] bg-[#d606b0] text-white text-[13px] leading-snug break-words w-full">
                                {{ $msg['text'] }}
                            </div>
                            <div class="text-[10px] text-[#84727e] mt-0.5">{{ $msg['time'] }}</div>
                        </div>
                    </div>
                    @else
                    <div class="flex gap-2 items-end flex-shrink-0">
                        <div class="w-7 h-7 rounded-full bg-[#d606b0] flex items-center justify-center flex-shrink-0 mb-4">
                            <span class="material-symbols-outlined text-white text-[16px]" aria-hidden="true">stethoscope</span>
                        </div>
                        <div class="flex flex-col max-w-[80%]">
                            <div class="px-3 py-2 rounded-[15px] rounded-bl-[4px] bg-white border border-[#d6c0ce] text-[13px] leading-snug text-[#0b1c30] break-words">
                                {!! $msg['text'] !!}
                            </div>
                            @if(!empty($msg['card']))
                            <div class="bg-[#e5eeff] rounded-[10px] p-2.5 mt-1.5 border-l-[3px] border-[#d606b0]">
                                @foreach($msg['card'] as $row)
                                <div class="flex items-center gap-1.5 py-0.5 text-[12.5px] text-[#0b1c30]">
                                    {{-- Mengubah icon dinamis (pastikan backend mengirim nama ikon Material yang valid, contoh: 'mail' atau 'phone') --}}
                                    <span class="material-symbols-outlined text-[#d606b0] text-[15px] flex-shrink-0" aria-hidden="true">
                                        {{ str_replace('ti-', '', $row['icon']) === 'phone' ? 'call' : (str_replace('ti-', '', $row['icon']) === 'map-pin' ? 'location_on' : 'info') }}
                                    </span>
                                    <span class="truncate">{{ $row['text'] }}</span>
                                </div>
                                @endforeach
                            </div>
                            @endif
                            @if(!empty($msg['opts']))
                            <div class="flex flex-wrap gap-1.5 mt-2">
                                @foreach($msg['opts'] as $opt)
                                <button
                                    wire:click="sendQuick('{{ $opt }}')"
                                    class="bg-[#eff4ff] border border-[#d606b0] text-[#d606b0] rounded-[9px] px-3 py-1 text-[11.5px] font-medium font-sans hover:bg-[#d606b0] hover:text-white cursor-pointer transition-all"
                                >{{ $opt }}</button>
                                @endforeach
                            </div>
                            @endif
                            <div class="text-[10px] text-[#84727e] mt-0.5">{{ $msg['time'] }}</div>
                        </div>
                    </div>
                    @endif
                @endforeach

                {{-- Typing indicator saat wire loading --}}
                <div wire:loading wire:target="sendMessage,sendQuick" class="flex gap-2 items-end flex-shrink-0">
                    <div class="w-7 h-7 rounded-full bg-[#d606b0] flex items-center justify-center">
                        <span class="material-symbols-outlined text-white text-[16px]" aria-hidden="true">stethoscope</span>
                    </div>
                    <div class="bg-white border border-[#d6c0ce] rounded-[15px] rounded-bl-[4px] px-3.5 py-3 flex gap-1 items-center">
                        <span class="w-[5px] h-[5px] rounded-full bg-[#d606b0] animate-bounce inline-block" style="animation-delay:0s"></span>
                        <span class="w-[5px] h-[5px] rounded-full bg-[#d606b0] animate-bounce inline-block" style="animation-delay:.15s"></span>
                        <span class="w-[5px] h-[5px] rounded-full bg-[#d606b0] animate-bounce inline-block" style="animation-delay:.3s"></span>
                    </div>
                </div>

            </div>

            {{-- Input Footer --}}
            <div class="bg-white border-t border-[#d6c0ce] px-3 py-2.5 flex-shrink-0">
                <div class="flex gap-2 items-center">
                    <input
                        wire:model="inputMessage"
                        wire:keydown.enter="sendMessage"
                        type="text"
                        placeholder="Ketik pertanyaan Anda..."
                        maxlength="300"
                        class="flex-1 border border-[#d6c0ce] rounded-full px-4 py-2 text-[13px] font-sans text-[#0b1c30] bg-[#eff4ff] outline-none focus:border-[#d606b0] focus:bg-white placeholder-[#84727e] transition-colors"
                        aria-label="Kotak pesan"
                    />
                    <button
                        wire:click="sendMessage"
                        class="w-9 h-9 rounded-full bg-[#d606b0] hover:bg-[#b649a9] active:scale-95 border-none flex items-center justify-center cursor-pointer flex-shrink-0 transition-all"
                        aria-label="Kirim pesan"
                    >
                        <span class="material-symbols-outlined text-white text-[16px]" aria-hidden="true">send</span>
                    </button>
                </div>
                <p class="text-center text-[10.5px] text-[#84727e] mt-1.5 flex items-center justify-center gap-1">
                    <span class="material-symbols-outlined text-[12px] text-[#84727e]" aria-hidden="true">verified_user</span>
                    <span>RSU Syifa Medika &bull; Layanan Informasi Kesehatan</span>
                </p>
            </div>

        </div>
        @endif

    </div>
</div>