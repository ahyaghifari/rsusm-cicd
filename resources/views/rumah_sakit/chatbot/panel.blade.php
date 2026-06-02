<div class="font-sans flex justify-center w-full">
    <div class="w-full max-w-md h-[500px] rounded-2xl border border-[#d6c0ce] overflow-hidden flex flex-col bg-[#f8f9ff] shadow-xl">

        {{-- Header --}}
        <div class="bg-[#d606b0] px-4 py-3 flex items-center gap-3 flex-shrink-0">
            <div class="w-10 h-10 rounded-full bg-white border-2 border-white/35 flex items-center justify-center flex-shrink-0 p-1">
                <img src="{{asset('img/favicon.png')}}" alt="">
            </div>
            <div class="flex-1 min-w-0">
                <div class="text-white text-[15px] font-medium truncate">Syifa Medika Assistant</div>
                <div class="text-white/80 text-[11.5px] flex items-center gap-1 truncate">
                    <span class="w-[7px] h-[7px] rounded-full bg-[#96f6c8] inline-block flex-shrink-0"></span>
                    @if($this->branchSelected)
                        {{ $this->activeBranch->nama }}
                    @else
                        Pilih cabang untuk memulai
                    @endif
                </div>
            </div>
            <div class="flex gap-2 flex-shrink-0">
                @if($this->branchSelected)
                    <button
                        wire:click="changeBranch"
                        class="bg-white/15 hover:bg-white/28 border-none rounded-lg w-8 h-8 flex items-center justify-center cursor-pointer text-white transition-colors p-1"
                        title="Ganti cabang" aria-label="Ganti cabang"
                    >
                        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">sync_alt</span>
                    </button>
                @endif
            </div>
        </div>

        {{-- Branch Selection Screen --}}
        @if(! $this->branchSelected)
        <div class="p-5 flex flex-col gap-4 flex-1 overflow-y-auto">
            <div class="text-center pt-1 pb-2">
                <div class="w-14 h-14 rounded-full bg-[#fce7f9] flex items-center justify-center mx-auto mb-3 p-2">
                    <img src="{{asset('img/favicon.png')}}" alt="">
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
                    <div class="w-11 h-11 rounded-xl bg-[#e5eeff] flex items-center justify-center shrink-0 @if($branch->logo) p-1 @endif">
                        @if($branch->logo)
                            <img src="{{ asset('storage/' . $branch->logo) }}" alt="{{ $branch->nama }} logo" class="max-w-full max-h-full">
                        @else
                        <span class="material-symbols-outlined text-[#d606b0] text-[24px]" aria-hidden="true">corporate_fare</span>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-medium text-[#0b1c30] mb-0.5">{{ $branch->nama }}</div>
                       
                    </div>
                    
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

            {{-- ==================== MESSAGES AREA (PERBAIKAN SYSTEM SCROLL) ==================== --}}
            <div
                id="chat-msgs"
                class="flex-1 overflow-y-auto p-3.5 flex flex-col gap-3"
                x-init="
                    $el.scrollTop = $el.scrollHeight;

                    const scrollToLastMsg = () => {
                        const last = $el.lastElementChild;
                        if (!last) return;
                        // Hitung posisi relatif terhadap container agar bubble terlihat dari atas
                        const childRect = last.getBoundingClientRect();
                        const containerRect = $el.getBoundingClientRect();
                        const relTop = childRect.top - containerRect.top + $el.scrollTop;
                        $el.scrollTo({ top: relTop - 16, behavior: 'smooth' });
                    };

                    const observer = new MutationObserver(() => {
                        setTimeout(scrollToLastMsg, 60);
                    });

                    observer.observe($el, { childList: true, subtree: true });
                "
            >
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
                        <div class="w-7 h-7 rounded-full bg-white border-2 border-primary flex items-center justify-center flex-shrink-0 mb-4 p-1">
                             <img src="{{ asset('img/favicon.png') }}" alt="">
                        </div>
                        <div class="flex flex-col max-w-[80%]">
                            <div class="px-3 py-2 rounded-[15px] rounded-bl-[4px] bg-white border border-[#d6c0ce] text-[13px] leading-snug text-[#0b1c30] break-words">
                                {!! nl2br(str($msg['text'])->sanitizeHtml()) !!}
                            </div>
                          
                            <div class="text-[10px] text-[#84727e] mt-0.5">{{ $msg['time'] }}</div>
                        </div>
                    </div>
                    @endif
                @endforeach

                {{-- Typing indicator saat wire loading --}}
                <div wire:loading wire:target="sendMessage,sendQuick" class="flex gap-2 items-end flex-shrink-0">
                    <div class="w-7 h-7 rounded-full bg-white border-2 border-primary flex items-center justify-center flex-shrink-0 mb-4 p-1">
                             <img src="{{ asset('img/favicon.png') }}" alt="">
                        </div>
                    <div class="bg-white border border-[#d6c0ce] rounded-[15px] rounded-bl-[4px] px-3.5 py-3 flex gap-1 items-center mt-2">
                        <span class="w-[5px] h-[5px] rounded-full bg-[#d606b0] animate-bounce inline-block" style="animation-delay:0s"></span>
                        <span class="w-[5px] h-[5px] rounded-full bg-[#d606b0] animate-bounce inline-block" style="animation-delay:.15s"></span>
                        <span class="w-[5px] h-[5px] rounded-full bg-[#d606b0] animate-bounce inline-block" style="animation-delay:.3s"></span>
                    </div>
                </div>

            </div>
            {{-- ================================================================================= --}}

            {{-- Input Footer --}}
            <div class="bg-white border-t border-[#d6c0ce] px-3 py-2.5 flex-shrink-0"
                 x-data="{
                     msg: '',
                     get remaining() { return 50 - this.msg.length; },
                     send() {
                         const text = this.msg.trim();
                         if (!text) return;
                         $wire.set('inputMessage', text);
                         this.msg = '';
                         $wire.call('sendMessage');
                     }
                 }">
                <div class="flex gap-2 items-center">
                    <input
                        x-model="msg"
                        @keydown.enter="send()"
                        wire:loading.attr="disabled"
                        wire:target="sendMessage,sendQuick"
                        type="text"
                        placeholder="Ketik pertanyaan Anda..."
                        maxlength="50"
                        class="flex-1 border border-[#d6c0ce] rounded-full px-4 py-2 text-[13px] font-sans text-[#0b1c30] bg-[#eff4ff] outline-none focus:border-[#d606b0] focus:bg-white placeholder-[#84727e] transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        aria-label="Kotak pesan"
                    />
                    <button
                        @click="send()"
                        wire:loading.attr="disabled"
                        wire:target="sendMessage,sendQuick"
                        class="w-9 h-9 rounded-full bg-[#d606b0] hover:bg-[#b649a9] active:scale-95 border-none flex items-center justify-center cursor-pointer flex-shrink-0 transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                        aria-label="Kirim pesan"
                    >
                        <span class="material-symbols-outlined text-white text-[16px]" aria-hidden="true">send</span>
                    </button>
                </div>
                <div class="flex items-center justify-between mt-1.5">
                    <p class="text-[10.5px] text-[#84727e] flex items-center gap-1">
                        <span class="material-symbols-outlined text-[10px]" aria-hidden="true">verified_user</span>
                        <span>RSU Syifa Medika &bull; Layanan Informasi Kesehatan</span>
                    </p>
                    <span class="text-[10.5px] font-medium tabular-nums"
                          :class="remaining <= 20 ? (remaining <= 0 ? 'text-red-500' : 'text-amber-500') : 'text-[#b0a0ab]'"
                          x-text="remaining + '/50'"></span>
                </div>
            </div>

        </div>
        @endif

    </div>
</div>